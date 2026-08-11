<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RAGFlowClient
{
    protected $baseUrl;

    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.ragflow.url');
        $this->apiKey = config('services.ragflow.api_key');
    }

    public function post(string $endpoint, array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.$endpoint, $data);
            if ($response->failed()) {
                Log::error('RAGFlow API Error', ['endpoint' => $endpoint, 'response' => $response->body()]);
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('RAGFlow Connection Exception', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
