<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AIApiKey;
use App\Models\AiAuditTrail;
use App\Models\AIModel;
use App\Models\AIProvider;
use App\Models\IntentRouting;
use App\Models\Setting;
use App\Models\UsageLog;
use App\Services\AiModelsHub\DynamicRestProvider;
use App\Services\AiModelsHub\EncryptedApiKeyStorage;
use Illuminate\Support\Facades\Crypt;

class AiHubService
{
    /**
     * Toggle AI provider status.
     */
    public function toggleProvider(string $providerId, bool $isActive): array
    {
        $provider = AIProvider::findOrFail($providerId);
        $provider->is_active = $isActive;
        $provider->save();

        return [
            'provider' => $provider->id,
            'is_active' => $provider->is_active,
        ];
    }

    /**
     * Store API key for an AI provider.
     */
    public function storeApiKey(string $providerId, string $apiKey, string $name = 'Default Key'): array
    {
        $encryptedKey = Crypt::encryptString($apiKey);

        $isFirst = !AIApiKey::where('provider_id', $providerId)->exists();
        $keyRecord = AIApiKey::create([
            'provider_id' => $providerId,
            'key_hash' => $encryptedKey,
            'name' => $name,
            'is_active' => true,
            'is_default' => $isFirst,
        ]);

        return $keyRecord->toArray();
    }

    /**
     * Revoke (Delete) an API key.
     */
    public function revokeApiKey(string $keyId): bool
    {
        $key = AIApiKey::findOrFail($keyId);

        return (bool) $key->delete();
    }

    /**
     * Get API keys usage stats vs limits.
     */
    public function getApiKeysStats(): array
    {
        $monthCost = UsageLog::whereMonth('created_at', today()->month)->sum('total_cost');

        $budgetSetting = Setting::where('key', 'ai_budget_monthly')->first();
        $budget = $budgetSetting ? (float) $budgetSetting->value : 100.00;

        return [
            'month_cost' => round((float) $monthCost, 2),
            'budget' => $budget,
            'usage_percent' => $budget > 0 ? min(100, round((($monthCost / $budget) * 100), 2)) : 0,
        ];
    }

    /**
     * Add a new AI Provider.
     */
    public function storeProvider(array $data): array
    {
        $apiKey = $data['api_key'] ?? null;
        unset($data['api_key']);

        $provider = AIProvider::create($data);

        if ($apiKey) {
            $this->storeApiKey($provider->id, $apiKey, 'Default Key');
        }

        return $provider->toArray();
    }

    /**
     * Toggle Model (currently toggles provider to match existing HubController logic).
     */
    public function toggleModel(string $id, bool $isActive): array
    {
        $model = AIModel::findOrFail($id);
        $model->is_active = $isActive;
        $model->save();

        return [
            'success' => true,
        ];
    }

    /**
     * Store (create or edit) an Intent Routing rule.
     */
    public function storeRoutingRule(array $data): array
    {
        $id = $data['id'] ?? null;

        $routing = IntentRouting::updateOrCreate(
            ['id' => $id],
            $data
        );

        return $routing->toArray();
    }

    /**
     * Toggle Intent Routing rule.
     */
    public function toggleRoutingRule(string $id, bool $isActive): array
    {
        $routing = IntentRouting::findOrFail($id);
        $routing->is_active = $isActive;
        $routing->save();

        return $routing->toArray();
    }

    /**
     * Delete Intent Routing rule.
     */
    public function deleteRoutingRule(string $id): bool
    {
        $routing = IntentRouting::findOrFail($id);

        return (bool) $routing->delete();
    }

    /**
     * Update AI Hub budget limit.
     */
    public function updateBudget(string $type, float $limit): array
    {
        $key = 'ai_budget_'.$type;
        Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $limit,
                'type' => 'numeric',
                'group' => 'ai_hub',
                'is_public' => false,
            ]
        );

        return [
            'type' => $type,
            'limit' => $limit,
        ];
    }

    /**
     * Get Cost Charts data from usage logs.
     */
    public function getCostCharts(): array
    {
        $logsCursor = UsageLog::selectRaw('DATE(created_at) as date, provider_id, SUM(total_cost) as daily_cost')
            ->with('provider:id,name')
            ->groupBy('date', 'provider_id')
            ->orderBy('date', 'asc')
            ->cursor();

        $datesMap = [];
        $providersMap = [];
        $logData = [];

        foreach ($logsCursor as $log) {
            $date = $log->date;
            $providerName = $log->provider?->name;

            if ($date !== null) {
                $datesMap[$date] = true;
            }

            if ($providerName !== null) {
                $providersMap[$providerName] = true;
                if (!isset($logData[$providerName])) {
                    $logData[$providerName] = [];
                }
                $logData[$providerName][$date] = $log->daily_cost;
            }
        }

        $dates = array_keys($datesMap);
        $providers = array_keys($providersMap);
        sort($dates); // Keep dates ordered ascending

        $series = [];
        foreach ($providers as $providerName) {
            $series[$providerName] = [];
            foreach ($dates as $date) {
                $cost = $logData[$providerName][$date] ?? 0.0;
                $series[$providerName][] = round((float) $cost, 2);
            }
        }

        return [
            'dates' => $dates,
            'series' => $series,
        ];
    }

    /**
     * Simulate AI Chat response.
     */
    public function simulateChat(string $providerId, string $modelId, string $message): string
    {
        $inputTokens = max(5, strlen($message) * 4);
        $outputTokens = rand(100, 300);
        $totalTokens = $inputTokens + $outputTokens;

        $inputCost = $inputTokens * 0.000015;
        $outputCost = $outputTokens * 0.000030;
        $totalCost = $inputCost + $outputCost;

        UsageLog::create([
            'provider_id' => $providerId,
            'model_id' => $modelId,
            'intent_name' => 'playground_chat',
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'input_cost' => $inputCost,
            'output_cost' => $outputCost,
            'total_cost' => $totalCost,
        ]);

        AiAuditTrail::create([
            'event_type' => 'route_executed',
            'provider_id' => $providerId,
            'model_id' => $modelId,
            'intent' => 'playground_chat',
            'status' => 'success',
            'latency_ms' => rand(150, 450),
            'estimated_cost' => $totalCost,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'metadata' => ['cache_hit' => (rand(0, 10) > 7)],
        ]);

        return sprintf(
            "Mocked AI response for message: '%s' using model '%s' from provider '%s'. Generated %d input tokens and %d output tokens. Estimated cost: $%s.",
            $message,
            $modelId,
            $providerId,
            $inputTokens,
            $outputTokens,
            number_format($totalCost, 4)
        );
    }

    public function pingProvider(array $data): array
    {
        $restProvider = new class($data) extends DynamicRestProvider {
            protected array $tempData;

            public function __construct(array $data)
            {
                $this->tempData = $data;
                // No parent constructor call needed for ping
            }

            protected function getProviderRecord(): ?object
            {
                return (object) [
                    'name' => 'Ping Provider',
                    'base_url' => $this->tempData['base_url'],
                    'auth_header_format' => $this->tempData['auth_header_format'] ?? null,
                    'test_endpoint' => $this->tempData['test_endpoint'] ?? null,
                    'models_fetch_endpoint' => $this->tempData['models_fetch_endpoint'] ?? '/v1/models',
                ];
            }

            protected function getApiKey(): ?string
            {
                return $this->tempData['api_key'] ?? null;
            }
        };

        $health = $restProvider->getHealthStatus();
        $isHealthy = ($health['status'] ?? '') === 'healthy';

        return [
            'success' => $isHealthy,
            'message' => $isHealthy ? 'Ping successful.' : 'Ping failed.',
            'health' => $health,
        ];
    }

    /**
     * Sync models from AI Providers.
     */
    public function syncModels(?string $providerId = null): int
    {
        $query = AIProvider::where('is_active', true);
        if ($providerId) {
            $query->where('id', $providerId);
        }

        $providers = $query->cursor();
        $syncedCount = 0;
        $keyStorage = app(EncryptedApiKeyStorage::class);

        foreach ($providers as $provider) {
            // Delete old mocked models just in case
            AIModel::where('provider_id', $provider->id)->where('model_id', 'like', 'mock-model-%')->delete();

            $restProvider = new DynamicRestProvider($provider->id, $keyStorage);
            $availableModels = $restProvider->getAvailableModels();

            foreach ($availableModels as $modelData) {
                AIModel::updateOrCreate(
                    [
                        'provider_id' => $provider->id,
                        'model_id' => $modelData['id'],
                    ],
                    [
                        'name' => $modelData['name'],
                        'is_active' => true,
                    ]
                );
                $syncedCount++;
            }

            $provider->update(['last_synced_at' => now()]);
        }

        return $syncedCount;
    }

    /**
     * Rotate expired API keys.
     */
    public function rotateKeys(bool $force = false): int
    {
        $query = AIApiKey::where('is_active', true);

        if (! $force) {
            $query->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()->addDays(3)); // Rotate keys expiring in <= 3 days
        }

        $keys = $query->cursor();
        $rotatedCount = 0;

        foreach ($keys as $key) {
            // Mocking rotation logic
            $key->update([
                'is_active' => false,
                'revoked_at' => now(),
            ]);

            // Create a new key
            AIApiKey::create([
                'provider_id' => $key->provider_id,
                'name' => $key->name.' (Rotated)',
                'key_hash' => hash('sha256', 'mock-new-key-'.uniqid()),
                'encrypted_key' => encrypt('mock-new-key-'.uniqid()),
                'is_active' => true,
                'expires_at' => now()->addDays(90),
            ]);

            $rotatedCount++;
        }

        return $rotatedCount;
    }

    /**
     * Get dynamic telemetry statistics for dashboard and ribbon.
     */
    public function getTelemetry(): array
    {
        $now = now();
        $sub24h = $now->copy()->subDay();
        $startOfMonth = $now->copy()->startOfMonth();

        // 1. Total Requests 24h
        $totalRequests24h = UsageLog::where('created_at', '>=', $sub24h)->count();

        // 2. Success Rate (from ai_audit_trails)
        $auditQuery = AiAuditTrail::where('created_at', '>=', $sub24h);
        $totalAudit = (clone $auditQuery)->count();
        $successAudit = (clone $auditQuery)->where('status', 'success')->count();
        $successRate = $totalAudit > 0 ? round(($successAudit / $totalAudit) * 100, 1) : 100.0;

        // 3. Average Latency (from ai_audit_trails)
        $avgLatency = (clone $auditQuery)->avg('latency_ms') ?? 0.0;
        $avgLatency = round((float) $avgLatency, 0);

        // 4. Total Cost (Month) (from usage_logs)
        $totalCostMonth = UsageLog::where('created_at', '>=', $startOfMonth)->sum('total_cost');
        $totalCostMonth = round((float) $totalCostMonth, 2);

        // 5. Active Providers Count (from usage_logs)
        $activeProvidersCount = UsageLog::where('created_at', '>=', $sub24h)
            ->distinct('provider_id')
            ->count('provider_id');

        // 6. Cache Hit Rate (percentage of cache_hit = true in audit trail metadata)
        $cacheHits = 0;
        $auditCursor = (clone $auditQuery)->select('metadata')->cursor();
        foreach ($auditCursor as $trail) {
            $metadata = $trail->metadata;
            if (is_array($metadata) && ! empty($metadata['cache_hit'])) {
                $cacheHits++;
            }
        }
        $cacheHitRate = $totalAudit > 0 ? round(($cacheHits / $totalAudit) * 100, 0) : 0;

        // 7. Today's Cost
        $costToday = UsageLog::where('created_at', '>=', $now->copy()->startOfDay())->sum('total_cost');
        $costToday = round((float) $costToday, 2);

        // 8. Tokens per minute (rough estimation from last 5 minutes)
        $last5Min = $now->copy()->subMinutes(5);
        $totalTokens5Min = UsageLog::where('created_at', '>=', $last5Min)->sum(\DB::raw('input_tokens + output_tokens'));
        $tpm = round($totalTokens5Min / 5, 0);

        // 9. Active Requests (requests in the last 10 seconds)
        $last10Sec = $now->copy()->subSeconds(10);
        $activeRequests = AiAuditTrail::where('created_at', '>=', $last10Sec)->count();

        // 10. Token timeline for last 7 days (line chart)
        $days = [];
        $inputTokensSeries = [];
        $outputTokensSeries = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateString = $now->copy()->subDays($i)->toDateString();
            $dayName = $now->copy()->subDays($i)->format('D');
            $days[] = $dayName;

            $dailyUsage = UsageLog::whereDate('created_at', $dateString)
                ->selectRaw('SUM(input_tokens) as input, SUM(output_tokens) as output')
                ->first();

            $inputTokensSeries[] = $dailyUsage ? round(($dailyUsage->input ?? 0) / 1000, 1) : 0.0; // in thousands (K)
            $outputTokensSeries[] = $dailyUsage ? round(($dailyUsage->output ?? 0) / 1000, 1) : 0.0; // in thousands (K)
        }

        return [
            'success_rate' => $successRate,
            'avg_latency' => $avgLatency,
            'total_requests_24h' => $totalRequests24h,
            'total_cost_month' => $totalCostMonth,
            'active_providers_count' => $activeProvidersCount,
            'cache_hit_rate' => $cacheHitRate,
            'cost_today' => $costToday,
            'tpm' => $tpm,
            'active_requests' => $activeRequests,
            'token_timeline' => [
                'labels' => $days,
                'input' => $inputTokensSeries,
                'output' => $outputTokensSeries,
            ],
        ];
    }
}
