<?php

namespace App\Services\AiModelsHub;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DynamicRestProvider implements AiProviderInterface
{
    protected string $providerId;

    protected EncryptedApiKeyStorage $keyStorage;

    protected ?object $providerRecord = null;

    protected ?string $currentKeyId = null;

    public function __construct(string $providerId, EncryptedApiKeyStorage $keyStorage)
    {
        $this->providerId = $providerId;
        $this->keyStorage = $keyStorage;
        $this->providerRecord = DB::table('ai_providers')->where('id', $providerId)->first();
    }

    protected function getProviderRecord(): ?object
    {
        return $this->providerRecord;
    }

    protected function getApiKey(): ?string
    {
        return $this->keyStorage->getDecryptedKey($this->providerId, $this->currentKeyId);
    }

    protected function buildHeaders(): array
    {
        $apiKey = $this->getApiKey();
        $record = $this->getProviderRecord();

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($apiKey && $record && $record->auth_header_format) {
            $authFormat = $record->auth_header_format;

            // Support custom headers format like "x-goog-api-key: {key}" or "Authorization: Bearer {key}"
            if (str_contains($authFormat, ':')) {
                [$headerName, $headerValFormat] = explode(':', $authFormat, 2);
                $authValue = str_ireplace(['{KEY}', '{API_KEY}', '{key}'], $apiKey, $headerValFormat);
                $headers[trim($headerName)] = trim($authValue);
            } else {
                $authValue = str_ireplace(['{KEY}', '{API_KEY}', '{key}'], $apiKey, $authFormat);
                $parts = explode(' ', trim($authValue), 2);
                if (count($parts) === 2) {
                    $prefix = strtolower($parts[0]);
                    if ($prefix === 'bearer' || $prefix === 'key') {
                        $headers['Authorization'] = trim($authValue);
                    } else {
                        $headers[trim($parts[0])] = trim($parts[1]);
                    }
                } else {
                    $headers['Authorization'] = $authValue;
                }
            }
        } elseif ($apiKey) {
            // Default to Bearer if format isn't specified
            $headers['Authorization'] = 'Bearer '.$apiKey;
        }

        return $headers;
    }

    public function getProviderName(): string
    {
        return $this->getProviderRecord() ? $this->getProviderRecord()->name : 'Unknown Dynamic Provider';
    }

    public function getAvailableModels(): array
    {
        $record = $this->getProviderRecord();
        if (! $record || ! $record->models_fetch_endpoint) {
            return [];
        }

        try {
            $url = rtrim($record->base_url, '/').'/'.ltrim($record->models_fetch_endpoint, '/');
            $response = Http::withHeaders($this->buildHeaders())
                ->withOptions(['verify' => config('services.ai.verify_ssl', true)])
                ->timeout(15)
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();

                return $this->normalizeModelsResponse($data);
            }

            Log::warning("Model fetch returned HTTP {$response->status()} for provider {$this->providerId}: ".$response->body());
        } catch (\Exception $e) {
            Log::error('Failed to fetch dynamic models: '.$e->getMessage());
        }

        return [];
    }

    /**
     * Normalize different provider API response formats into a consistent array.
     * Handles: OpenAI { data: [] }, Anthropic { models: [] }, Google { models: [] }, direct arrays []
     */
    protected function normalizeModelsResponse(mixed $data): array
    {
        $rawList = [];

        // OpenAI format: { "object": "list", "data": [ { "id": "gpt-4", ... } ] }
        if (isset($data['data']) && is_array($data['data'])) {
            $rawList = $data['data'];
        }
        // Anthropic / Google format: { "models": [ { "name": "models/gemini-1.5-flash", ... } ] }
        elseif (isset($data['models']) && is_array($data['models'])) {
            $rawList = $data['models'];
        }
        // Ollama / direct array: [ { "name": "llama3", ... } ]
        elseif (is_array($data) && ! empty($data) && isset($data[0])) {
            $rawList = $data;
        }

        return array_values(array_filter(array_map(function ($model) {
            // Google returns "name" as "models/gemini-1.5-flash" and "displayName"
            // We extract the short ID from the "name" path
            $rawName = $model['name'] ?? null;
            $id = $model['id'] ?? ($rawName ? basename(str_replace('models/', '', $rawName)) : null);
            $name = $model['displayName'] ?? $model['display_name'] ?? $model['name'] ?? $model['id'] ?? null;

            if (! $id && ! $name) {
                return null; // skip malformed entries
            }

            return [
                'id' => $id ?? $name,
                'name' => $name ?? $id,
                'description' => $model['description'] ?? null,
            ];
        }, $rawList)));
    }

    public function getDefaultModel(): string
    {
        $models = $this->getAvailableModels();

        return ! empty($models) ? $models[0]['id'] : '';
    }

    public function generateText(string $prompt, array $options = []): array
    {
        $record = $this->getProviderRecord();
        if (! $record || ! $record->generate_endpoint) {
            return ['success' => false, 'error' => 'No generation endpoint configured'];
        }

        $url = rtrim($record->base_url, '/').'/'.ltrim($record->generate_endpoint, '/');

        $payload = [
            'model' => $options['model'] ?? $this->getDefaultModel(),
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        if (isset($options['max_tokens'])) {
            $payload['max_tokens'] = $options['max_tokens'];
        }

        $maxRetries = 3;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::withHeaders($this->buildHeaders())
                    ->withOptions(['verify' => config('services.ai.verify_ssl', true)])
                    ->post($url, $payload);

                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['choices'][0]['message']['content'] ?? '';
                    $usage = $data['usage'] ?? ['input_tokens' => 0, 'output_tokens' => 0];

                    return [
                        'success' => true,
                        'provider' => $this->getProviderName(),
                        'model' => $payload['model'],
                        'content' => $content,
                        'used_key_id' => $this->currentKeyId,
                        'usage' => [
                            'input_tokens' => $usage['prompt_tokens'] ?? 0,
                            'output_tokens' => $usage['completion_tokens'] ?? 0,
                        ],
                    ];
                }

                $status = $response->status();
                $body = $response->body();

                if (in_array($status, [402, 403, 429]) || str_contains(strtolower($body), 'quota') || str_contains(strtolower($body), 'rate limit')) {
                    if ($this->currentKeyId) {
                        $this->keyStorage->flagKeyExhausted($this->currentKeyId, 60, "HTTP {$status}: ".substr($body, 0, 150));
                        if ($attempt < $maxRetries) {
                            Log::info("Rotating API key for provider [{$this->providerId}] after attempt {$attempt} rate/quota limit.");

                            continue;
                        }
                    }
                }

                return ['success' => false, 'error' => "HTTP {$status}: ".$body, 'status' => $status];
            } catch (\Exception $e) {
                Log::error('Dynamic generation attempt '.$attempt.' failed: '.$e->getMessage());
                if ($attempt === $maxRetries) {
                    return ['success' => false, 'error' => $e->getMessage()];
                }
            }
        }

        return ['success' => false, 'error' => 'All key attempts failed'];
    }

    public function generateEmbeddings(string $text, array $options = []): array
    {
        $record = $this->getProviderRecord();
        if (! $record) {
            return ['success' => false, 'error' => 'Provider record not found'];
        }

        // Default to OpenAI compatible embeddings endpoint
        $url = rtrim($record->base_url, '/').'/v1/embeddings';

        $payload = [
            'model' => $options['model'] ?? 'text-embedding-3-small',
            'input' => $text,
        ];

        $maxRetries = 3;
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::withHeaders($this->buildHeaders())
                    ->withOptions(['verify' => config('services.ai.verify_ssl', true)])
                    ->post($url, $payload);

                if ($response->successful()) {
                    $data = $response->json();
                    $embedding = $data['data'][0]['embedding'] ?? null;

                    if ($embedding) {
                        return [
                            'success' => true,
                            'provider' => $this->getProviderName(),
                            'model' => $payload['model'],
                            'used_key_id' => $this->currentKeyId,
                            'vector' => $embedding,
                            'usage' => [
                                'input_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                            ],
                        ];
                    }

                    return ['success' => false, 'error' => 'Malformed response from provider'];
                }

                $status = $response->status();
                $body = $response->body();

                if (in_array($status, [402, 403, 429]) || str_contains(strtolower($body), 'quota') || str_contains(strtolower($body), 'rate limit')) {
                    if ($this->currentKeyId) {
                        $this->keyStorage->flagKeyExhausted($this->currentKeyId, 60, "HTTP {$status}: ".substr($body, 0, 150));
                        if ($attempt < $maxRetries) {
                            continue;
                        }
                    }
                }

                return ['success' => false, 'error' => "HTTP {$status}: ".$body];
            } catch (\Exception $e) {
                Log::error('Dynamic embeddings attempt '.$attempt.' failed: '.$e->getMessage());
                if ($attempt === $maxRetries) {
                    return ['success' => false, 'error' => $e->getMessage()];
                }
            }
        }

        return ['success' => false, 'error' => 'All embedding attempts failed'];
    }

    public function validateRequest(array $request): array
    {
        return ['valid' => true];
    }

    public function estimateCost(string $model, int $inputTokens, int $outputTokens = 0): float
    {
        return 0.0;
    }

    public function getHealthStatus(): array
    {
        $record = $this->getProviderRecord();
        if (! $record) {
            return ['status' => 'unknown'];
        }

        $endpoint = $record->test_endpoint ?: $record->models_fetch_endpoint;
        if (! $endpoint) {
            $endpoint = match (strtolower($record->schema ?? 'openai')) {
                'gemini' => '/models',
                'anthropic' => '/v1/models',
                default => '/models',
            };
        }

        $baseUrl = rtrim($record->base_url, '/');
        $endpointPath = '/'.ltrim($endpoint, '/');

        // Prevent duplicate /v1/v1 in URL
        if (str_ends_with(strtolower($baseUrl), '/v1') && str_starts_with(strtolower($endpointPath), '/v1/')) {
            $endpointPath = substr($endpointPath, 3);
        }

        $url = $baseUrl.$endpointPath;
        $apiKey = $this->getApiKey();

        try {
            $start = microtime(true);
            $response = Http::withHeaders($this->buildHeaders())
                ->withOptions(['verify' => config('services.ai.verify_ssl', true)])
                ->timeout(10)
                ->get($url);
            $latencyMs = (int) round((microtime(true) - $start) * 1000);

            $status = $response->status();
            $isHealthy = $response->successful() || (in_array($status, [401, 403]) && ! $apiKey);

            $result = [
                'status' => $isHealthy ? 'healthy' : 'unhealthy',
                'latency' => $latencyMs,
                'http_status' => $status,
                'url' => $url,
            ];

            if (! $response->successful()) {
                $data = $response->json();
                $body = $response->body();
                $result['provider_error'] = $data['error']['message'] ?? $data['message'] ?? (substr($body, 0, 300) ?: "HTTP Error {$status}");
                if (in_array($status, [401, 403]) && ! $apiKey) {
                    $result['provider_error'] = 'Endpoint is online & reachable (Authentication key required for full request).';
                }
            }

            return $result;
        } catch (\Exception $e) {
            return ['status' => 'offline', 'error' => $e->getMessage(), 'url' => $url];
        }
    }

    public function getRateLimitStatus(): array
    {
        return ['limit' => -1, 'remaining' => -1, 'reset' => -1];
    }
}
