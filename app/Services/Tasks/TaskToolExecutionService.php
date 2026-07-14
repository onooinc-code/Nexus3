<?php

namespace App\Services\Tasks;

use App\Models\AgentTask;
use App\Services\AgentToolRegistry;

class TaskToolExecutionService
{
    protected AgentToolRegistry $toolRegistry;

    public function __construct(AgentToolRegistry $toolRegistry)
    {
        $this->toolRegistry = $toolRegistry;
    }

    /**
     * Execute an agent tool directly via a task.
     */
    public function execute(AgentTask $task): array
    {
        $payload = $task->payload_data ?? [];

        $toolName = $payload['tool_name'] ?? null;
        $toolParameters = $payload['parameters'] ?? [];

        if (empty($toolName)) {
            throw new \Exception('Tool name is required for tool tasks.');
        }

        try {
            $tool = $this->toolRegistry->get($toolName);

            if (! $tool) {
                throw new \Exception("Tool {$toolName} not found.");
            }

            // Execute the tool with parameters
            $result = $this->toolRegistry->execute($toolName, $toolParameters);

            return [
                'status' => 'success',
                'tool_name' => $toolName,
                'result' => $result,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Tool Execution Error: '.$e->getMessage());
        }
    }
}
