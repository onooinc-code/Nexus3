<?php

use App\Jobs\PeopleConnect\CloseInactivePeopleConnectSessionsJob;
use App\Jobs\PeopleConnect\ReconcileWahaDeliveryStatusJob;
use App\Jobs\PeopleConnect\SyncWahaContactsJob;
use App\Jobs\PeopleConnect\SyncWahaConversationsJob;
use App\Jobs\PeopleConnect\SyncWahaMessagesJob;
use App\Services\TaskSchedulingService;
use App\Services\Workflows\WorkflowScheduleService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('ai:poll-health')->everyFiveMinutes();
Schedule::command('ai:rotate-keys')->daily();

Schedule::call(function () {
    app(TaskSchedulingService::class)->processDueTasks();
})->everyMinute();

Schedule::call(function () {
    app(WorkflowScheduleService::class)->processScheduledWorkflows();
})->everyMinute();

Schedule::command('monitor:reverb-health')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->description('Run periodic Reverb WebSocket health checks.');

Schedule::command('proactive:run-scheduler')
    ->everyMinute()
    ->withoutOverlapping()
    ->description('Run proactive AI autonomous trigger scheduler.');

Schedule::command('monitor:settings-health')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->description('Run periodic health checks for settings and integration credentials.');

// PeopleConnect Scheduled Jobs
Schedule::job(new SyncWahaContactsJob, null, 'peopleconnect')->hourly();
Schedule::job(new SyncWahaConversationsJob, null, 'peopleconnect')->hourly();
Schedule::job(new SyncWahaMessagesJob, null, 'peopleconnect')->hourly();
Schedule::job(new ReconcileWahaDeliveryStatusJob, null, 'peopleconnect')->hourly();
Schedule::job(new CloseInactivePeopleConnectSessionsJob, null, 'peopleconnect')->everyFifteenMinutes();

// Auto-sync providers based on their auto_sync_interval setting
Schedule::call(function () {
    \App\Models\AIProvider::where('is_active', true)
        ->whereNotNull('auto_sync_interval')
        ->where('auto_sync_interval', '!=', 'never')
        ->get()
        ->each(function ($provider) {
            $shouldSync = match($provider->auto_sync_interval) {
                '6h'     => $provider->last_synced_at === null || $provider->last_synced_at->lt(now()->subHours(6)),
                '12h'    => $provider->last_synced_at === null || $provider->last_synced_at->lt(now()->subHours(12)),
                '24h'    => $provider->last_synced_at === null || $provider->last_synced_at->lt(now()->subDay()),
                'weekly' => $provider->last_synced_at === null || $provider->last_synced_at->lt(now()->subWeek()),
                default  => false,
            };
            if ($shouldSync) {
                dispatch(new \App\Jobs\SyncProviderModelsJob($provider->id))->onQueue('ai-sync');
            }
        });
})->hourly()->name('ai-providers:auto-sync');
