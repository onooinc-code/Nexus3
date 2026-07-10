<?php

namespace App\Http\Controllers\Web;

use App\Events\AgentStarted;
use App\Http\Controllers\Controller;
use App\Jobs\SyncWahaContactsJob;
use App\Jobs\SyncWahaMessagesJob;
use App\Models\Agent;
use App\Models\AgentTask;
use App\Models\AIApiKey;
use App\Models\AIModel;
use App\Models\AIProvider;
use App\Models\Contact;
use App\Models\ContactMessage;
use App\Models\HedrasoulMessage;
use App\Models\HedrasoulNotification;
use App\Models\HedrasoulSession;
use App\Models\IntentRouting;
use App\Models\Memory;
use App\Models\NotificationLog;
use App\Models\ProactiveTrigger;
use App\Models\Setting;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Models\WorkflowSchedule;
use App\Services\LogService;
use App\Services\NexusDashboardService;
use App\Services\SettingCacheService;
use App\Services\WorkflowExecutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class HubController extends Controller
{
    public function dashboard()
    {
        $totalContacts = Contact::count();
        $contactDelta = Contact::where('created_at', '>=', now()->startOfDay())->count();

        $activeExecutes = WorkflowExecution::whereIn('status', ['running', 'pending'])->count();
        $activeTasksCount = 0;
        try {
            $activeTasksCount = AgentTask::whereIn('status', ['running', 'in_progress', 'queued', 'pending'])->count();
        } catch (\Exception $e) {
        }

        $agentCount = Agent::count();
        $onlineAgentsCount = Agent::where('status', 'active')->count();
        $totalAgentsCount = $agentCount;

        $activeAgent = Agent::where('status', 'active')->first() ?: Agent::first();
        $activeAgentModel = $activeAgent ? strtoupper($activeAgent->model) : 'GEMINI';

        $memoryCount = 0;
        $memoryDelta = 0;
        try {
            $memoryCount = \DB::table('memories')->count();
            $memoryDelta = \DB::table('memories')->where('created_at', '>=', now()->startOfDay())->count();
        } catch (\Exception $e) {
        }

        // Recent contacts for dashboard panel
        $recentContacts = Contact::orderBy('updated_at', 'desc')->take(6)->get();

        // Agents for status panel
        $agents = Agent::orderBy('status', 'asc')->take(6)->get();

        // Upcoming schedules
        $upcomingSchedules = [];
        try {
            $upcomingSchedules = WorkflowSchedule::where('is_active', true)
                ->orderBy('next_run_at', 'asc')
                ->take(5)
                ->get();
        } catch (\Exception $e) {
        }

        // Recent activity logs for telemetry (using logs table)
        $recentLogs = [];
        try {
            $recentLogs = \DB::table('logs')
                ->orderBy('created_at', 'desc')
                ->take(20)
                ->get()
                ->reverse()
                ->values();
        } catch (\Exception $e) {
        }

        return view('hubs.dashboard', compact(
            'totalContacts', 'contactDelta', 'activeExecutes', 'activeTasksCount',
            'agentCount', 'onlineAgentsCount', 'totalAgentsCount', 'activeAgentModel',
            'memoryCount', 'memoryDelta', 'recentContacts', 'agents',
            'upcomingSchedules', 'recentLogs'
        ));
    }

    public function contacts(Request $request)
    {
        $totalContacts = Contact::count();
        $wahaContacts = Contact::whereNotNull('waha_contact_id')->count();
        $autopilotCount = Contact::where('reply_mode_override', 'autopilot')->count();
        $copilotCount = Contact::where('reply_mode_override', 'copilot')->count();

        $query = Contact::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('mode') && $request->mode !== 'all') {
            if ($request->mode === 'manual') {
                $query->where(function ($q) {
                    $q->where('reply_mode_override', 'manual')->orWhereNull('reply_mode_override');
                });
            } else {
                $query->where('reply_mode_override', $request->mode);
            }
        }

        if ($request->filled('waha') && $request->waha == '1') {
            $query->whereNotNull('waha_contact_id');
        }

        if ($request->filled('favorites') && $request->favorites == '1') {
            $user = $request->user();
            if ($user) {
                $query->whereHas('favoritedBy', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }
        }

        $contacts = $query->orderBy('created_at', 'desc')->paginate(24)->withQueryString();

        return view('hubs.contacts', compact('contacts', 'totalContacts', 'wahaContacts', 'autopilotCount', 'copilotCount'));
    }

    public function contactProfile($id)
    {
        $contact = Contact::findOrFail($id);

        $auditEvents = \DB::table('contact_audit_events')
            ->where('contact_id', $contact->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        $stats = [
            'total_messages' => ContactMessage::where('contact_id', $contact->id)->count(),
            'inbound' => ContactMessage::where('contact_id', $contact->id)->where('direction', 'inbound')->count(),
            'outbound' => ContactMessage::where('contact_id', $contact->id)->where('direction', 'outbound')->count(),
            'has_media' => ContactMessage::where('contact_id', $contact->id)->whereNotNull('attachments_metadata')->count(),
        ];

        $messages = ContactMessage::where('contact_id', $contact->id)
            ->orderBy('source_timestamp', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(100, ['*'], 'msg_page');

        return view('hubs.contact-profile', compact('contact', 'auditEvents', 'stats', 'messages'));
    }

    public function storeContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'role' => 'nullable|string',
            'company' => 'nullable|string',
        ]);

        $contact = Contact::create($validated);

        return response()->json(['success' => true, 'contact' => $contact]);
    }

    public function agents()
    {
        $agents = Agent::all();

        return view('hubs.agents', compact('agents'));
    }

    public function storeAgent(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'role' => 'required|string',
            'model' => 'required|string',
            'system_prompt' => 'nullable|string',
        ]);

        $validated['status'] = 'draft';
        $agent = Agent::create($validated);

        return response()->json(['success' => true, 'agent' => $agent]);
    }

    public function toggleAgent(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);
        $agent->status = $request->status ?? 'active';
        $agent->save();

        return response()->json(['success' => true]);
    }

    public function workflows()
    {
        $workflows = Workflow::all();

        return view('hubs.workflows', compact('workflows'));
    }

    public function memory()
    {
        $memories = Memory::orderBy('created_at', 'desc')->get();

        return view('hubs.memory', compact('memories'));
    }

    public function storeMemory(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'required|string',
            'confidence' => 'nullable|numeric|min:0|max:1',
        ]);

        $memory = new Memory;
        $memory->content = $validated['content'];
        $memory->type = strtolower($validated['type']);
        $memory->source = 'user_injection';
        $memory->title = Str::limit($validated['content'], 40);
        $memory->metadata = [
            'confidence' => (float) ($validated['confidence'] ?? 1.0),
            'injected_by' => 'user',
        ];
        $memory->save();

        return response()->json(['success' => true, 'memory' => $memory]);
    }

    public function logs()
    {
        return view('hubs.logs');
    }

    public function models(Request $request)
    {
        $currentPage = $request->get('page', 1);

        $providersQuery = AIProvider::withCount(['models', 'apiKeys'])
            ->with(['apiKeys' => fn($q) => $q->where('is_active', true)->limit(1)]);
            
        $allProviders = $providersQuery->get();

        // Attach usage stats in bulk (single query)
        $monthStats = \Illuminate\Support\Facades\DB::table('usage_logs')
            ->selectRaw('provider_id, SUM(total_cost) as month_cost, COUNT(*) as month_requests, SUM(input_tokens + output_tokens) as month_tokens')
            ->where('timestamp', '>=', now()->startOfMonth())
            ->groupBy('provider_id')
            ->get()->keyBy('provider_id');

        $todayStats = \Illuminate\Support\Facades\DB::table('usage_logs')
            ->selectRaw('provider_id, SUM(total_cost) as today_cost, COUNT(*) as today_requests, SUM(input_tokens + output_tokens) as today_tokens')
            ->where('timestamp', '>=', now()->startOfDay())
            ->groupBy('provider_id')
            ->get()->keyBy('provider_id');

        // Attach last ping status per provider
        $lastPings = \Illuminate\Support\Facades\DB::table('provider_health_metrics')
            ->select('provider_id', 'status', 'latency_ms')
            ->whereIn('id', function($q) {
                $q->selectRaw('MAX(id)')->from('provider_health_metrics')->groupBy('provider_id');
            })->get()->keyBy('provider_id');

        // Inject into each provider object before passing to view
        $enrichedProviders = $allProviders->map(function($p) use ($monthStats, $todayStats, $lastPings) {
            $p->month_stats  = $monthStats[$p->id]  ?? null;
            $p->today_stats  = $todayStats[$p->id]  ?? null;
            $p->last_ping    = $lastPings[$p->id]   ?? null;
            $p->health_status = $p->last_ping?->status ?? ($p->is_active ? 'no_ping' : 'disabled');
            return $p;
        });

        // Provider Health Summary for strip
        $healthSummary = [
            'active'       => $enrichedProviders->where('is_active', true)->count(),
            'total'        => $enrichedProviders->count(),
            'no_key'       => $enrichedProviders->filter(fn($p) => $p->api_keys_count === 0)->count(),
            'unreachable'  => $enrichedProviders->where('health_status', 'offline')->count(),
            'degraded'     => $enrichedProviders->where('health_status', 'degraded')->count(),
            'last_sync_at' => AIProvider::max('last_synced_at'),
        ];

        // Paginate (after enrichment)
        $providers = new \Illuminate\Pagination\LengthAwarePaginator(
            $enrichedProviders->forPage($currentPage, 12), 
            $enrichedProviders->count(), 
            12, 
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $models = AIModel::with('provider')->get();
        $apiKeys = AIApiKey::with('provider')->get();
        $routingRules = IntentRouting::with(['defaultProvider', 'defaultModel', 'fallbackProvider', 'fallbackModel'])->get();

        return view('hubs.models', compact('providers', 'models', 'apiKeys', 'routingRules', 'healthSummary'));
    }

    public function settings()
    {
        // Get settings grouped by their group for dynamic rendering in Blade
        $settings = Setting::all()->groupBy('group');

        return view('hubs.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $cacheService = app(SettingCacheService::class);
        $updatedKeys = [];

        // Expecting data in a structured format: { 'key': 'value' }
        $data = $request->all();

        // Remove Laravel tokens and metadata
        unset($data['_token'], $data['_method']);

        foreach ($data as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if ($setting) {
                // Handle type casting for the value
                if ($setting->type === Setting::TYPE_BOOLEAN) {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                } elseif ($setting->type === Setting::TYPE_INTEGER) {
                    $value = (int) $value;
                } elseif ($setting->type === Setting::TYPE_JSON && is_array($value)) {
                    $value = json_encode($value);
                }

                $setting->update(['value' => $value]);
                $updatedKeys[] = $key;

                try {
                    $cacheService->forget($key);
                } catch (\Exception $e) {
                    // ignore cache failures
                }
            }
        }

        return response()->json([
            'success' => true,
            'updated_count' => count($updatedKeys),
            'updated_keys' => $updatedKeys,
        ]);
    }

    public function clearSettingsCache()
    {
        $cacheService = app(SettingCacheService::class);
        $cacheService->clear();

        return response()->json([
            'success' => true,
            'message' => 'Settings cache cleared successfully!',
        ]);
    }

    public function peopleConnect(Request $request)
    {
        $contacts = Contact::withCount('messages')->orderBy('updated_at', 'desc')->get();
        $selectedContactId = $request->query('contact_id');
        $selectedContact = null;
        $messages = [];

        if ($selectedContactId) {
            $selectedContact = Contact::find($selectedContactId);
            if ($selectedContact) {
                // Assuming we use 'ContactMessage' or 'Message' table. Let's use Message for now.
                // Or maybe just the contact's messages relation if it exists.
                // Let's assume Contact has a messages() relation.
                if (method_exists($selectedContact, 'messages')) {
                    $messages = $selectedContact->messages()->orderBy('created_at', 'asc')->get();
                } else {
                    $messages = ContactMessage::where('contact_id', $selectedContactId)->orderBy('created_at', 'asc')->get();
                }
            }
        }

        return view('hubs.people-connect', compact('contacts', 'selectedContact', 'messages'));
    }

    public function hedraSoul(Request $request)
    {
        $sessions = HedrasoulSession::orderBy('updated_at', 'desc')->get();
        $selectedSessionId = $request->query('session_id');
        $selectedSession = null;
        $messages = [];

        if ($selectedSessionId) {
            $selectedSession = HedrasoulSession::find($selectedSessionId);
        } elseif ($sessions->count() > 0) {
            $selectedSession = $sessions->first();
        }

        if ($selectedSession) {
            $messages = HedrasoulMessage::where('session_id', $selectedSession->id)
                ->orderBy('created_at', 'asc')->get();
        }

        return view('hubs.hedra-soul', compact('sessions', 'selectedSession', 'messages'));
    }

    public function proactiveAi()
    {
        $triggers = ProactiveTrigger::orderBy('next_run_at', 'asc')->get();
        $logs = NotificationLog::orderBy('created_at', 'desc')->take(10)->get();

        return view('hubs.proactive-ai', compact('triggers', 'logs'));
    }

    public function notifications()
    {
        $notifications = HedrasoulNotification::orderBy('created_at', 'desc')
            ->paginate(50);

        return view('hubs.notifications', compact('notifications'));
    }

    public function scheduler()
    {
        $schedules = WorkflowSchedule::with('workflow')->orderBy('next_run_at', 'asc')->get();

        return view('hubs.scheduler', compact('schedules'));
    }

    public function apis()
    {
        return view('hubs.apis');
    }

    public function admin()
    {
        return view('hubs.admin');
    }

    public function waha()
    {
        return view('hubs.waha');
    }

    public function triggerWahaSync(Request $request)
    {
        $type = $request->input('type');

        if ($type === 'Messages') {
            SyncWahaMessagesJob::dispatch();
        } else {
            SyncWahaContactsJob::dispatch();
        }

        return response()->json(['success' => true, 'message' => "Sync process dispatched for {$type}"]);
    }

    public function sendContactMessage(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|integer',
            'content' => 'required|string',
        ]);

        $message = new ContactMessage;
        $message->contact_id = $validated['contact_id'];
        $message->body = $validated['content'];
        $message->direction = 'outbound';
        $message->channel = 'whatsapp';
        $message->source = 'web';
        $message->source_timestamp = now();
        $message->save();

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function sendHedraMessage(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'nullable|integer',
            'content' => 'nullable|string',
            'message' => 'nullable|string',
            'context' => 'nullable|string',
        ]);

        $body = $validated['content'] ?? $validated['message'] ?? '';
        $sessionId = $validated['session_id'] ?? null;

        // Resolve or create a session if it's missing (e.g. from the dashboard)
        if (! $sessionId) {
            $session = HedrasoulSession::where('status', 'active')
                ->orWhere('status', 'open')
                ->orderBy('updated_at', 'desc')
                ->first();
            if (! $session) {
                $session = HedrasoulSession::create([
                    'title' => 'Dashboard Chat Session',
                    'status' => 'active',
                    'last_autonomy_mode' => 'copilot',
                    'opened_at' => now(),
                ]);
            }
            $sessionId = $session->id;
        }

        // Save User Message
        $message = new HedrasoulMessage;
        $message->session_id = $sessionId;
        $message->sender_type = 'user';
        $message->body = $body;
        $message->status = 'sent';
        $message->save();

        // Call LLM using UniversalAiGatewayService
        $replyText = '';
        $tokensUsed = 0;
        try {
            $aiGateway = app('nexus.ai');
            $agent = Agent::where('status', 'active')->first() ?: Agent::first();
            if (! $agent) {
                $model = AIModel::where('status', 'active')->first();
                $agent = new Agent([
                    'name' => 'Souly',
                    'role' => 'Assistant',
                    'model' => $model ? ($model->external_id ?? $model->name) : 'gemini-1.5-flash',
                    'system_prompt' => 'You are Souly, a helpful AI assistant.',
                    'status' => 'active',
                ]);
            }

            $aiResult = $aiGateway->executeWithAgent($agent, [
                'input' => $body,
                'system_prompt' => $agent->system_prompt,
            ]);

            if (! empty($aiResult['text'])) {
                $replyText = $aiResult['text'];
                $tokensUsed = $aiResult['tokens'] ?? 0;
            } else {
                $replyText = 'I processed your request, but received an empty response.';
            }

            // Save actual usage log in usage_logs table
            try {
                \DB::table('usage_logs')->insert([
                    'provider_id' => $agent->ai_provider_id ?? null,
                    'model_id' => $agent->ai_model_id ?? null,
                    'intent_name' => 'agent_execution_'.$agent->id,
                    'input_tokens' => (int) ($tokensUsed * 0.4),
                    'output_tokens' => (int) ($tokensUsed * 0.6),
                    'total_cost' => round($tokensUsed * 0.000002, 6),
                    'timestamp' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $ex) {
            }

        } catch (\Exception $e) {
            \Log::error('AI Console execution failed: '.$e->getMessage());
            $replyText = 'I encountered an issue processing your request: '.$e->getMessage();
        }

        // Save Agent Response
        $reply = new HedrasoulMessage;
        $reply->session_id = $sessionId;
        $reply->sender_type = 'agent';
        $reply->body = $replyText;
        $reply->status = 'delivered';
        $reply->token_count = $tokensUsed;
        $reply->cost_usd = round($tokensUsed * 0.000002, 4);
        $reply->save();

        return response()->json([
            'success' => true,
            'reply' => $reply->body,
            'token_count' => $tokensUsed,
        ]);
    }

    public function executeWorkflow(Workflow $workflow, WorkflowExecutor $executor)
    {
        if ($workflow->isRunning()) {
            return response()->json([
                'code' => 'workflow_running',
                'message' => 'Workflow is already running',
            ], 409);
        }

        $result = $executor->execute($workflow, [], 'async', request()->user());

        $execution = WorkflowExecution::with('stepLogs')->find($result['execution_id']);

        return response()->json([
            'success' => true,
            'execution_id' => $execution->id,
            'status' => $execution->status,
            'message' => 'Workflow execution queued',
        ], 202);
    }

    public function showExecution(WorkflowExecution $execution)
    {
        $execution->load(['workflow', 'stepLogs' => fn ($query) => $query->orderBy('created_at')]);

        return response()->json([
            'success' => true,
            'execution' => $execution,
        ]);
    }

    public function toggleFavorite(Request $request, $id, LogService $logService)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $contact = Contact::findOrFail($id);
        $wasFavorite = $contact->isFavoritedBy($user);

        $user->favoriteContacts()->toggle($contact->id);
        $isFavorite = ! $wasFavorite;

        // Structured audit logging via LogService
        $logService->info('Contact favorite flag changed', [
            'channel' => 'contact',
            'type' => 'favorite_toggle',
            'related_id' => $contact->id,
            'related_type' => Contact::class,
            'user_id' => $user->id,
            'before' => $wasFavorite ? 'favorited' : 'unfavorited',
            'after' => $isFavorite ? 'favorited' : 'unfavorited',
            'actor' => $user->name,
        ]);

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite,
            'message' => $isFavorite ? 'Contact added to favorites.' : 'Contact removed from favorites.',
        ]);
    }

    public function logoutWeb(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('hub.dashboard');
    }

    public function restartAgent(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);

        // Set status to active (representing a restarted/re-initialised state)
        $agent->status = 'active';
        $agent->save();

        // Log application event in logs table
        try {
            \DB::table('logs')->insert([
                'level' => 'INFO',
                'channel' => 'system',
                'message' => "Agent '{$agent->name}' successfully restarted.",
                'type' => 'application',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
        }

        // Broadcast AgentStarted event
        try {
            event(new AgentStarted($agent));
        } catch (\Exception $e) {
        }

        return response()->json([
            'success' => true,
            'message' => "Agent '{$agent->name}' restarted successfully.",
        ]);
    }

    public function dashboardHealth(Request $request, NexusDashboardService $service)
    {
        return response()->json($service->getHealthStatus());
    }

    public function dashboardActivityFeed(Request $request, NexusDashboardService $service)
    {
        $limit = $request->query('limit', 20);

        return response()->json($service->getActivityFeed($limit));
    }
}
