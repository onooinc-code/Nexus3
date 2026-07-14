<?php

namespace App\Services\Tasks;

use App\Models\AgentTask;
use Illuminate\Support\Facades\Process;

class TaskCodeExecutionService
{
    /**
     * Execute a code task.
     * Supports sandboxed and non-sandboxed modes.
     */
    public function execute(AgentTask $task): array
    {
        $payload = $task->payload_data ?? [];
        $code = $payload['code'] ?? '';
        $language = $payload['language'] ?? 'php';
        $sandboxed = $payload['sandboxed'] ?? true;

        if (empty($code)) {
            throw new \Exception('Code payload is required for code tasks.');
        }

        if ($sandboxed) {
            return $this->executeSandboxed($code, $language);
        }

        return $this->executeUnsafe($code, $language);
    }

    /**
     * Execute code inside a sandbox.
     * Currently a placeholder, but recommends using:
     * 1. Piston (https://github.com/engineer-man/piston) - A high performance general purpose code execution engine.
     * 2. Judge0 (https://github.com/judge0/judge0) - A robust, scalable open-source online code execution system.
     * 3. Docker runtime wrapper.
     */
    protected function executeSandboxed(string $code, string $language): array
    {
        // TODO: Integrate with Piston API or Judge0 API here.
        // Example for Piston:
        // $response = Http::post('http://piston-api:2000/api/v2/execute', [
        //     'language' => $language,
        //     'version' => '*',
        //     'files' => [['content' => $code]]
        // ]);
        // return $response->json();

        return [
            'status' => 'success',
            'mode' => 'sandboxed (simulated)',
            'language' => $language,
            'output' => "Simulated sandbox execution for {$language}",
            'message' => 'To make this real, install Piston or Judge0 open-source sandboxes and integrate the API call here.',
        ];
    }

    /**
     * Execute code directly on the host (UNSAFE).
     * Only supports PHP and Python currently.
     */
    protected function executeUnsafe(string $code, string $language): array
    {
        if ($language === 'php') {
            ob_start();
            try {
                $result = eval($code);
                $output = ob_get_clean();

                return [
                    'status' => 'success',
                    'mode' => 'unsafe',
                    'output' => $output,
                    'result' => $result,
                ];
            } catch (\Throwable $e) {
                ob_end_clean();
                throw new \Exception('PHP Execution Error: '.$e->getMessage());
            }
        } elseif ($language === 'python' || $language === 'python3') {
            $tmpFile = tempnam(sys_get_temp_dir(), 'nexus_py_');
            file_put_contents($tmpFile, $code);
            $process = Process::run("python3 {$tmpFile}");
            unlink($tmpFile);

            if ($process->failed()) {
                throw new \Exception('Python Execution Error: '.$process->errorOutput());
            }

            return [
                'status' => 'success',
                'mode' => 'unsafe',
                'output' => $process->output(),
            ];
        }

        throw new \Exception("Unsupported language for unsafe execution: {$language}");
    }
}
