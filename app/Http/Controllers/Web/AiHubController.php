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
use App\Services\AiHubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $name = $validated['name'] ?? 'Default Key';
        $result = $this->aiHubService->storeApiKey($validated['provider'], $validated['api_key'], $name);

        return response()->json([
            'success' => true,
            'message' => 'API key stored successfully.',
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

        $response = $this->aiHubService->simulateChat(
            $validated['provider_id'],
            $validated['model_id'],
            $validated['message']
        );

        return response()->json([
            'success' => true,
            'message' => 'Chat simulated successfully.',
            'data' => [
                'response' => $response,
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
                if (!$host) return $fail("The $attribute is invalid.");
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
            'message' => $result['message'] ?? 'Ping completed.',
            'data' => $result,
        ], ($result['success'] ?? false) ? 200 : 400);
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
