<?php

namespace App\Services\PeopleConnect;

use App\Models\PeopleConnect\PeopleConnectContextSnapshot;
use Illuminate\Support\Facades\Http;

class PeopleConnectAgentReplyService
{
    public function generateDraft(PeopleConnectContextSnapshot $contextSnapshot, int $agentId): array
    {
        // Call AgentsHub API
        $response = Http::post(route('agents.run', ['id' => $agentId]), [
            'context' => $contextSnapshot->payload,
            'mode' => 'reply_draft',
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('AgentsHub call failed: '.$response->body());
        }

        $data = $response->json();

        return [
            'body' => $data['reply'] ?? '',
            'trace_id' => $data['trace_id'] ?? null,
        ];
    }
}
