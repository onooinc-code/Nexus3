<?php

namespace App\Services\Tasks;

use App\Models\AgentTask;
use Illuminate\Support\Facades\Http;

class TaskApiExecutionService
{
    /**
     * Execute an API request task.
     */
    public function execute(AgentTask $task): array
    {
        $payload = $task->payload_data ?? [];

        $url = $payload['url'] ?? null;
        $method = strtoupper($payload['method'] ?? 'GET');
        $headers = $payload['headers'] ?? [];
        $data = $payload['data'] ?? [];
        $timeout = $payload['timeout'] ?? 30;

        if (empty($url)) {
            throw new \Exception('URL is required for API tasks.');
        }

        $request = Http::timeout($timeout)->withHeaders($headers);

        try {
            $response = match ($method) {
                'GET' => $request->get($url, $data),
                'POST' => $request->post($url, $data),
                'PUT' => $request->put($url, $data),
                'PATCH' => $request->patch($url, $data),
                'DELETE' => $request->delete($url, $data),
                default => throw new \Exception("Unsupported HTTP method: {$method}"),
            };

            return [
                'status' => $response->successful() ? 'success' : 'failed',
                'status_code' => $response->status(),
                'response_body' => $response->json() ?? $response->body(),
                'headers' => $response->headers(),
            ];
        } catch (\Exception $e) {
            throw new \Exception('API Request failed: '.$e->getMessage());
        }
    }
}
