<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiHub\DispatchAiJobRequest;
use App\Http\Requests\AiHub\PlaygroundChatRequest;
use App\Http\Requests\RevokeApiKeyRequest;
use App\Http\Requests\StoreApiKeyRequest;
use App\Http\Requests\StoreProviderRequest;
use App\Http\Requests\StoreRoutingRuleRequest;
use App\Http\Requests\ToggleModelRequest;
use App\Http\Requests\ToggleProviderRequest;
use App\Http\Requests\ToggleRoutingRuleRequest;
use App\Http\Requests\UpdateAiBudgetRequest;
use App\Jobs\AiHub\SimulateAiJob;
use App\Models\AiAbExperiment;
use App\Models\AIApiKey;
use App\Models\AIModel;
use App\Services\AiHubService;
use App\Services\AiModelsHub\EncryptedApiKeyStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiHubController extends Controller
{
    public function __construct(
        protected AiHubService $aiHubService
    ) {}

    public function toggleProvider(ToggleProviderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->aiHubService->toggleProvider($validated['provider'], (bool) $validated['is_active']);

        return response()->json([
            'success' => true,
            'message' => 'Provider toggled successfully.',
            'data' => $result,
        ]);
    }

    public function storeApiKey(StoreApiKeyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $providerId = $validated['provider_id'] ?? $validated['provider'] ?? null;

        if (! $providerId) {
            return response()->json([
                'success' => false,
                'message' => 'Provider ID is required.',
            ], 422);
        }

        $name = $validated['name'] ?? 'Default Key';
        $result = $this->aiHubService->storeApiKey($providerId, $validated['api_key'], $name);

        if (! empty($validated['is_default']) && isset($result['id'])) {
            AIApiKey::where('provider_id', $providerId)->update(['is_default' => false]);
            AIApiKey::where('id', $result['id'])->update(['is_default' => true]);
        }

        if (! empty($validated['expires_at']) && isset($result['id'])) {
            AIApiKey::where('id', $result['id'])->update(['expires_at' => $validated['expires_at']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'API key stored and encrypted successfully.',
            'data' => $result,
        ]);
    }

    public function revokeApiKey(RevokeApiKeyRequest $request, string $id): JsonResponse
    {
        $this->aiHubService->revokeApiKey($id);

        return response()->json([
            'success' => true,
            'message' => 'API key revoked successfully.',
        ]);
    }

    public function getApiKeysStats(): JsonResponse
    {
        $stats = $this->aiHubService->getApiKeysStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function storeProvider(StoreProviderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->aiHubService->storeProvider($validated);

        return response()->json([
            'success' => true,
            'message' => 'Provider added successfully.',
            'data' => $result,
        ], 201);
    }

    public function toggleModel(ToggleModelRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();

        $this->aiHubService->toggleModel($id, (bool) $validated['is_active']);

        return response()->json([
            'success' => true,
        ]);
    }

    public function storeRoutingRule(StoreRoutingRuleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->aiHubService->storeRoutingRule($validated);

        return response()->json([
            'success' => true,
            'message' => 'Routing rule saved successfully.',
            'data' => $result,
        ]);
    }

    public function toggleRoutingRule(ToggleRoutingRuleRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->aiHubService->toggleRoutingRule($id, (bool) $validated['is_active']);

        return response()->json([
            'success' => true,
            'message' => 'Routing rule toggled successfully.',
            'data' => $result,
        ]);
    }

    public function deleteRoutingRule(string $id): JsonResponse
    {
        $this->aiHubService->deleteRoutingRule($id);

        return response()->json([
            'success' => true,
            'message' => 'Routing rule deleted successfully.',
        ]);
    }

    public function updateBudget(UpdateAiBudgetRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->aiHubService->updateBudget($validated['type'], (float) $validated['limit']);

        return response()->json([
            'success' => true,
            'message' => 'Budget updated successfully.',
            'data' => $result,
        ]);
    }

    public function costCharts(): JsonResponse
    {
        $data = $this->aiHubService->getCostCharts();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function telemetry(): JsonResponse
    {
        $data = $this->aiHubService->getTelemetry();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function simulateChat(PlaygroundChatRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->aiHubService->simulateChat(
            $validated['provider_id'],
            $validated['model_id'],
            $validated['message']
        );

        $responseText = is_array($result) ? ($result['response'] ?? '') : $result;

        return response()->json([
            'success' => true,
            'message' => 'Chat prompt executed successfully.',
            'data' => is_array($result) ? $result : [
                'response' => $responseText,
            ],
        ]);
    }

    public function dispatchJob(DispatchAiJobRequest $request): JsonResponse
    {
        $validated = $request->validated();

        SimulateAiJob::dispatch(
            $validated['provider_id'],
            $validated['model_id'],
            $validated['message']
        );

        return response()->json([
            'success' => true,
            'message' => 'AI job dispatched to Horizon successfully.',
        ]);
    }

    public function pingProvider(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'base_url' => ['required', 'url', function ($attribute, $value, $fail) {
                $host = parse_url($value, PHP_URL_HOST);
                if (! $host) {
                    return $fail("The $attribute is invalid.");
                }
                if (in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'])) {
                    return $fail("The $attribute cannot be a local address.");
                }
                $ip = gethostbyname($host);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                    return $fail("The $attribute resolves to a private IP or is unresolvable.");
                }
            }],
            'api_key' => ['nullable', 'string'],
            'auth_header_format' => ['nullable', 'string'],
            'models_fetch_endpoint' => ['nullable', 'string'],
        ]);

        $result = $this->aiHubService->pingProvider($validated);

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Ping executed.',
            'data' => $result,
        ]);
    }

    public function pingApiKey(string $id): JsonResponse
    {
        $apiKey = AIApiKey::with('provider')->find($id);
        if (! $apiKey) {
            return response()->json(['success' => false, 'message' => 'API Key not found.'], 404);
        }

        $provider = $apiKey->provider;
        if (! $provider) {
            return response()->json(['success' => false, 'message' => 'Associated Provider not found.'], 404);
        }

        $decryptedKey = app(EncryptedApiKeyStorage::class)->getDecryptedKey($apiKey->id);
        if (! $decryptedKey) {
            return response()->json(['success' => false, 'message' => 'Unable to decrypt API key.'], 400);
        }

        $pingData = [
            'base_url' => $provider->base_url,
            'api_key' => $decryptedKey,
            'auth_header_format' => $provider->auth_header_format,
            'models_fetch_endpoint' => $provider->models_fetch_endpoint,
        ];

        $result = $this->aiHubService->pingProvider($pingData);

        if ($result['success'] ?? false) {
            $apiKey->update(['error_count' => 0, 'cooldown_until' => null, 'last_used_at' => now()]);
        } else {
            $apiKey->increment('error_count');
        }

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => 'Key ping completed.',
            'data' => $result,
        ]);
    }

    public function setDefaultApiKey(string $id): JsonResponse
    {
        $key = AIApiKey::find($id);
        if (! $key) {
            return response()->json(['success' => false, 'message' => 'API Key not found.'], 404);
        }

        AIApiKey::where('provider_id', $key->provider_id)->update(['is_default' => false]);
        $key->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Primary default API key updated successfully.',
        ]);
    }

    public function getKeyAnalytics(string $id): JsonResponse
    {
        $key = AIApiKey::with('provider')->find($id);
        if (! $key) {
            return response()->json(['success' => false, 'message' => 'API Key not found.'], 404);
        }

        $logs = DB::table('usage_logs')
            ->where('api_key_id', $id)
            ->where('timestamp', '>=', now()->subDays(30))
            ->orderBy('timestamp', 'desc')
            ->get();

        $totalRequests = $logs->count();
        $totalCost = (float) $logs->sum('total_cost');
        $totalInputTokens = (int) $logs->sum('input_tokens');
        $totalOutputTokens = (int) $logs->sum('output_tokens');
        $avgCostPerReq = $totalRequests > 0 ? $totalCost / $totalRequests : 0.0;

        $errorsCount = (int) ($key->error_count ?? 0);
        $successRate = $totalRequests > 0 ? max(0, min(100, round((($totalRequests - $errorsCount) / $totalRequests) * 100, 1))) : 100.0;

        $dailyCosts = DB::table('usage_logs')
            ->selectRaw('DATE(COALESCE(timestamp, created_at)) as log_date, SUM(total_cost) as day_cost')
            ->where('api_key_id', $id)
            ->where('timestamp', '>=', now()->subDays(30))
            ->groupBy('log_date')
            ->orderBy('log_date', 'asc')
            ->pluck('day_cost', 'log_date')->toArray();

        $labels = [];
        $costSeries = [];
        for ($i = 29; $i >= 0; $i--) {
            $dateStr = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $costSeries[] = round((float) ($dailyCosts[$dateStr] ?? 0.0), 4);
        }

        $recentRequests = DB::table('usage_logs')
            ->leftJoin('ai_models', 'usage_logs.model_id', '=', 'ai_models.id')
            ->where('usage_logs.api_key_id', $id)
            ->select(
                'usage_logs.*',
                DB::raw("COALESCE(ai_models.name, 'Default Model') as model_name")
            )
            ->orderBy('usage_logs.created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'key_id' => $key->id,
                'key_name' => $key->name,
                'key_prefix' => $key->key_prefix ?? 'sk-••••',
                'provider_name' => $key->provider?->name ?? 'Universal',
                'total_requests' => $totalRequests,
                'success_rate' => $successRate,
                'total_cost' => round($totalCost, 4),
                'avg_cost_per_req' => round($avgCostPerReq, 6),
                'input_tokens' => $totalInputTokens,
                'output_tokens' => $totalOutputTokens,
                'error_count' => $errorsCount,
                'chart_labels' => $labels,
                'cost_series' => $costSeries,
                'recent_requests' => $recentRequests,
            ],
        ]);
    }

    public function simulateBattle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model_a_id' => 'required|string',
            'model_b_id' => 'required|string',
            'message' => 'required|string',
        ]);

        $modelA = AIModel::with('provider')->find($validated['model_a_id']);
        $modelB = AIModel::with('provider')->find($validated['model_b_id']);

        if (! $modelA || ! $modelB) {
            return response()->json(['success' => false, 'message' => 'Models not found.'], 404);
        }

        $resA = $this->aiHubService->simulateChat($modelA->provider_id, $modelA->id, $validated['message']);
        $resB = $this->aiHubService->simulateChat($modelB->provider_id, $modelB->id, $validated['message']);

        return response()->json([
            'success' => true,
            'data' => [
                'model_a' => ['model' => $modelA->name, 'provider' => $modelA->provider?->name, 'response' => $resA],
                'model_b' => ['model' => $modelB->name, 'provider' => $modelB->provider?->name, 'response' => $resB],
            ],
        ]);
    }

    public function storeAbExperiment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'intent_name' => 'required|string|max:255',
            'model_a_id' => 'required|string',
            'model_b_id' => 'required|string',
            'weight_a' => 'integer|min:0|max:100',
            'weight_b' => 'integer|min:0|max:100',
            'goal_metric' => 'string|in:lowest_cost,lowest_latency,highest_success',
        ]);

        $experiment = AiAbExperiment::create(array_merge($validated, ['id' => Str::uuid()]));

        return response()->json([
            'success' => true,
            'message' => 'A/B Experiment created successfully.',
            'data' => $experiment,
        ]);
    }

    public function updateAbExperimentWeights(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'weight_a' => 'required|integer|min:0|max:100',
            'weight_b' => 'required|integer|min:0|max:100',
        ]);

        $experiment = AiAbExperiment::find($id);
        if (! $experiment) {
            return response()->json(['success' => false, 'message' => 'Experiment not found.'], 404);
        }

        $experiment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Experiment weights updated.',
            'data' => $experiment,
        ]);
    }

    public function syncModels(string $id): JsonResponse
    {
        $syncedCount = $this->aiHubService->syncModels($id);

        return response()->json([
            'success' => true,
            'message' => "Successfully synced {$syncedCount} models.",
            'data' => ['synced_count' => $syncedCount],
        ]);
    }
}
