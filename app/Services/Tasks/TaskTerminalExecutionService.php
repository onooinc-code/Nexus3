<?php

namespace App\Services\Tasks;

use App\Models\AgentTask;
use Illuminate\Support\Facades\Process;

class TaskTerminalExecutionService
{
    /**
     * Execute a terminal command task.
     */
    public function execute(AgentTask $task): array
    {
        $payload = $task->payload_data ?? [];

        $command = $payload['command'] ?? null;
        $path = $payload['path'] ?? base_path();
        $timeout = $payload['timeout'] ?? 60;

        if (empty($command)) {
            throw new \Exception('Command is required for terminal tasks.');
        }

        // Warning: This allows executing arbitrary shell commands on the host.
        // It must be strictly controlled via permissions/policies.

        $process = Process::path($path)->timeout($timeout)->run($command);

        if ($process->failed()) {
            throw new \Exception('Command failed: '.$process->errorOutput());
        }

        return [
            'status' => 'success',
            'command' => $command,
            'output' => $process->output(),
            'exit_code' => $process->exitCode(),
        ];
    }
}
