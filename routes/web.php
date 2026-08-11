<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SwaggerController;
use App\Http\Controllers\WahaManageController;
use Illuminate\Support\Facades\Route;

// Swagger/OpenAPI Documentation Routes
Route::get('/swagger-ui', [SwaggerController::class, 'ui'])->name('swagger.ui');
Route::get('/openapi.json', [SwaggerController::class, 'spec'])->name('swagger.spec');
Route::get('/redoc', [SwaggerController::class, 'redoc'])->name('swagger.redoc');

// Dedicated Tasks Hub API Documentation Routes
Route::get('/tasks-hub-swagger', [SwaggerController::class, 'tasksHubUi'])->name('swagger.tasks-hub.ui');
Route::get('/tasks-hub-openapi.json', [SwaggerController::class, 'tasksHubSpec'])->name('swagger.tasks-hub.spec');
Route::get('/tasks-hub-openapi.js', [SwaggerController::class, 'tasksHubJs'])->name('swagger.tasks-hub.js');

// Dashboard routes
// Dashboard routes (Legacy - kept for backwards compatibility)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
Route::post('/dashboard/clear-cache', [DashboardController::class, 'clearCache'])->name('dashboard.clear-cache');
Route::post('/dashboard/restart-queue', [DashboardController::class, 'restartQueue'])->name('dashboard.restart-queue');
Route::post('/dashboard/refresh-metric', [DashboardController::class, 'refreshMetric'])->name('dashboard.refresh-metric');

// All other routes handled by Next.js frontend
// API routes are in routes/api.php

// --- Nexus Monolithic Hubs Routes ---
use App\Http\Controllers\CredentialsHub\CredentialsHubController;
use App\Http\Controllers\SystemTelemetryController;
use App\Http\Controllers\Web\AiHubController;
use App\Http\Controllers\Web\HubController;
use App\Http\Controllers\Web\TasksHubController;
use App\Http\Controllers\Web\WorkflowsHubController;

Route::prefix('hub')->group(function () {
    Route::get('/credentials', [CredentialsHubController::class, 'index'])->name('hub.credentials');
    Route::post('/credentials', [CredentialsHubController::class, 'store'])->name('hub.credentials.store');
    Route::post('/credentials/{id}/field', [CredentialsHubController::class, 'updateField'])->name('hub.credentials.update-field');
    Route::delete('/credentials/{id}', [CredentialsHubController::class, 'destroy'])->name('hub.credentials.destroy');
    Route::post('/credentials/{id}/test', [CredentialsHubController::class, 'testSingle'])->name('hub.credentials.test-single');
    Route::post('/credentials/test-all', [CredentialsHubController::class, 'testAll'])->name('hub.credentials.test-all');
    Route::post('/credentials/agent/chat', [CredentialsHubController::class, 'agentChat'])->name('hub.credentials.agent-chat');

    Route::get('/dashboard', [HubController::class, 'dashboard'])->name('hub.dashboard');
    Route::get('/dev', [HubController::class, 'dev'])->name('hub.dev');
    Route::get('/dev/status', [HubController::class, 'devStatus'])->name('hub.dev.status');
    Route::post('/dev/command', [HubController::class, 'devCommand'])->name('hub.dev.command');

    // Contacts
    Route::get('/contacts', [HubController::class, 'contacts'])->name('hub.contacts');
    Route::post('/contacts', [HubController::class, 'storeContact'])->name('hub.contacts.store');
    Route::get('/contacts/{id}/studio', [HubController::class, 'contactStudio'])->name('hub.contacts.studio');
    Route::get('/contacts/{id}/studio-eg', [HubController::class, 'contactStudioEg'])->name('hub.contacts.studio.eg');
    Route::get('/contacts/{id}/war-room', [HubController::class, 'contactWarRoom'])->name('hub.contacts.war-room');
    Route::get('/contacts/{id}/archives', [HubController::class, 'contactArchives'])->name('hub.contacts.archives');
    Route::get('/contacts/{id}', [HubController::class, 'contactProfile'])->name('hub.contacts.profile');

    // Agents
    Route::get('/agents', [HubController::class, 'agents'])->name('hub.agents');
    Route::post('/agents', [HubController::class, 'storeAgent'])->name('hub.agents.store');
    Route::post('/agents/{id}/toggle', [HubController::class, 'toggleAgent'])->name('hub.agents.toggle');

    // Workflows Hub
    Route::prefix('workflows')->group(function () {
        Route::get('/', [WorkflowsHubController::class, 'index'])->name('hub.workflows.index');
        Route::get('/stats', [WorkflowsHubController::class, 'dashboardStats'])->name('hub.workflows.stats');
        Route::get('/executions-data', [WorkflowsHubController::class, 'executionsData'])->name('hub.workflows.executions-data');
        Route::get('/schedules-data', [WorkflowsHubController::class, 'schedulesData'])->name('hub.workflows.schedules-data');
        Route::post('/{workflow}/save', [WorkflowsHubController::class, 'saveWorkflow'])->name('hub.workflows.save');
        Route::post('/schedules/{id}/toggle', [WorkflowsHubController::class, 'toggleSchedule'])->name('hub.workflows.schedules.toggle');
        Route::post('/{workflow}/execute', [WorkflowsHubController::class, 'execute'])->name('hub.workflows.execute');
        Route::get('/executions/{execution}', [WorkflowsHubController::class, 'showExecution'])->name('hub.workflows.execution');
    });

    // Memory
    Route::get('/memory', [HubController::class, 'memory'])->name('hub.memory');
    Route::post('/memory', [HubController::class, 'storeMemory'])->name('hub.memory.store');

    // Logs, Models, Settings
    Route::get('/logs', [HubController::class, 'logs'])->name('hub.logs');
    Route::get('/models', [HubController::class, 'models'])->name('hub.models');
    Route::get('/providers/{id}', [HubController::class, 'providerDetails'])->name('hub.providers.show');

    // AiHubController routes (API Keys, Providers, Models, Routing)
    Route::post('/models/routing', [AiHubController::class, 'storeRoutingRule'])->name('hub.models.routing.store');
    Route::post('/models/routing/{id}/toggle', [AiHubController::class, 'toggleRoutingRule'])->name('hub.models.routing.toggle');
    Route::delete('/models/routing/{id}', [AiHubController::class, 'deleteRoutingRule'])->name('hub.models.routing.delete');

    Route::post('/models/providers/ping', [AiHubController::class, 'pingProvider'])->name('hub.models.providers.ping');
    Route::post('/models/providers/{id}/sync', [AiHubController::class, 'syncModels'])->name('hub.models.providers.sync');
    Route::post('/models/providers', [AiHubController::class, 'storeProvider'])->name('hub.models.providers.store');
    Route::post('/models/providers/toggle', [AiHubController::class, 'toggleProvider'])->name('hub.models.providers.toggle');

    Route::get('/models/api-keys/stats', [AiHubController::class, 'getApiKeysStats'])->name('hub.models.api-keys.stats');
    Route::get('/models/api-keys/{id}/analytics', [AiHubController::class, 'getKeyAnalytics'])->name('hub.models.api-keys.analytics');
    Route::post('/models/api-keys', [AiHubController::class, 'storeApiKey'])->name('hub.models.api-keys.store');
    Route::post('/models/api-keys/{id}/ping', [AiHubController::class, 'pingApiKey'])->name('hub.models.api-keys.ping');
    Route::post('/models/api-keys/{id}/set-default', [AiHubController::class, 'setDefaultApiKey'])->name('hub.models.api-keys.set-default');
    Route::delete('/models/api-keys/{id}', [AiHubController::class, 'revokeApiKey'])->name('hub.models.api-keys.revoke');

    Route::post('/models/ab-experiments', [AiHubController::class, 'storeAbExperiment'])->name('hub.models.ab-experiments.store');
    Route::post('/models/ab-experiments/{id}/weights', [AiHubController::class, 'updateAbExperimentWeights'])->name('hub.models.ab-experiments.weights');

    Route::post('/models/budget', [AiHubController::class, 'updateBudget'])->name('hub.models.budget.update');
    Route::get('/models/cost-charts', [AiHubController::class, 'costCharts'])->name('hub.models.cost-charts');
    Route::get('/models/telemetry', [AiHubController::class, 'telemetry'])->name('hub.models.telemetry');

    // Playground routes
    Route::post('/models/playground/chat', [AiHubController::class, 'simulateChat'])->name('hub.models.playground.chat');
    Route::post('/models/playground/battle', [AiHubController::class, 'simulateBattle'])->name('hub.models.playground.battle');
    Route::post('/models/playground/dispatch-job', [AiHubController::class, 'dispatchJob'])->name('hub.models.playground.dispatch-job');

    // Wildcard routes must be at the end
    Route::post('/models/{id}/toggle', [AiHubController::class, 'toggleModel'])->name('hub.models.toggle');

    Route::get('/settings', [HubController::class, 'settings'])->name('hub.settings');
    Route::post('/settings', [HubController::class, 'updateSettings'])->name('hub.settings.update');
    Route::post('/settings/clear-cache', [HubController::class, 'clearSettingsCache'])->name('hub.settings.clear-cache');
    // New Hubs
    Route::get('/people-connect', [HubController::class, 'peopleConnect'])->name('hub.people-connect');
    Route::get('/people-connect/agent-settings', [HubController::class, 'peopleConnectAgentSettings'])->name('hub.people-connect.agent-settings');
    Route::post('/people-connect/agent-settings/save', [HubController::class, 'savePeopleConnectAgentSettings'])->name('hub.people-connect.agent-settings.save');
    Route::post('/people-connect/agent-settings/key-rotation', [HubController::class, 'manageKeyRotation'])->name('hub.people-connect.agent-settings.key-rotation');
    Route::get('/hedra-soul', [HubController::class, 'hedraSoul'])->name('hub.hedra-soul');
    Route::get('/hedra-soul/hermes/profiles', [HubController::class, 'fetchHermesProfiles'])->name('hub.hedra-soul.hermes.profiles');
    Route::get('/hedra-soul/hermes/health-details', [HubController::class, 'getHermesHealthDetails'])->name('hub.hedra-soul.hermes.health-details');
    Route::get('/hedra-soul/hermes/sessions', [HubController::class, 'fetchHermesSessions'])->name('hub.hedra-soul.hermes.sessions');
    Route::get('/hedra-soul/hermes/sessions/{sessionId}/messages', [HubController::class, 'getHermesSessionMessages'])->name('hub.hedra-soul.hermes.session-messages');
    Route::post('/hedra-soul/hermes/select-session', [HubController::class, 'selectHermesSession'])->name('hub.hedra-soul.hermes.select-session');
    Route::match(['get', 'post'], '/hedra-soul/hermes/test', [HubController::class, 'testHermesConnection'])->name('hub.hedra-soul.hermes.test-connection');
    Route::post('/hedra-soul/hermes/save', [HubController::class, 'saveHermesSettings'])->name('hub.hedra-soul.hermes.save');
    Route::post('/hedra-soul/hermes/send-message', [HubController::class, 'sendHermesMessage'])->name('hub.hedra-soul.hermes.send-message');
    Route::get('/proactive-ai', [HubController::class, 'proactiveAi'])->name('hub.proactive-ai');
    Route::get('/tasks', [TasksHubController::class, 'index'])->name('hub.tasks.index');
    Route::get('/tasks/{task}', [TasksHubController::class, 'show'])->name('hub.tasks.show');
    Route::get('/notifications', [HubController::class, 'notifications'])->name('hub.notifications');
    Route::get('/notifications/data', [HubController::class, 'notificationsData'])->name('hub.notifications.data');
    Route::post('/notifications/clear-all', [HubController::class, 'clearNotifications'])->name('hub.notifications.clear-all');
    Route::post('/notifications/generate-test', [HubController::class, 'generateTestNotification'])->name('hub.notifications.generate-test');
    Route::post('/notifications/{id}/read', [HubController::class, 'markNotificationRead'])->name('hub.notifications.read');
    Route::post('/approvals/{id}/respond', [HubController::class, 'respondApproval'])->name('hub.approvals.respond');
    Route::get('/scheduler', [HubController::class, 'scheduler'])->name('hub.scheduler');
    Route::get('/apis', [HubController::class, 'apis'])->name('hub.apis');
    Route::get('/admin', [HubController::class, 'admin'])->name('hub.admin');
    Route::get('/waha', [HubController::class, 'waha'])->name('hub.waha');

    // WAHA Realtime Actions
    Route::get('/waha/status', [WahaManageController::class, 'status'])->name('hub.waha.status');
    Route::post('/waha/sync', [HubController::class, 'triggerWahaSync'])->name('hub.waha.sync');
    Route::post('/waha/sync/{id}/pause', [WahaManageController::class, 'pauseSync'])->name('hub.waha.pause');
    Route::post('/people-connect/message', [HubController::class, 'sendContactMessage'])->name('hub.people-connect.message');
    Route::post('/hedra-soul/message', [HubController::class, 'sendHedraMessage'])->name('hub.hedra-soul.message');

    // Contact Favorites
    Route::post('/contacts/{id}/toggle-favorite', [HubController::class, 'toggleFavorite'])->name('hub.contacts.toggle-favorite');

    // Web Logout
    Route::post('/logout', [HubController::class, 'logoutWeb'])->name('hub.logout');

    // Agent Actions
    Route::post('/agents/{id}/restart', [HubController::class, 'restartAgent'])->name('hub.agents.restart');

    // Dashboard health and activity feed
    Route::get('/dashboard/health', [HubController::class, 'dashboardHealth'])->name('hub.dashboard.health');
    Route::get('/dashboard/activity-feed', [HubController::class, 'dashboardActivityFeed'])->name('hub.dashboard.activity-feed');
    Route::get('/system/telemetry', [SystemTelemetryController::class, 'getTelemetry'])->name('hub.system.telemetry');
});

Route::get('/test-ui', function () {
    return view('hubs.settings', ['settings' => collect([])]);
});

use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\SettingsReferenceController;
use App\Http\Controllers\SystemExplorerController;

Route::get('/hub/settings-reference', [SettingsReferenceController::class, 'index'])->name('hub.settings.reference');

// --- System Explorer Web Routes (Public Browser Access) ---
Route::prefix('system')->group(function () {
    Route::get('/', [SystemExplorerController::class, 'index'])->name('system.explorer.index');
    Route::get('/routes', [SystemExplorerController::class, 'routesView'])->name('system.explorer.routes');
    Route::get('/schema', [SystemExplorerController::class, 'schemaView'])->name('system.explorer.schema');
    Route::get('/codebase', [SystemExplorerController::class, 'codebaseView'])->name('system.explorer.codebase');
    Route::get('/docs', [SystemExplorerController::class, 'docsView'])->name('system.explorer.docs');
    Route::get('/views', [SystemExplorerController::class, 'viewsExplorer'])->name('system.explorer.views');
    Route::get('/readme', [SystemController::class, 'readme'])->name('system.explorer.readme');
    Route::get('/queue-details', [SystemController::class, 'queueDetails'])->name('system.explorer.queue-details');
    Route::post('/optimize-and-clear', [SystemController::class, 'optimizeAndClear'])->name('system.explorer.optimize-and-clear');
});

Route::get('/readme', [SystemController::class, 'readme']);
Route::post('/system/optimize-and-clear', [SystemController::class, 'optimizeAndClear']);
