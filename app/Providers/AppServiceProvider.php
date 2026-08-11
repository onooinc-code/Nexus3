<?php

namespace App\Providers;

use App\Contracts\AIEngineContract;
use App\Contracts\IntentRouterContract;
use App\Contracts\MemoryEngineContract;
use App\Events\ContactCreated;
use App\Events\MemoryIndexed;
use App\Events\MessageReceived;
use App\Events\PeopleConnect\MessageReceived as PeopleConnectMessageReceived;
use App\Events\TaskCompletedEvent;
use App\Events\TaskFailedEvent;
use App\Events\WorkflowCompleted;
use App\Events\WorkflowStarted;
use App\Events\WorkflowStepCompleted;
use App\Listeners\IndexMemory;
use App\Listeners\LogJobFailed;
use App\Listeners\LogWorkflowCompleted;
use App\Listeners\LogWorkflowStarted;
use App\Listeners\LogWorkflowStepCompleted;
use App\Listeners\NotifyJobFailed;
use App\Listeners\ProcessContactCreated;
use App\Listeners\ProcessMessageReceived;
use App\Listeners\ResumeWorkflowOnTaskCompletion;
use App\Listeners\TriggerWorkflowsForEvent;
use App\Models\Agent;
use App\Models\Contact;
use App\Models\ConversationSession;
use App\Models\HedrasoulMessage;
use App\Models\HedrasoulNotification;
use App\Models\HedrasoulSession;
use App\Models\Setting;
use App\Models\User;
use App\Policies\AgentPolicy;
use App\Policies\ContactPolicy;
use App\Policies\HedrasoulMessagePolicy;
use App\Policies\HedrasoulSessionPolicy;
use App\Policies\SessionPolicy;
use App\Policies\SettingPolicy;
use App\Services\AI\AIEngine;
use App\Services\AiModelsHub\UniversalAiGatewayService;
use App\Services\Memory\MemoryEngine;
use App\Services\Memory\MemoryService;
use App\Services\Routing\IntentRouter;
use App\Services\Routing\MessageRouterService;
use App\Services\WhatsApp\WAHAService;
use App\Services\Workflows\WorkflowEventTriggerService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Sentinel\Drivers\Driver;
use Laravel\Sentinel\Sentinel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Singleton Services
        $this->app->singleton('nexus.memory', function ($app) {
            return new MemoryService($app['cache'], $app['db']);
        });

        $this->app->singleton('nexus.ai', function ($app) {
            return $app->make(UniversalAiGatewayService::class);
        });

        $this->app->singleton('nexus.whatsapp', function ($app) {
            return new WAHAService($app['config']);
        });

        $this->app->singleton('nexus.router', function ($app) {
            return new MessageRouterService($app['cache']);
        });

        // Bind Interface implementations
        $this->app->bind(
            MemoryEngineContract::class,
            MemoryEngine::class
        );

        $this->app->bind(
            AIEngineContract::class,
            AIEngine::class
        );

        $this->app->bind(
            IntentRouterContract::class,
            IntentRouter::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (class_exists(Sentinel::class)) {
            Sentinel::extend('horizon', function ($app) {
                return new class(fn () => $app) extends Driver
                {
                    public function authorize(Request $request): bool
                    {
                        return true;
                    }
                };
            });
            Sentinel::extend('laravel', function ($app) {
                return new class(fn () => $app) extends Driver
                {
                    public function authorize(Request $request): bool
                    {
                        return true;
                    }
                };
            });
        }

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Configure pagination
        Paginator::useBootstrap();

        // Register event listeners
        Event::listen(MemoryIndexed::class, [IndexMemory::class, 'handle']);
        Event::listen(ContactCreated::class, [ProcessContactCreated::class, 'handle']);
        Event::listen(ContactCreated::class, [TriggerWorkflowsForEvent::class, 'handle']);
        Event::listen(MessageReceived::class, [ProcessMessageReceived::class, 'handle']);
        Event::listen(MessageReceived::class, [TriggerWorkflowsForEvent::class, 'handle']);
        Event::listen(PeopleConnectMessageReceived::class, [ProcessMessageReceived::class, 'handle']);
        Event::listen(PeopleConnectMessageReceived::class, [TriggerWorkflowsForEvent::class, 'handle']);
        Event::listen(WorkflowStarted::class, [LogWorkflowStarted::class, 'handle']);
        Event::listen(WorkflowCompleted::class, [LogWorkflowCompleted::class, 'handle']);
        Event::listen(WorkflowStepCompleted::class, [LogWorkflowStepCompleted::class, 'handle']);
        Event::listen(JobFailed::class, [LogJobFailed::class, 'handle']);
        Event::listen(JobFailed::class, [NotifyJobFailed::class, 'handle']);
        Event::listen(TaskCompletedEvent::class, [ResumeWorkflowOnTaskCompletion::class, 'handle']);
        Event::listen(TaskFailedEvent::class, [ResumeWorkflowOnTaskCompletion::class, 'handle']);

        // Register broadcast authorization policies
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(Agent::class, AgentPolicy::class);
        Gate::policy(ConversationSession::class, SessionPolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(HedrasoulSession::class, HedrasoulSessionPolicy::class);
        Gate::policy(HedrasoulMessage::class, HedrasoulMessagePolicy::class);
        Gate::define('viewBatch', fn (User $user, string $batchId): bool => in_array($user->email, config('broadcasting.admin_emails', []), true));
        Gate::define('viewDlq', fn (User $user): bool => in_array($user->email, config('broadcasting.admin_emails', []), true));

        RateLimiter::for('analysis', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Register broadcast auth routes and load channel definitions
        Broadcast::routes(['middleware' => ['api', 'auth:sanctum']]);
        if (file_exists(base_path('routes/channels.php'))) {
            require base_path('routes/channels.php');
        }

        // Apply optional Redis fallback when local Redis is unavailable.
        $this->configureRedisFallback();

        // Register macros for common operations
        $this->registerMacros();

        // Register wildcard workflow event trigger listener
        app(WorkflowEventTriggerService::class)->registerWildcardListener();

        // Global view composer for layout
        View::composer('layouts.app', function ($view) {
            $unreadNotificationsCount = 0;
            try {
                $unreadNotificationsCount = HedrasoulNotification::unread()->active()->count();
            } catch (\Exception $e) {
            }
            $view->with('unreadNotificationsCount', $unreadNotificationsCount);
        });
    }

    /**
     * Apply a safe local fallback when Redis cannot be reached.
     */
    protected function configureRedisFallback(): void
    {
        if (! env('REDIS_FALLBACK', false)) {
            return;
        }

        try {
            $connection = app('redis')->connection();
            if (! $connection->ping()) {
                throw new \RuntimeException('Redis ping failed');
            }
        } catch (\Throwable $exception) {
            Log::warning('Redis unavailable, falling back to local drivers.', [
                'redis_host' => env('REDIS_HOST'),
                'redis_port' => env('REDIS_PORT'),
                'exception' => $exception->getMessage(),
            ]);

            config(['cache.default' => 'file']);
            config(['session.driver' => 'database']);
            config(['queue.default' => 'sync']);
        }
    }

    /**
     * Register helper macros
     */
    protected function registerMacros(): void
    {
        // Collection macros for common operations
        Collection::macro('mapWithKeys', function (callable $callback) {
            $result = [];
            foreach ($this as $key => $value) {
                $mapped = $callback($value, $key);
                foreach ($mapped as $k => $v) {
                    $result[$k] = $v;
                }
            }

            return new self($result);
        });
    }
}
