<?php

namespace App\Http\Controllers;

use App\Models\AIApiKey;
use App\Models\AIModel;
use App\Models\AIProvider;
use App\Services\AiModelsHub\DynamicProviderRegistry;
use App\Services\AiModelsHub\DynamicRestProvider;
use App\Services\AiModelsHub\EncryptedApiKeyStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AiProviderController extends Controller
{
    protected $providerRegistry;

    protected $keyStorage;

    public function __construct(DynamicProviderRegistry $providerRegistry, EncryptedApiKeyStorage $keyStorage)
    {
        $this->providerRegistry = $providerRegistry;
        $this->keyStorage = $keyStorage;
    }

    /**
     * List all AI providers
     */
    public function index()
    {
        try {
            $providers = AIProvider::with('models')->get();

            return response()->json([
                'success' => true,
                'data' => $providers,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching AI providers: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch AI providers',
            ], 500);
        }
    }

    /**
     * Store a new AI provider
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'base_url' => 'required|url',
            'models_fetch_endpoint' => 'nullable|string|max:255',
            'generate_endpoint' => 'nullable|string|max:255',
            'test_endpoint' => 'nullable|string|max:255',
            'auth_header_format' => 'nullable|string|max:255',
            'payload_format' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'api_key' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $provider = $this->providerRegistry->registerProvider([
                'name' => $request->name,
                'base_url' => $request->base_url,
                'models_fetch_endpoint' => $request->models_fetch_endpoint,
                'generate_endpoint' => $request->generate_endpoint,
                'test_endpoint' => $request->test_endpoint,
                'auth_header_format' => $request->auth_header_format,
                'payload_format' => $request->payload_format,
                'is_active' => $request->is_active ?? true,
            ]);

            // Save the API key if provided
            if ($request->filled('api_key')) {
                $this->keyStorage->storeKey($provider->id, $request->api_key, "API Key for {$provider->name}");
            }

            // Sync models first time from the provider API (independent of api_key presence)
            if ($request->models_fetch_endpoint) {
                try {
                    $restProvider = new DynamicRestProvider($provider->id, $this->keyStorage);
                    $models = $restProvider->getAvailableModels();
                    if (! empty($models)) {
                        foreach ($models as $modelData) {
                            // Use updateOrCreate to prevent duplicate key errors on retry
                            AIModel::updateOrCreate(
                                [
                                    'name' => $modelData['id'] ?? $modelData['name'],
                                    'provider_id' => $provider->id,
                                ],
                                [
                                    'id' => (string) Str::uuid(),
                                    'last_synced_at' => now(),
                                ]
                            );
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Initial model sync failed for provider '.$provider->id.': '.$e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'data' => $provider->load('models'),
                'message' => 'AI provider created successfully',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating AI provider: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create AI provider',
            ], 500);
        }
    }

    /**
     * Display the specified AI provider
     */
    public function show($id)
    {
        try {
            $provider = AIProvider::with('models')->find($id);

            if (! $provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI provider not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $provider,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching AI provider: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch AI provider',
            ], 500);
        }
    }

    /**
     * Update the specified AI provider
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'base_url' => 'required|url',
            'models_fetch_endpoint' => 'nullable|string|max:255',
            'generate_endpoint' => 'nullable|string|max:255',
            'test_endpoint' => 'nullable|string|max:255',
            'auth_header_format' => 'nullable|string|max:255',
            'payload_format' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'api_key' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $provider = AIProvider::find($id);

            if (! $provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI provider not found',
                ], 404);
            }

            $provider->update([
                'name' => $request->name,
                'base_url' => $request->base_url,
                'models_fetch_endpoint' => $request->models_fetch_endpoint,
                'generate_endpoint' => $request->generate_endpoint,
                'test_endpoint' => $request->test_endpoint,
                'auth_header_format' => $request->auth_header_format,
                'payload_format' => $request->payload_format,
                'is_active' => $request->is_active ?? $provider->is_active,
            ]);

            if ($request->filled('api_key')) {
                $this->keyStorage->updateKey($provider->id, $request->api_key, "API Key for {$provider->name}");
            }

            return response()->json([
                'success' => true,
                'data' => $provider->load('models'),
                'message' => 'AI provider updated successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating AI provider: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update AI provider: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified AI provider
     */
    public function destroy($id)
    {
        try {
            $provider = AIProvider::find($id);

            if (! $provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI provider not found',
                ], 404);
            }

            // Deactivate and delete associated keys
            $this->keyStorage->deactivateKey($id);
            AIApiKey::where('provider_id', $id)->delete();

            // Delete associated models
            AIModel::where('provider_id', $id)->delete();

            $provider->delete();

            return response()->json([
                'success' => true,
                'message' => 'AI provider deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting AI provider: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete AI provider: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync models for a specific provider
     */
    public function syncModels(Request $request, $id)
    {
        try {
            // Use AIProvider::find() directly to avoid the is_active gate in the registry
            // and prevent dirty-attribute issues from resolved_api_key
            $provider = AIProvider::find($id);

            if (! $provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI provider not found',
                ], 404);
            }

            $restProvider = new DynamicRestProvider($id, $this->keyStorage);
            $models = $restProvider->getAvailableModels();

            if (empty($models)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch models from provider API, or provider returned no models',
                ], 400);
            }

            foreach ($models as $modelData) {
                AIModel::updateOrCreate(
                    [
                        'name' => $modelData['id'] ?? $modelData['name'],
                        'provider_id' => $id,
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'last_synced_at' => now(),
                    ]
                );
            }

            // Use raw query update to avoid Eloquent dirty attribute pollution
            AIProvider::where('id', $id)->update(['last_synced_at' => now()]);

            // Reload from DB to return current full list
            $syncedModels = AIModel::where('provider_id', $id)->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $syncedModels,
                'synced_count' => count($models), // count of models fetched from API
                'total_count' => $syncedModels->count(), // total in DB
                'message' => 'Models synchronized successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error syncing models for AI provider: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync models for AI provider: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test connection to a specific provider
     */
    public function test(Request $request, $id)
    {
        try {
            // Use direct find to bypass the is_active gate and avoid dirty-attribute issues
            $provider = AIProvider::find($id);

            if (! $provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI provider not found',
                ], 404);
            }

            $restProvider = new DynamicRestProvider($id, $this->keyStorage);
            $health = $restProvider->getHealthStatus();

            $isHealthy = $health['status'] === 'healthy';

            $errorDetail = isset($health['provider_error'])
                ? ' — '.substr($health['provider_error'], 0, 120)
                : '';

            $message = match ($health['status']) {
                'healthy' => 'Connection to provider successful',
                'no_key' => 'No API key configured — please add an API key to test this provider',
                'unhealthy' => 'Provider returned HTTP '.($health['http_status'] ?? '?').$errorDetail,
                'offline' => 'Connection failed: '.($health['error'] ?? 'unreachable'),
                default => 'Unable to determine provider status',
            };

            return response()->json([
                'success' => $isHealthy,
                'message' => $message,
                'status' => $health['status'],
                'data' => $health,
                'timestamp' => now()->toIso8601String(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error testing provider connection: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to test provider connection: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle active/inactive status of a provider (PATCH /ai/providers/{id}/toggle-active)
     */
    public function toggleActive(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $provider = AIProvider::find($id);

            if (! $provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI provider not found',
                ], 404);
            }

            $provider->update(['is_active' => $request->boolean('is_active')]);

            return response()->json([
                'success' => true,
                'data' => $provider->load('models'),
                'message' => 'Provider status updated successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error toggling AI provider status: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update provider status: '.$e->getMessage(),
            ], 500);
        }
    }

    public function details($id)
    {
        $provider = AIProvider::with([
            'models',
            'apiKeys' => fn($q) => $q->select('id', 'provider_id', 'name', 'status', 'is_active', 'is_default', 'last_used_at', 'created_at', \Illuminate\Support\Facades\DB::raw("CONCAT('sk-...****', SUBSTRING(key_hash, -4)) as masked_key"))
        ])->find($id);

        if (!$provider) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        // Latency sparkline (last 24 points)
        $sparkline = \Illuminate\Support\Facades\DB::table('provider_health_metrics')->where('provider_id', $id)
            ->orderBy('created_at', 'desc')
            ->take(24)
            ->pluck('latency_ms')
            ->reverse()
            ->values();

        // Uptime timeline (last 90 days simplified - pseudo data if sparse)
        // Here we just fetch the last 90 ping statuses
        $uptime = \Illuminate\Support\Facades\DB::table('provider_health_metrics')->where('provider_id', $id)
            ->orderBy('created_at', 'desc')
            ->take(90)
            ->pluck('status')
            ->reverse()
            ->values();

        // Last 10 pings
        $lastPings = \Illuminate\Support\Facades\DB::table('provider_health_metrics')->where('provider_id', $id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get(['created_at', 'status', 'latency_ms']); // 'http_status' is not in migration, using status/latency

        // Usage stats (simple month aggregate)
        $usage = \Illuminate\Support\Facades\DB::table('usage_logs')
            ->selectRaw('SUM(total_cost) as month_cost, COUNT(*) as month_requests, SUM(input_tokens + output_tokens) as month_tokens')
            ->where('provider_id', $id)
            ->where('timestamp', '>=', now()->startOfMonth())
            ->first();

        $provider->recent_latency = $sparkline;
        $provider->uptime = $uptime;
        $provider->last_pings = $lastPings;
        $provider->usage = $usage;
        $provider->nexus_apis = $this->getNexusApisList();

        return response()->json([
            'success' => true,
            'data' => $provider
        ]);
    }

    public function updateMeta(Request $request, $id)
    {
        $provider = AIProvider::find($id);
        if (!$provider) return response()->json(['success' => false], 404);

        $provider->update($request->only([
            'notes', 'tags', 'sort_order', 'is_favorite', 'monthly_budget_cap',
            'auto_sync_interval', 'circuit_breaker_threshold', 'request_timeout_ms', 'max_retries'
        ]));

        return response()->json(['success' => true, 'data' => $provider]);
    }

    public function reorder(Request $request)
    {
        $orders = $request->input('orders', []);
        foreach ($orders as $order) {
            if (isset($order['id']) && isset($order['sort_order'])) {
                AIProvider::where('id', $order['id'])->update(['sort_order' => $order['sort_order']]);
            }
        }
        return response()->json(['success' => true]);
    }

    public function syncAll()
    {
        $providers = AIProvider::where('is_active', true)->get();
        foreach ($providers as $provider) {
            dispatch(new \App\Jobs\SyncProviderModelsJob($provider->id));
        }
        return response()->json(['success' => true, 'message' => 'Sync jobs dispatched for ' . $providers->count() . ' providers.']);
    }

    public function healthSummary()
    {
        $providers = AIProvider::withCount('apiKeys')->get();
        
        $active = $providers->where('is_active', true)->count();
        $noKey = $providers->filter(fn($p) => $p->api_keys_count === 0)->count();
        
        $lastPings = \Illuminate\Support\Facades\DB::table('provider_health_metrics')
            ->select('provider_id', 'status')
            ->whereIn('id', function($q) {
                $q->selectRaw('MAX(id)')->from('provider_health_metrics')->groupBy('provider_id');
            })->get()->keyBy('provider_id');

        $unreachable = 0;
        $degraded = 0;
        foreach ($providers as $p) {
            if (!$p->is_active) continue;
            $status = $lastPings[$p->id]->status ?? 'unknown';
            if ($status === 'offline') $unreachable++;
            if ($status === 'degraded') $degraded++;
        }

        $lastSync = AIProvider::max('last_synced_at');

        return response()->json([
            'success' => true,
            'data' => [
                'active' => $active,
                'total' => $providers->count(),
                'no_key' => $noKey,
                'unreachable' => $unreachable,
                'degraded' => $degraded,
                'last_sync_at' => $lastSync,
            ]
        ]);
    }

    public function usageStats($id)
    {
        $today = \Illuminate\Support\Facades\DB::table('usage_logs')
            ->selectRaw('SUM(total_cost) as cost, COUNT(*) as requests, SUM(input_tokens + output_tokens) as tokens')
            ->where('provider_id', $id)->where('timestamp', '>=', now()->startOfDay())->first();

        $month = \Illuminate\Support\Facades\DB::table('usage_logs')
            ->selectRaw('SUM(total_cost) as cost, COUNT(*) as requests, SUM(input_tokens + output_tokens) as tokens')
            ->where('provider_id', $id)->where('timestamp', '>=', now()->startOfMonth())->first();

        $dailyChart = \Illuminate\Support\Facades\DB::table('usage_logs')
            ->selectRaw('DATE(timestamp) as date, SUM(total_cost) as cost, SUM(input_tokens) as input_tokens, SUM(output_tokens) as output_tokens')
            ->where('provider_id', $id)->where('timestamp', '>=', now()->subDays(30))
            ->groupBy('date')->orderBy('date')->get();

        return response()->json(['success' => true, 'data' => ['today' => $today, 'month' => $month, 'daily_chart' => $dailyChart]]);
    }

    private function getNexusApisList()
    {
        $routes = [];
        foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
            if (str_contains($route->uri(), 'api/v1/ai/') && !str_contains($route->uri(), 'providers')) {
                $routes[] = [
                    'method' => $route->methods()[0],
                    'uri' => '/' . $route->uri(),
                    'name' => $route->getName(),
                    'description' => 'Nexus AI API Route',
                    'example_payload' => '{}',
                ];
            }
        }
        return $routes;
    }

    public function nexusApis($id)
    {
        return response()->json(['success' => true, 'data' => $this->getNexusApisList()]);
    }

    public function duplicate($id)
    {
        $provider = AIProvider::find($id);
        if (!$provider) return response()->json(['success' => false], 404);

        $newProvider = $provider->replicate();
        $newProvider->id = (string) Str::uuid();
        $newProvider->name = '(Copy) ' . $provider->name;
        $newProvider->is_active = false;
        $newProvider->last_synced_at = null;
        $newProvider->save();

        return response()->json(['success' => true, 'data' => $newProvider]);
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (empty($ids)) return response()->json(['success' => false]);

        switch ($action) {
            case 'enable':
                AIProvider::whereIn('id', $ids)->update(['is_active' => true]);
                break;
            case 'disable':
                AIProvider::whereIn('id', $ids)->update(['is_active' => false]);
                break;
            case 'delete':
                foreach ($ids as $id) {
                    $this->keyStorage->deactivateKey($id);
                    AIApiKey::where('provider_id', $id)->delete();
                    AIModel::where('provider_id', $id)->delete();
                }
                AIProvider::whereIn('id', $ids)->delete();
                break;
            case 'sync':
                foreach ($ids as $id) {
                    dispatch(new \App\Jobs\SyncProviderModelsJob($id));
                }
                break;
        }

        return response()->json(['success' => true]);
    }
}
