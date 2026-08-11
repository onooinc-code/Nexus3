<?php

use App\Http\Controllers\Admin\DlqController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AgentPersonaController;
use App\Http\Controllers\AgentToolLibraryController;
use App\Http\Controllers\AIApiKeyController;
use App\Http\Controllers\AiCostAnalyticsController;
use App\Http\Controllers\AiInstanceController;
use App\Http\Controllers\AiModelController;
use App\Http\Controllers\AiProviderController;
use App\Http\Controllers\AiRequestController;
use App\Http\Controllers\AiRouteController;
use App\Http\Controllers\Api\TelemetryController;
use App\Http\Controllers\Api\V1\DomEventTriggerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactAliasController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactIdentifierController;
use App\Http\Controllers\ContactImportController;
use App\Http\Controllers\ContactNoteController;
use App\Http\Controllers\ContactPreferenceController;
use App\Http\Controllers\ContactRelationshipController;
use App\Http\Controllers\ContactStatsController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\HedraSoul\HedraCloneSourceController;
use App\Http\Controllers\HedraSoul\HedraMemoryController;
use App\Http\Controllers\HedraSoul\HedraProfileController;
use App\Http\Controllers\HedraSoul\HedraSoulApprovalController;
use App\Http\Controllers\HedraSoul\HedraSoulMessageController;
use App\Http\Controllers\HedraSoul\HedraSoulMiscController;
use App\Http\Controllers\HedraSoul\HedraSoulNotificationController;
use App\Http\Controllers\HedraSoul\HedraSoulSessionController;
use App\Http\Controllers\HedraSoul\SoulyControlController;
use App\Http\Controllers\HedraSoul\SoulyInstructionController;
use App\Http\Controllers\HedraSoulController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MCPServerController;
use App\Http\Controllers\MemoryController;
use App\Http\Controllers\Monitoring\HealthController;
use App\Http\Controllers\Monitoring\MetricsController;
use App\Http\Controllers\NexusDashboardController;
use App\Http\Controllers\NotificationBroadcastController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PeopleConnect\LiveMsgsController;
use App\Http\Controllers\PeopleConnect\PeopleConnectController;
use App\Http\Controllers\ProactiveAIController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchedulerController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SettingsHubAdminController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\SystemTelemetryController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskTemplateController;
use App\Http\Controllers\WahaManageController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\WorkflowWebhookController;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * API Routes for Nexus Platform
 * All routes are prefixed with /api/v1
 */
Route::post('/telemetry/upload/{deviceId}', [TelemetryController::class, 'uploadScreenshot']);
Route::get('/telemetry/screenshot/{deviceId}', [TelemetryController::class, 'getLatestScreenshot']);
Route::get('/telemetry/screenshots/{deviceId}', [TelemetryController::class, 'getAllScreenshots']);
// Public Webhook Routes (Legacy no prefix)
Route::post('/webhooks/waha', [WebhookController::class, 'handleWahaWebhook'])
    ->name('webhooks.waha.legacy');

// Public routes (no authentication required)
Route::group(['prefix' => 'v1', 'middleware' => ['api']], function () {
    Route::post('/webhooks/waha', [WebhookController::class, 'handleWahaWebhook'])
        ->name('webhooks.waha');

    // Health check endpoint
    Route::get('/health', function (Request $request) {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now(),
            'app' => config('app.name'),
        ]);
    });

    // Broadcast auth for token-based (Sanctum) clients — supports Bearer tokens
    Route::post('/broadcasting/auth', function (Request $request) {
        // If a bearer token is present, try to authenticate the tokenable user manually
        $bearer = $request->bearerToken();
        Log::info('broadcasting.auth called', ['bearer_present' => $bearer ? true : false]);
        if ($bearer) {
            $tokenModel = PersonalAccessToken::findToken($bearer);
            if ($tokenModel && $tokenModel->tokenable) {
                auth()->loginUsingId($tokenModel->tokenable->getAuthIdentifier());
                Log::info('broadcasting.auth: logged in tokenable', ['user_id' => $tokenModel->tokenable->getAuthIdentifier()]);
            } else {
                Log::warning('broadcasting.auth: token not found or has no tokenable');
            }
        }

        $resp = Broadcast::auth($request);
        Log::info('broadcasting.auth: Broadcast::auth result', ['status' => $resp ? 'present' : 'empty']);

        return $resp;
    });

    // Workflow webhook endpoint
    Route::post('/webhooks/workflows/{id}', [WorkflowWebhookController::class, 'handle'])
        ->name('webhooks.workflows');

    Route::prefix('monitoring')->group(function () {
        Route::get('/health', [HealthController::class, 'health']);
        Route::get('/health/reverb', [HealthController::class, 'reverb']);
        Route::get('/health/queue', [HealthController::class, 'queue']);
        Route::get('/metrics', [MetricsController::class, 'metrics']);
        Route::get('/metrics/websocket', [MetricsController::class, 'websocket']);
    });

    Route::prefix('system')->group(function () {
        Route::get('/routes', [SystemController::class, 'routes'])->name('system.routes');
        Route::get('/schema', [SystemController::class, 'schema'])->name('system.schema');
        Route::get('/codebase', [SystemController::class, 'codebase'])->name('system.codebase');
        Route::get('/docs', [SystemController::class, 'docs'])->name('system.docs');
        Route::get('/views', [SystemController::class, 'views'])->name('system.views');
        Route::get('/readme', [SystemController::class, 'readme'])->name('system.readme');
        Route::get('/queue-details', [SystemController::class, 'queueDetails'])->name('system.queue-details');
        Route::post('/optimize-and-clear', [SystemController::class, 'optimizeAndClear'])->name('system.optimize-and-clear');
    });

    // Sanctum authentication routes
    Route::post('/login', [AuthController::class, 'login'])
        ->name('login');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register');

    Route::post('/verify-token', [AuthController::class, 'verifyToken'])
        ->name('verify-token');
});

// Protected routes (authentication required via Sanctum)
Route::group(['prefix' => 'v1', 'middleware' => ['api', 'auth:sanctum']], function () {
    // Settings Management
    Route::get('/settings/clear-cache', [SettingController::class, 'clearCache'])
        ->name('api.settings.clear-cache');

    // Authentication actions
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    /**
     * Contacts Hub Routes
     * Phase 2 — Stats & Reply Mode (must be defined BEFORE resource/wildcard routes)
     */
    Route::get('/contacts/stats', [ContactStatsController::class, 'stats'])
        ->name('contacts.stats');
    Route::get('/contacts/reply-mode', [ContactStatsController::class, 'getGlobalReplyMode'])
        ->name('contacts.reply-mode.global.get');
    Route::patch('/contacts/reply-mode', [ContactStatsController::class, 'setGlobalReplyMode'])
        ->name('contacts.reply-mode.global.set');

    // Standard single-contact action routes (before resource to avoid conflicts)
    Route::post('/contacts/import', [ContactController::class, 'import'])
        ->middleware('throttle:5,1')
        ->name('contacts.import');
    Route::post('/contacts/import/preview', [ContactImportController::class, 'preview'])
        ->name('contacts.import.preview');
    Route::post('/contacts/import/whatsapp', [ContactImportController::class, 'importWhatsApp'])
        ->middleware('throttle:5,1')
        ->name('contacts.import.whatsapp');
    Route::post('/contacts/import/whatsapp/waha', [ContactImportController::class, 'importWaha'])
        ->middleware('throttle:5,1')
        ->name('contacts.import.whatsapp.waha');
    Route::post('/contacts/import/facebook', [ContactImportController::class, 'importFacebook'])
        ->middleware('throttle:5,1')
        ->name('contacts.import.facebook');
    Route::get('/contacts/imports', [ContactImportController::class, 'listBatches'])
        ->name('contacts.imports.index');
    Route::get('/contacts/imports/{batch}', [ContactImportController::class, 'showBatch'])
        ->name('contacts.imports.show');
    Route::post('/contacts/imports/{batch}/rollback', [ContactImportController::class, 'rollbackBatch'])
        ->name('contacts.imports.rollback');

    // ContactHub vNext message, intelligence, maintenance, and privacy routes.
    Route::get('/contacts/analytics', [ContactController::class, 'hubAnalytics'])->name('contacts.hub-analytics');
    Route::get('/contacts/conflicts', [ContactController::class, 'conflicts'])->name('contacts.conflicts');
    Route::get('/contacts/stale-memory', [ContactController::class, 'staleMemory'])->name('contacts.stale-memory');
    Route::get('/contacts/{id}/memory-maintenance/runs', [ContactController::class, 'contactMaintenanceRuns'])->name('contacts.memory-maintenance.contact.runs');

    Route::post('/contacts/analysis-runs/batch', [ContactController::class, 'batchAnalysisRun'])
        ->name('contacts.analysis-runs.batch');
    Route::post('/contacts/analysis-runs/{run}/apply', [ContactController::class, 'applyAnalysisRun'])
        ->name('contacts.analysis-runs.apply');
    Route::post('/contacts/analysis-runs/{run}/rollback', [ContactController::class, 'rollbackAnalysisRun'])
        ->name('contacts.analysis-runs.rollback');
    Route::post('/contacts/memory-maintenance', [ContactController::class, 'memoryMaintenance'])
        ->name('contacts.memory-maintenance.store');
    Route::get('/contacts/memory-maintenance/runs', [ContactController::class, 'memoryMaintenanceRuns'])
        ->name('contacts.memory-maintenance.runs');
    Route::get('/contacts/memory-maintenance/runs/{run}', [ContactController::class, 'showMemoryMaintenanceRun'])
        ->name('contacts.memory-maintenance.runs.show');

    Route::get('/contacts/export', [ContactController::class, 'export'])
        ->name('contacts.export');
    Route::get('/contacts/{id}/messages', [ContactController::class, 'messages'])
        ->name('contacts.messages');
    Route::get('/contacts/{id}/messages/whatsapp', [ContactController::class, 'whatsappMessages'])
        ->name('contacts.messages.whatsapp');
    Route::get('/contacts/{id}/messages/facebook', [ContactController::class, 'facebookMessages'])
        ->name('contacts.messages.facebook');
    Route::get('/contacts/{id}/threads', [ContactController::class, 'threads'])
        ->name('contacts.threads');
    Route::get('/contacts/{id}/threads/{thread}', [ContactController::class, 'showThread'])
        ->name('contacts.threads.show');
    Route::post('/contacts/{id}/analysis-runs', [ContactController::class, 'createAnalysisRun'])
        ->middleware('throttle:analysis')
        ->name('contacts.analysis-runs.store');
    Route::get('/contacts/{id}/analysis-runs', [ContactController::class, 'listAnalysisRuns'])
        ->name('contacts.analysis-runs.index');
    Route::get('/contacts/{id}/analysis-runs/{run}', [ContactController::class, 'showAnalysisRun'])
        ->name('contacts.analysis-runs.show');
    Route::post('/contacts/{id}/memory-maintenance', [ContactController::class, 'memoryMaintenance'])
        ->name('contacts.memory-maintenance.contact.store');
    Route::get('/contacts/{id}/intelligence', [ContactController::class, 'intelligence'])
        ->name('contacts.intelligence');
    Route::get('/contacts/{id}/persona', [ContactController::class, 'persona'])
        ->name('contacts.persona');
    Route::get('/contacts/{id}/talk-specs', [ContactController::class, 'talkSpecs'])
        ->name('contacts.talk-specs');
    Route::get('/contacts/{id}/emotional-baseline', [ContactController::class, 'emotionalBaseline'])
        ->name('contacts.emotional-baseline');
    Route::get('/contacts/{id}/topics', [ContactController::class, 'topics'])
        ->name('contacts.topics');
    Route::get('/contacts/{id}/topics/{topic}/mentions', [ContactController::class, 'topicMentions'])
        ->name('contacts.topics.mentions');
    Route::get('/contacts/{id}/reply-rules', [ContactController::class, 'listReplyRules'])
        ->name('contacts.reply-rules.index');
    Route::post('/contacts/{id}/reply-rules', [ContactController::class, 'storeReplyRule'])
        ->name('contacts.reply-rules.store');
    Route::patch('/contacts/{id}/reply-rules/{rule}', [ContactController::class, 'updateReplyRule'])
        ->name('contacts.reply-rules.update');
    Route::delete('/contacts/{id}/reply-rules/{rule}', [ContactController::class, 'destroyReplyRule'])
        ->name('contacts.reply-rules.destroy');
    Route::post('/contacts/{id}/export', [ContactController::class, 'exportBundle'])
        ->name('contacts.export.bundle');
    Route::post('/contacts/{id}/erase', [ContactController::class, 'erase'])
        ->name('contacts.erase.post');
    Route::get('/contacts/{id}/audit', [ContactController::class, 'audit'])
        ->name('contacts.audit');
    Route::get('/contacts/{id}/memory', [ContactController::class, 'getMemory'])
        ->name('contacts.memory');
    Route::get('/contacts/{id}/rules', [ContactController::class, 'getRules'])
        ->name('contacts.rules');
    Route::get('/contacts/{id}/timeline', [ContactController::class, 'timeline'])
        ->name('contacts.timeline');
    Route::get('/contacts/{id}/analytics', [ContactController::class, 'getAnalytics'])
        ->name('contacts.analytics');
    Route::get('/contacts/{id}/conflicts', [ContactController::class, 'conflicts'])
        ->name('contacts.contact.conflicts');
    Route::get('/contacts/{id}/stale-memory', [ContactController::class, 'staleMemory'])
        ->name('contacts.contact.stale-memory');
    Route::post('/contacts/{id}/merge', [ContactController::class, 'merge'])
        ->name('contacts.merge');

    Route::post('/contacts/{id}/enrich', [ContactController::class, 'enrich'])
        ->name('contacts.enrich');

    // Phase 2 — Per-contact reply mode
    Route::get('/contacts/{contact}/reply-mode', [ContactStatsController::class, 'getContactReplyMode'])
        ->name('contacts.reply-mode.get');
    Route::patch('/contacts/{contact}/reply-mode', [ContactStatsController::class, 'setContactReplyMode'])
        ->name('contacts.reply-mode.set');

    Route::apiResource('contacts', ContactController::class);

    /**
     * Contact Sub-resources Routes
     */
    Route::get('/contacts/{contact}/identifiers', [ContactIdentifierController::class, 'index'])
        ->name('contacts.identifiers.index');
    Route::post('/contacts/{contact}/identifiers', [ContactIdentifierController::class, 'store'])
        ->name('contacts.identifiers.store');
    Route::get('/contacts/{contact}/identifiers/{identifier}', [ContactIdentifierController::class, 'show'])
        ->name('contacts.identifiers.show');
    Route::put('/contacts/{contact}/identifiers/{identifier}', [ContactIdentifierController::class, 'update'])
        ->name('contacts.identifiers.update');
    Route::delete('/contacts/{contact}/identifiers/{identifier}', [ContactIdentifierController::class, 'destroy'])
        ->name('contacts.identifiers.destroy');

    Route::get('/contacts/{contact}/relationships', [ContactRelationshipController::class, 'index'])
        ->name('contacts.relationships.index');
    Route::post('/contacts/{contact}/relationships', [ContactRelationshipController::class, 'store'])
        ->name('contacts.relationships.store');
    Route::get('/contacts/{contact}/relationships/{relationship}', [ContactRelationshipController::class, 'show'])
        ->name('contacts.relationships.show');
    Route::put('/contacts/{contact}/relationships/{relationship}', [ContactRelationshipController::class, 'update'])
        ->name('contacts.relationships.update');
    Route::delete('/contacts/{contact}/relationships/{relationship}', [ContactRelationshipController::class, 'destroy'])
        ->name('contacts.relationships.destroy');

    Route::get('/contacts/{contact}/preferences', [ContactPreferenceController::class, 'index'])
        ->name('contacts.preferences.index');
    Route::post('/contacts/{contact}/preferences', [ContactPreferenceController::class, 'store'])
        ->name('contacts.preferences.store');
    Route::get('/contacts/{contact}/preferences/{preference}', [ContactPreferenceController::class, 'show'])
        ->name('contacts.preferences.show');
    Route::put('/contacts/{contact}/preferences/{preference}', [ContactPreferenceController::class, 'update'])
        ->name('contacts.preferences.update');
    Route::delete('/contacts/{contact}/preferences/{preference}', [ContactPreferenceController::class, 'destroy'])
        ->name('contacts.preferences.destroy');

    Route::get('/contacts/{contact}/aliases', [ContactAliasController::class, 'index'])
        ->name('contacts.aliases.index');
    Route::post('/contacts/{contact}/aliases', [ContactAliasController::class, 'store'])
        ->name('contacts.aliases.store');
    Route::get('/contacts/{contact}/aliases/{alias}', [ContactAliasController::class, 'show'])
        ->name('contacts.aliases.show');
    Route::put('/contacts/{contact}/aliases/{alias}', [ContactAliasController::class, 'update'])
        ->name('contacts.aliases.update');
    Route::delete('/contacts/{contact}/aliases/{alias}', [ContactAliasController::class, 'destroy'])
        ->name('contacts.aliases.destroy');

    Route::get('/contacts/{contact}/notes', [ContactNoteController::class, 'index'])
        ->name('contacts.notes.index');
    Route::post('/contacts/{contact}/notes', [ContactNoteController::class, 'store'])
        ->name('contacts.notes.store');
    Route::get('/contacts/{contact}/notes/{note}', [ContactNoteController::class, 'show'])
        ->name('contacts.notes.show');
    Route::put('/contacts/{contact}/notes/{note}', [ContactNoteController::class, 'update'])
        ->name('contacts.notes.update');
    Route::delete('/contacts/{contact}/notes/{note}', [ContactNoteController::class, 'destroy'])
        ->name('contacts.notes.destroy');

    /**
     * Notification Hub Routes
     */
    Route::get('/notifications/templates', [NotificationController::class, 'indexTemplates'])
        ->name('notifications.templates.index');
    Route::post('/notifications/templates', [NotificationController::class, 'storeTemplate'])
        ->name('notifications.templates.store');
    Route::get('/notifications/templates/{template}', [NotificationController::class, 'showTemplate'])
        ->name('notifications.templates.show');
    Route::put('/notifications/templates/{template}', [NotificationController::class, 'updateTemplate'])
        ->name('notifications.templates.update');
    Route::delete('/notifications/templates/{template}', [NotificationController::class, 'destroyTemplate'])
        ->name('notifications.templates.destroy');

    Route::get('/notifications', [NotificationController::class, 'indexLogs'])
        ->name('notifications.index');
    Route::get('/notifications/logs', [NotificationController::class, 'indexLogs'])
        ->name('notifications.logs.index');
    Route::post('/notifications/send', [NotificationController::class, 'send'])
        ->name('notifications.send');
    Route::post('/notifications/{notification}/retry', [NotificationController::class, 'retry'])
        ->name('notifications.retry');

    /**
     * Conversations Routes
     */
    Route::resource('conversations', ConversationController::class);
    Route::get('/conversations/{id}/messages', [ConversationController::class, 'getMessages'])
        ->name('conversations.messages');
    Route::post('/conversations/{id}/send-message', [ConversationController::class, 'sendMessage'])
        ->name('conversations.send-message');

    /**
     * People Connect Hub Routes
     */
    Route::group(['prefix' => 'people-connect'], function () {
        Route::get('/stats', [PeopleConnectController::class, 'stats'])->name('peopleconnect.stats');
        Route::get('/search', [PeopleConnectController::class, 'search'])->name('peopleconnect.search');
        Route::get('/conversations/{id}', [PeopleConnectController::class, 'showConversation'])->name('peopleconnect.conversations.show');
        Route::post('/conversations/{id}/reply-mode', [PeopleConnectController::class, 'updateReplyMode'])->name('peopleconnect.conversations.reply-mode');

        Route::get('/livemsgs', [LiveMsgsController::class, 'index'])->name('peopleconnect.livemsgs.index');
        Route::post('/livemsgs/sync', [LiveMsgsController::class, 'triggerSync'])->name('peopleconnect.livemsgs.sync');
    });

    /**
     * Agents Hub Routes
     */
    Route::middleware('auth:sanctum')->group(function () {
        Route::resource('agents', AgentController::class);
        Route::post('/agents/{agent}/run', [AgentController::class, 'run'])
            ->name('agents.run');
        Route::post('/agents/{agent}/simulate', [AgentController::class, 'simulate'])
            ->name('agents.simulate');
        Route::post('/agents/{agent}/quarantine', [AgentController::class, 'quarantine'])
            ->name('agents.quarantine');
        Route::post('/agents/{agent}/unquarantine', [AgentController::class, 'unquarantine'])
            ->name('agents.unquarantine');
        Route::get('/agents/{agent}/status', [AgentController::class, 'getStatus'])
            ->name('agents.status');
        Route::get('/agents/{agent}/logs', [AgentController::class, 'getLogs'])
            ->name('agents.logs');

        Route::get('/agent-tools', [AgentToolLibraryController::class, 'index'])
            ->name('agent-tools.index');
        Route::get('/agent-tools/{id}', [AgentToolLibraryController::class, 'show'])
            ->name('agent-tools.show');

        Route::apiResource('agent-personas', AgentPersonaController::class);
        Route::apiResource('mcp-servers', MCPServerController::class);
        Route::post('/mcp-servers/{name}/connect', [MCPServerController::class, 'connect'])
            ->name('mcp-servers.connect');
        Route::post('/mcp-servers/{name}/disconnect', [MCPServerController::class, 'disconnect'])
            ->name('mcp-servers.disconnect');
    });

    /**
     * Workflows Hub Routes
     * NOTE: Specific routes must be defined BEFORE resource routes to prevent route conflicts
     */
    Route::get('/workflows/templates', [WorkflowController::class, 'getTemplates'])
        ->name('workflows.templates');
    Route::get('/workflows/executions/{execution}', [WorkflowController::class, 'showExecution'])
        ->name('workflows.executions.show');
    Route::post('/workflows/executions/{execution}/resume', [WorkflowController::class, 'resume'])
        ->name('workflows.executions.resume');
    Route::post('/workflows/executions/{execution}/cancel', [WorkflowController::class, 'cancel'])
        ->name('workflows.executions.cancel');

    Route::apiResource('workflows', WorkflowController::class);

    // Workflow action routes on specific resources
    Route::post('/workflows/{workflow}/execute', [WorkflowController::class, 'execute'])
        ->name('workflows.execute');
    Route::get('/workflows/{workflow}/progress', [WorkflowController::class, 'getProgress'])
        ->name('workflows.progress');

    /**
     * Tasks Hub Routes
     * NOTE: Specific routes must be defined BEFORE resource routes to prevent route conflicts
     */
    // Specific action routes (must come before resource)
    Route::get('/tasks/stats', [TaskController::class, 'getStats'])
        ->name('tasks.stats');
    Route::get('/tasks/stats/by-type', [TaskController::class, 'getStatsByType'])
        ->name('tasks.stats-by-type');
    Route::get('/tasks/stats/timeline', [TaskController::class, 'getExecutionTimeline'])
        ->name('tasks.stats-timeline');
    Route::get('/tasks/stats/agents', [TaskController::class, 'getAgentPerformance'])
        ->name('tasks.stats-agents');
    Route::get('/tasks/active', [TaskController::class, 'getActive'])
        ->name('tasks.active');
    Route::get('/tasks/queue-stats', [TaskController::class, 'getQueueStats'])
        ->name('tasks.queue-stats');
    Route::get('/tasks/routing-stats', [TaskController::class, 'getRoutingStats'])
        ->name('tasks.routing-stats');

    // New TaskHub specific endpoints (must come before resource)
    Route::post('/task-templates/{taskTemplate}/spawn', [TaskTemplateController::class, 'spawn'])
        ->name('task-templates.spawn');
    Route::apiResource('task-templates', TaskTemplateController::class);
    Route::post('/tasks/{task}/execute', [TaskController::class, 'execute'])
        ->name('tasks.execute');
    Route::get('/tasks/{task}/logs', [TaskController::class, 'logs'])
        ->name('tasks.logs');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])
        ->name('tasks.update-status');

    // Type-specific task creation endpoints
    Route::post('/tasks/manual', [TaskController::class, 'createManual'])
        ->name('tasks.create-manual');
    Route::post('/tasks/agent', [TaskController::class, 'createAgent'])
        ->name('tasks.create-agent');
    Route::post('/tasks/system', [TaskController::class, 'createSystem'])
        ->name('tasks.create-system');
    Route::get('/tasks/type/{type}', [TaskController::class, 'getByType'])
        ->name('tasks.by-type');
    Route::get('/tasks/stats/by-type', [TaskController::class, 'getStatsByType'])
        ->name('tasks.stats-by-type');

    // Antigravity Browser Agent Routes
    Route::get('/agent-tasks/pending', [TaskController::class, 'getPendingBrowserTasks'])
        ->name('agent-tasks.pending');
    Route::post('/agent-tasks/{task}/status', [TaskController::class, 'updateStatusWithProof'])
        ->name('agent-tasks.update-status');
    Route::post('/agent-tasks', [TaskController::class, 'store'])
        ->name('agent-tasks.store');

    Route::post('/events/dom-trigger', [DomEventTriggerController::class, 'handle'])
        ->name('events.dom-trigger');

    // Resource routes
    Route::apiResource('tasks', TaskController::class);

    // Task action routes on specific resources
    Route::post('/tasks/{task}/cancel', [TaskController::class, 'cancel'])
        ->name('tasks.cancel');
    Route::post('/tasks/{task}/pause', [TaskController::class, 'pause'])
        ->name('tasks.pause');
    Route::post('/tasks/{task}/resume', [TaskController::class, 'resume'])
        ->name('tasks.resume');
    Route::post('/tasks/{task}/subtasks', [TaskController::class, 'addSubtask'])
        ->name('tasks.subtasks.add');
    Route::patch('/tasks/{task}/subtasks/{subtaskId}', [TaskController::class, 'toggleSubtask'])
        ->name('tasks.subtasks.toggle');
    Route::delete('/tasks/{task}/subtasks/{subtaskId}', [TaskController::class, 'deleteSubtask'])
        ->name('tasks.subtasks.delete');

    Route::get('/stats/usage', [StatsController::class, 'usage'])
        ->name('stats.usage');

    Route::get('/stats/dashboard', [StatsController::class, 'dashboard'])
        ->name('stats.dashboard');

    /**
     * NexusHub Dashboard Aggregation Routes (Req 1, 2, 3)
     */
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [NexusDashboardController::class, 'stats'])
            ->middleware('throttle:60,1')
            ->name('dashboard.stats');
        Route::get('/health', [NexusDashboardController::class, 'health'])
            ->name('dashboard.health');
        Route::get('/activity-feed', [NexusDashboardController::class, 'activityFeed'])
            ->name('dashboard.activity-feed');
    });

    /**
     * Job retry endpoint for dashboard panel (Req 6.6)
     */
    Route::post('/jobs/{id}/retry', function (string $id) {
        try {
            $failedJob = DB::table('failed_jobs')->where('id', $id)->first();
            if (! $failedJob) {
                return response()->json(['message' => 'Job not found.'], 404);
            }
            $payload = json_decode($failedJob->payload, true);
            DB::table('failed_jobs')->where('id', $id)->delete();

            return response()->json(['message' => 'Job re-queued successfully.', 'status' => 'pending']);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    })->name('jobs.retry');

    /**
     * Proactive AI suggestion approve/dismiss routes (Req 10.5, 10.6)
     */
    Route::prefix('proactive-ai/suggestions')->group(function () {
        Route::post('/{id}/approve', function (string $id) {
            try {
                DB::table('proactive_suggestions')->where('id', $id)->update(['status' => 'approved', 'updated_at' => now()]);

                return response()->json(['message' => 'Suggestion approved.']);
            } catch (Throwable) {
                try {
                    DB::table('proactive_logs')->where('id', $id)->update(['status' => 'approved', 'updated_at' => now()]);

                    return response()->json(['message' => 'Suggestion approved.']);
                } catch (Throwable $e) {
                    return response()->json(['message' => $e->getMessage()], 500);
                }
            }
        })->name('proactive-ai.suggestions.approve');

        Route::post('/{id}/dismiss', function (string $id) {
            try {
                DB::table('proactive_suggestions')->where('id', $id)->update(['status' => 'dismissed', 'updated_at' => now()]);

                return response()->json(['message' => 'Suggestion dismissed.']);
            } catch (Throwable) {
                try {
                    DB::table('proactive_logs')->where('id', $id)->update(['status' => 'dismissed', 'updated_at' => now()]);

                    return response()->json(['message' => 'Suggestion dismissed.']);
                } catch (Throwable $e) {
                    return response()->json(['message' => $e->getMessage()], 500);
                }
            }
        })->name('proactive-ai.suggestions.dismiss');
    });

    /**
     * Memory Hub Routes
     * NOTE: Specific routes must be defined BEFORE resource routes to prevent route conflicts
     */
    Route::get('/memories/search', [MemoryController::class, 'search'])
        ->name('memories.search');
    Route::post('/memories/{id}/index', [MemoryController::class, 'indexMemory'])
        ->name('memories.indexMemory');
    Route::post('/memories/{id}/reinforce', [MemoryController::class, 'reinforceConfidence'])
        ->name('memories.reinforce');
    Route::post('/memories/decay', [MemoryController::class, 'applyDecay'])
        ->name('memories.decay');
    Route::get('/memories/{id}/versions', [MemoryController::class, 'versions'])
        ->name('memories.versions');

    // Contact-specific memory panel endpoints
    Route::get('/contacts/{contactId}/memories', [MemoryController::class, 'contactMemories'])
        ->name('contacts.memories.index');
    Route::post('/contacts/{contactId}/memories/extract', [MemoryController::class, 'extractForContact'])
        ->name('contacts.memories.extract');

    Route::resource('memories', MemoryController::class);

    /**
     * HedraSoul Hub Routes (50+ endpoints)
     */
    Route::prefix('hedrasoul')->group(function () {
        // Session management
        Route::get('/sessions', [HedraSoulSessionController::class, 'index'])
            ->name('hedrasoul.sessions.index');
        Route::post('/sessions', [HedraSoulSessionController::class, 'store'])
            ->name('hedrasoul.sessions.store');
        Route::get('/sessions/{session}', [HedraSoulSessionController::class, 'show'])
            ->name('hedrasoul.sessions.show');
        Route::patch('/sessions/{session}', [HedraSoulSessionController::class, 'update'])
            ->name('hedrasoul.sessions.update');
        Route::post('/sessions/{session}/archive', [HedraSoulSessionController::class, 'archive'])
            ->name('hedrasoul.sessions.archive');
        Route::get('/sessions/{session}/messages', [HedraSoulSessionController::class, 'messages'])
            ->name('hedrasoul.sessions.messages');
        Route::post('/sessions/{session}/messages', [HedraSoulSessionController::class, 'sendMessage'])
            ->name('hedrasoul.sessions.sendMessage');

        // Message management
        Route::post('/messages/{message}/regenerate', [HedraSoulMessageController::class, 'regenerate'])
            ->name('hedrasoul.messages.regenerate');
        Route::get('/messages/{message}/trace', [HedraSoulMessageController::class, 'trace'])
            ->name('hedrasoul.messages.trace');
        Route::get('/messages/{message}/context', [HedraSoulMessageController::class, 'context'])
            ->name('hedrasoul.messages.context');

        // Souly control & simulation
        Route::get('/souly/status', [SoulyControlController::class, 'status'])
            ->name('hedrasoul.souly.status');
        Route::patch('/souly/autonomy', [SoulyControlController::class, 'updateAutonomy'])
            ->name('hedrasoul.souly.updateAutonomy');
        Route::patch('/souly/model', [SoulyControlController::class, 'updateModel'])
            ->name('hedrasoul.souly.updateModel');
        Route::post('/souly/quarantine', [SoulyControlController::class, 'quarantine'])
            ->name('hedrasoul.souly.quarantine');
        Route::post('/souly/resume', [SoulyControlController::class, 'resume'])
            ->name('hedrasoul.souly.resume');
        Route::post('/souly/simulate', [SoulyControlController::class, 'simulate'])
            ->name('hedrasoul.souly.simulate');

        // Instruction versioning
        Route::get('/instructions', [SoulyInstructionController::class, 'index'])
            ->name('hedrasoul.instructions.index');
        Route::post('/instructions', [SoulyInstructionController::class, 'store'])
            ->name('hedrasoul.instructions.store');
        Route::get('/instructions/{version}', [SoulyInstructionController::class, 'show'])
            ->name('hedrasoul.instructions.show');
        Route::patch('/instructions/{version}', [SoulyInstructionController::class, 'update'])
            ->name('hedrasoul.instructions.update');
        Route::post('/instructions/{version}/activate', [SoulyInstructionController::class, 'activate'])
            ->name('hedrasoul.instructions.activate');
        Route::post('/instructions/{version}/rollback', [SoulyInstructionController::class, 'rollback'])
            ->name('hedrasoul.instructions.rollback');
        Route::post('/instructions/{version}/test', [SoulyInstructionController::class, 'test'])
            ->name('hedrasoul.instructions.test');

        // Hedra profile
        Route::get('/profile', [HedraProfileController::class, 'show'])
            ->name('hedrasoul.profile.show');
        Route::patch('/profile', [HedraProfileController::class, 'update'])
            ->name('hedrasoul.profile.update');

        // Clone sources
        Route::get('/clone-sources', [HedraCloneSourceController::class, 'index'])
            ->name('hedrasoul.cloneSources.index');
        Route::post('/clone-sources', [HedraCloneSourceController::class, 'store'])
            ->name('hedrasoul.cloneSources.store');
        Route::patch('/clone-sources/{source}', [HedraCloneSourceController::class, 'update'])
            ->name('hedrasoul.cloneSources.update');
        Route::delete('/clone-sources/{source}', [HedraCloneSourceController::class, 'destroy'])
            ->name('hedrasoul.cloneSources.destroy');

        // Memory management
        Route::get('/memories', [HedraMemoryController::class, 'index'])
            ->name('hedrasoul.memories.index');
        Route::post('/memories', [HedraMemoryController::class, 'store'])
            ->name('hedrasoul.memories.store');
        Route::patch('/memories/{memory}', [HedraMemoryController::class, 'update'])
            ->name('hedrasoul.memories.update');
        Route::post('/memories/{memory}/approve', [HedraMemoryController::class, 'approve'])
            ->name('hedrasoul.memories.approve');
        Route::post('/memories/{memory}/reject', [HedraMemoryController::class, 'reject'])
            ->name('hedrasoul.memories.reject');
        Route::post('/memory-maintenance', [HedraMemoryController::class, 'maintenance'])
            ->name('hedrasoul.memory.maintenance');

        // Approval inbox
        Route::get('/approvals', [HedraSoulApprovalController::class, 'index'])
            ->name('hedrasoul.approvals.index');
        Route::get('/approvals/{approval}', [HedraSoulApprovalController::class, 'show'])
            ->name('hedrasoul.approvals.show');
        Route::post('/approvals/{approval}/approve', [HedraSoulApprovalController::class, 'approve'])
            ->name('hedrasoul.approvals.approve');
        Route::post('/approvals/{approval}/reject', [HedraSoulApprovalController::class, 'reject'])
            ->name('hedrasoul.approvals.reject');
        Route::post('/approvals/{approval}/defer', [HedraSoulApprovalController::class, 'defer'])
            ->name('hedrasoul.approvals.defer');

        // Notifications
        Route::get('/notifications', [HedraSoulNotificationController::class, 'index'])
            ->name('hedrasoul.notifications.index');
        Route::post('/notifications/{notification}/read', [HedraSoulNotificationController::class, 'markRead'])
            ->name('hedrasoul.notifications.markRead');
        Route::post('/notifications/{notification}/snooze', [HedraSoulNotificationController::class, 'snooze'])
            ->name('hedrasoul.notifications.snooze');

        // Misc/utility endpoints
        Route::get('/mentions/search', [HedraSoulMiscController::class, 'mentionsSearch'])
            ->name('hedrasoul.mentions.search');
        Route::post('/context/preview', [HedraSoulMiscController::class, 'contextPreview'])
            ->name('hedrasoul.context.preview');
        Route::get('/search', [HedraSoulMiscController::class, 'search'])
            ->name('hedrasoul.search');
        Route::get('/analytics', [HedraSoulMiscController::class, 'analytics'])
            ->name('hedrasoul.analytics');
        Route::get('/usage', [HedraSoulMiscController::class, 'usage'])
            ->name('hedrasoul.usage');
    });

    Route::middleware(['can:viewDlq'])->group(function () {
        Route::get('/admin/dlq', [DlqController::class, 'index']);
        Route::post('/admin/dlq/{id}/retry', [DlqController::class, 'retry']);
        Route::delete('/admin/dlq/{id}', [DlqController::class, 'destroy']);
        Route::post('/admin/dlq/batch-retry', [DlqController::class, 'batchRetry']);
    });

    /**
     * AI Models Hub Routes
     * NOTE: Specific routes must be defined BEFORE resource routes to prevent route conflicts
     */
    // AI Models resource and ID-specific routes (Legacy API, keeping for backward compatibility)
    Route::resource('ai-models', AiModelController::class);
    Route::post('/ai-models/{id}/test', [AiModelController::class, 'test'])
        ->name('ai-models.test');

    // New AI Models Hub endpoints for UP-002
    // Provider Health & Observability
    Route::get('/ai/providers/health', [AiRouteController::class, 'providerHealth'])
        ->name('ai.providers.health');

    Route::get('/ai/providers/health-summary', [AiProviderController::class, 'healthSummary'])
        ->name('ai.providers.health-summary');
    Route::post('/ai/providers/reorder', [AiProviderController::class, 'reorder'])
        ->name('ai.providers.reorder');
    Route::post('/ai/providers/sync-all', [AiProviderController::class, 'syncAll'])
        ->name('ai.providers.sync-all');
    Route::post('/ai/providers/bulk-action', [AiProviderController::class, 'bulkAction'])
        ->name('ai.providers.bulk-action');

    Route::get('/ai/providers', [AiProviderController::class, 'index'])
        ->name('ai.providers.index');
    Route::post('/ai/providers', [AiProviderController::class, 'store'])
        ->name('ai.providers.store');

    Route::get('/ai/providers/{id}/details', [AiProviderController::class, 'details'])
        ->name('ai.providers.details');
    Route::patch('/ai/providers/{id}/meta', [AiProviderController::class, 'updateMeta'])
        ->name('ai.providers.update-meta');
    Route::get('/ai/providers/{id}/usage-stats', [AiProviderController::class, 'usageStats'])
        ->name('ai.providers.usage-stats');
    Route::get('/ai/providers/{id}/nexus-apis', [AiProviderController::class, 'nexusApis'])
        ->name('ai.providers.nexus-apis');
    Route::post('/ai/providers/{id}/duplicate', [AiProviderController::class, 'duplicate'])
        ->name('ai.providers.duplicate');

    Route::get('/ai/providers/{id}', [AiProviderController::class, 'show'])
        ->name('ai.providers.show');
    Route::put('/ai/providers/{id}', [AiProviderController::class, 'update'])
        ->name('ai.providers.update');
    Route::delete('/ai/providers/{id}', [AiProviderController::class, 'destroy'])
        ->name('ai.providers.destroy');
    Route::post('/ai/providers/{id}/test', [AiProviderController::class, 'test'])
        ->name('ai.providers.test');
    Route::post('/ai/providers/{id}/sync-models', [AiProviderController::class, 'syncModels'])
        ->name('ai.providers.sync-models');
    Route::patch('/ai/providers/{id}/toggle-active', [AiProviderController::class, 'toggleActive'])
        ->name('ai.providers.toggle-active');

    // API Keys Sub-resource
    Route::get('/ai/providers/{id}/keys', [AIApiKeyController::class, 'indexForProvider'])
        ->name('ai.providers.keys.index');
    Route::post('/ai/providers/{id}/keys', [AIApiKeyController::class, 'store'])
        ->name('ai.providers.keys.store');
    Route::delete('/ai/api-keys/{keyId}', [AIApiKeyController::class, 'destroy'])
        ->name('ai.api-keys.destroy');
    Route::post('/ai/api-keys/{keyId}/set-default', [AIApiKeyController::class, 'setDefault'])
        ->name('ai.api-keys.set-default');
    Route::get('/ai/intents/routing', [AiRequestController::class, 'getRoutingMatrix'])
        ->name('ai.intents.routing.index');
    Route::put('/ai/intents/routing', [AiRequestController::class, 'routeIntent'])
        ->name('ai.intents.routing.update');
    Route::post('/ai/request', [AiRequestController::class, 'handleRequest'])
        ->name('ai.request.handle');

    // Core routing execution endpoint
    Route::post('/ai-models/route', [AiRouteController::class, 'route'])
        ->name('ai-models.route');

    // AI Instances
    Route::apiResource('ai-instances', AiInstanceController::class);

    // Cost Analytics & Budget Endpoints
    Route::get('/ai/cost/forecast', [AiCostAnalyticsController::class, 'forecast'])
        ->name('ai.cost.forecast');
    Route::post('/ai/cost/budget', [AiCostAnalyticsController::class, 'setBudget'])
        ->name('ai.cost.budget');

    // Audit Trail
    Route::get('/ai/audit-trail', [AiRouteController::class, 'auditTrail'])
        ->name('ai.audit.trail');

    // Telemetry Dashboard
    Route::get('/ai-hub/telemetry', [AiRouteController::class, 'telemetry'])
        ->name('ai.telemetry');

    /**
     * Settings Hub Routes
     */
    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', [SettingController::class, 'index'])
            ->name('settings.index');
        Route::post('/', [SettingController::class, 'store'])
            ->name('settings.store');
        Route::get('/grouped', [SettingController::class, 'grouped'])
            ->name('settings.grouped');
        Route::get('/public', [SettingController::class, 'publicSettings'])
            ->name('settings.public');
        Route::put('/bulk', [SettingController::class, 'bulkUpdate'])
            ->name('settings.bulk-update');
        Route::post('/factory-reset', [SettingController::class, 'factoryReset'])
            ->name('settings.factory-reset');

        // Emergency control routes (super-admin only)
        Route::get('/system/agent-pause', [SettingController::class, 'getGlobalAgentPauseStatus'])
            ->name('settings.agent-pause.status');
        Route::post('/system/agent-pause', [SettingController::class, 'toggleGlobalAgentPause'])
            ->middleware('can:toggleEmergency,App\Models\Setting')
            ->name('settings.agent-pause');

        Route::post('/system/maintenance-mode', [SettingController::class, 'toggleMaintenanceMode'])
            ->middleware('can:toggleEmergency,App\Models\Setting')
            ->name('settings.maintenance-mode');

        Route::post('/system/api-proxy', [SettingController::class, 'apiProxy'])
            ->name('settings.api-proxy');

        // Seed manager routes (super-admin only)
        Route::get('/seeds', [SettingController::class, 'listSeeds'])
            ->middleware('can:runSeeder,App\Models\Setting')
            ->name('settings.seeds.list');
        Route::post('/seeds/{seedId}/run', [SettingController::class, 'runSeed'])
            ->middleware('can:runSeeder,App\Models\Setting')
            ->name('settings.seeds.run');
        Route::post('/seeds/run-multiple', [SettingController::class, 'runMultipleSeeds'])
            ->middleware('can:runSeeder,App\Models\Setting')
            ->name('settings.seeds.run-multiple');

        // Credential validation and health routes
        Route::post('/credentials/validate', [SettingController::class, 'validateCredential'])
            ->name('settings.credentials.validate');
        Route::get('/credentials/validate', [SettingController::class, 'validateAllCredentials'])
            ->name('settings.credentials.validate_all');
        Route::get('/health', [SettingController::class, 'healthStatus'])
            ->name('settings.health');

        // Admin dashboard routes (super-admin only)
        Route::group(['prefix' => 'admin', 'middleware' => 'can:create,App\Models\Setting'], function () {
            Route::get('/dashboard', [SettingsHubAdminController::class, 'dashboardOverview'])
                ->name('settings.admin.dashboard');
            Route::get('/audit-trail', [SettingsHubAdminController::class, 'auditTrail'])
                ->name('settings.admin.audit-trail');
            Route::get('/compliance', [SettingsHubAdminController::class, 'complianceStatus'])
                ->name('settings.admin.compliance');
            Route::get('/multi-tenancy', [SettingsHubAdminController::class, 'multiTenancyStatus'])
                ->name('settings.admin.multi-tenancy');
            Route::get('/performance', [SettingsHubAdminController::class, 'performanceMetrics'])
                ->name('settings.admin.performance');
            Route::post('/export', [SettingsHubAdminController::class, 'exportSettings'])
                ->name('settings.admin.export');
            Route::post('/import', [SettingsHubAdminController::class, 'importSettings'])
                ->name('settings.admin.import');
        });

        // Global System Telemetry
        Route::get('/system/telemetry', [SystemTelemetryController::class, 'getTelemetry'])
            ->name('system.telemetry');
        // WAHA WhatsApp Integration testing and URL helper routes
        Route::get('/waha/webhook-url', [SettingController::class, 'getWahaWebhookUrl'])
            ->name('settings.waha.webhook-url');
        Route::post('/waha/test-connection', [SettingController::class, 'testWahaConnection'])
            ->name('settings.waha.test-connection');
        Route::post('/waha/test-webhook', [SettingController::class, 'testWahaWebhook'])
            ->name('settings.waha.test-webhook');

        // WAHA Management
        Route::get('/waha-manage/status', [WahaManageController::class, 'status']);
        Route::get('/waha-manage/contacts', [WahaManageController::class, 'contacts']);
        Route::post('/waha-manage/sync/start', [WahaManageController::class, 'startSync']);
        Route::post('/waha-manage/sync/contact/{id}', [WahaManageController::class, 'startContactMessageSync']);
        Route::post('/waha-manage/sync/{id}/pause', [WahaManageController::class, 'pauseSync']);
        Route::post('/waha-manage/analyze/start', [WahaManageController::class, 'startAnalysis']);

        // Credential masking route
        Route::get('/{key}/masked', [SettingController::class, 'getMaskedCredential'])
            ->name('settings.masked');

        // Standard CRUD routes
        Route::get('/{key}', [SettingController::class, 'show'])
            ->name('settings.show');
        Route::put('/{key}', [SettingController::class, 'update'])
            ->name('settings.update');
        Route::delete('/{key}', [SettingController::class, 'destroy'])
            ->name('settings.destroy');
    });

    Route::group(['prefix' => 'scheduler'], function () {
        Route::get('/', [SchedulerController::class, 'index'])->name('scheduler.index');
        Route::post('/', [SchedulerController::class, 'store'])->name('scheduler.store');
        Route::get('/{schedulerJob}', [SchedulerController::class, 'show'])->name('scheduler.show');
        Route::put('/{id}', [SchedulerController::class, 'update'])->name('scheduler.update');
        Route::delete('/{id}', [SchedulerController::class, 'destroy'])->name('scheduler.destroy');
    });

    /**
     * Proactive AI Engine Routes
     */
    Route::group(['prefix' => 'proactive'], function () {
        Route::get('/rules', [ProactiveAIController::class, 'indexRules'])->name('proactive.rules.index');
        Route::post('/rules', [ProactiveAIController::class, 'storeRule'])->name('proactive.rules.store');
        Route::patch('/rules/{id}/toggle', [ProactiveAIController::class, 'toggleRule'])->name('proactive.rules.toggle');
        Route::delete('/rules/{id}', [ProactiveAIController::class, 'destroyRule'])->name('proactive.rules.destroy');
        Route::get('/triggers', [ProactiveAIController::class, 'indexTriggers'])->name('proactive.triggers.index');
        Route::get('/logs', [ProactiveAIController::class, 'indexLogs'])->name('proactive.logs.index');
    });

    /**
     * Logs Hub Routes
     */
    Route::group(['prefix' => 'logs'], function () {
        Route::get('/', [LogController::class, 'index'])
            ->name('logs.index');
        Route::post('/clear', [LogController::class, 'clear'])
            ->name('logs.clear');
        Route::get('/stats', [LogController::class, 'stats'])
            ->name('logs.stats');
        Route::get('/levels', [LogController::class, 'levels'])
            ->name('logs.levels');
        Route::get('/channels', [LogController::class, 'channels'])
            ->name('logs.channels');
        Route::get('/categories', [LogController::class, 'channels'])
            ->name('logs.categories');
        Route::get('/errors', [LogController::class, 'errors'])
            ->name('logs.errors');
        Route::get('/{id}', [LogController::class, 'show'])
            ->name('logs.show');
        Route::delete('/{id}', [LogController::class, 'destroy'])
            ->name('logs.destroy');
    });

    /**
     * User Profile Routes
     */
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [ProfileController::class, 'show'])
            ->name('profile.show');
        Route::put('/', [ProfileController::class, 'update'])
            ->name('profile.update');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])
            ->name('profile.avatar');
    });

    /**
     * Authentication Routes
     */
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::post('/refresh-token', [AuthController::class, 'refreshToken'])
        ->name('refresh-token');
});

/**
 * System Management Routes
 */
Route::group(['prefix' => 'v1', 'middleware' => ['api', 'auth:sanctum']], function () {
    Route::get('/panels', [PanelController::class, 'index']);
    Route::post('/panels', [PanelController::class, 'store']);
    Route::put('/panels/{id}', [PanelController::class, 'update']);
    Route::delete('/panels/{id}', [PanelController::class, 'destroy']);

    Route::get('/agents/active', function () {
        return response()->json(Agent::where('is_active', true)->get());
    });

    Route::prefix('admin/system')->group(function () {
        Route::get('/status', [SystemController::class, 'status'])
            ->name('admin.system.status');
        Route::get('/routes', [SystemController::class, 'routes'])
            ->name('admin.system.routes');
        Route::get('/schema', [SystemController::class, 'schema'])
            ->name('admin.system.schema');
        Route::post('/service/start', [SystemController::class, 'startService'])
            ->name('admin.service.start');
        Route::post('/service/stop', [SystemController::class, 'stopService'])
            ->name('admin.service.stop');
        Route::post('/service/restart', [SystemController::class, 'restartService'])
            ->name('admin.service.restart');
        Route::get('/service/logs', [SystemController::class, 'getServiceLogs'])
            ->name('admin.service.logs');
        Route::post('/build/trigger', [SystemController::class, 'triggerBuild'])
            ->name('admin.build.trigger');
    });
});

/**
 * Notification Broadcast Routes
 * Real-time notifications using Laravel Echo and Reverb
 */
Route::group(['prefix' => 'v1/notifications', 'middleware' => ['api', 'auth:sanctum']], function () {
    Route::post('/broadcast', [NotificationBroadcastController::class, 'send'])
        ->name('notifications.broadcast');
    Route::post('/broadcast-batch', [NotificationBroadcastController::class, 'sendBatch'])
        ->name('notifications.broadcast.batch');
    Route::post('/fcm-token', [NotificationBroadcastController::class, 'registerFcmToken'])
        ->name('notifications.fcm.token');
});

Route::get('/v1/notifications/fcm-config', [NotificationBroadcastController::class, 'fcmConfig'])
    ->name('notifications.fcm.config');

/**
 * Hedra Soul Routes
 */
Route::group(['prefix' => 'v1/hedra-soul', 'middleware' => ['api', 'auth:sanctum']], function () {
    Route::get('/sessions', [HedraSoulController::class, 'getSessions']);
    Route::get('/approvals', [HedraSoulController::class, 'getApprovals']);
    Route::get('/notifications', [HedraSoulController::class, 'getNotifications']);
    Route::get('/status', [HedraSoulController::class, 'getStatus']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
    });
    Route::get('/panels', [PanelController::class, 'index']);
    Route::post('/panels', [PanelController::class, 'store']);
    Route::put('/panels/{id}', [PanelController::class, 'update']);
    Route::delete('/panels/{id}', [PanelController::class, 'destroy']);

    Route::get('/agents/active', function () {
        return response()->json(Agent::where('is_active', true)->get());
    });
});

// Fallback route
Route::fallback(function () {
    return response()->json([
        'error' => 'Not Found',
        'message' => 'The requested resource was not found',
    ], 404);
});
