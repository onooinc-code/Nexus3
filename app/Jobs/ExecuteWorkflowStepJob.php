<?php

namespace App\Jobs;

use App\Models\WorkflowExecution;
use App\Models\WorkflowStepLog;
use App\Services\Workflows\WorkflowTaskDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExecuteWorkflowStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 900;

    public function __construct(
        public string $executionId,
        public array $step,
        public array $variables
    ) {
        $this->onQueue('workflows');
    }

    public function handle(WorkflowTaskDispatcher $dispatcher): void
    {
        $execution = WorkflowExecution::findOrFail($this->executionId);
        $result = $dispatcher->dispatch($execution, $this->step, $this->variables);

        // Record step log for the branch step execution
        WorkflowStepLog::create([
            'execution_id' => $execution->id,
            'workflow_id' => $execution->workflow_id,
            'step_id' => $this->step['id'],
            'step_name' => $this->step['name'],
            'step_type' => $this->step['type'] ?? 'action',
            'status' => ($result['success'] ?? false) ? 'completed' : 'failed',
            'input' => $this->variables,
            'output' => $result['output'] ?? $result,
            'error' => $result['error'] ?? null,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        // Lock & reload execution state to update parallel branch completion atomically
        $execution->refresh();

        $state = $execution->runtime_state ?? [];
        $waitingFor = $state['waiting_for'] ?? null;

        if ($waitingFor && $waitingFor['type'] === 'parallel_branches') {
            $completed = $waitingFor['completed_branches'] ?? [];
            if (! in_array($this->step['id'], $completed)) {
                $completed[] = $this->step['id'];
            }
            $waitingFor['completed_branches'] = $completed;
            $state['waiting_for'] = $waitingFor;

            // Merge branch output variables into the parent runtime state
            if (! empty($result['output'])) {
                $state['variables'] = array_merge($state['variables'] ?? [], $result['output']);
            }

            $execution->update([
                'runtime_state' => $state,
            ]);

            Log::info("Parallel branch completed: {$this->step['id']} ({$this->step['name']}) in execution {$execution->id}. ".count($completed).'/'.$waitingFor['total_branches'].' done.');

            // Check if all branches are finished
            if (count($completed) >= $waitingFor['total_branches']) {
                Log::info("All parallel branches completed for step {$waitingFor['step_id']}. Resuming workflow execution {$execution->id}.");

                $state['waiting_for'] = null; // Clear wait barrier

                $execution->update([
                    'status' => WorkflowExecution::STATUS_PENDING,
                    'paused_at' => null,
                    'runtime_state' => $state,
                ]);

                // Dispatch execution job to resume the interpreter
                ExecuteWorkflowJob::dispatch($execution->id);
            }
        }
    }
}
