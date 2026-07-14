<?php

namespace App\Services\Workflows;

use App\Models\WorkflowEventTrigger;
use App\Services\LogService;
use App\Services\WorkflowExecutor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

class WorkflowEventTriggerService
{
    public function __construct(
        protected WorkflowExecutor $executor,
        protected LogService $logService
    ) {}

    public function handleEvent(string $eventName, array $payload): void
    {
        if (! Schema::hasTable('workflow_event_triggers')) {
            return;
        }

        $triggers = WorkflowEventTrigger::where('event_name', $eventName)
            ->where('is_active', true)
            ->with('workflow')
            ->get();

        foreach ($triggers as $trigger) {
            try {
                if (! $trigger->workflow || ! $trigger->workflow->is_active) {
                    continue;
                }

                if ($this->matchesConditions($trigger->condition_payload, $payload)) {
                    $this->logService->info('Executing event-triggered workflow', [
                        'channel' => 'workflow',
                        'type' => 'event_trigger',
                        'related_id' => $trigger->workflow_id,
                        'related_type' => 'App\Models\Workflow',
                        'context' => [
                            'trigger_id' => $trigger->id,
                            'event' => $eventName,
                        ],
                    ]);

                    $this->executor->execute($trigger->workflow, $payload);
                }
            } catch (\Exception $e) {
                $this->logService->error('Failed to execute event-triggered workflow', [
                    'channel' => 'workflow',
                    'type' => 'event_trigger_failed',
                    'related_id' => $trigger->workflow_id,
                    'related_type' => 'App\Models\Workflow',
                    'context' => [
                        'trigger_id' => $trigger->id,
                        'error' => $e->getMessage(),
                    ],
                ]);
            }
        }
    }

    protected function matchesConditions(?array $conditions, array $payload): bool
    {
        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $key => $value) {
            if (Arr::get($payload, $key) !== $value) {
                return false;
            }
        }

        return true;
    }

    public function registerWildcardListener(): void
    {
        if (! Schema::hasTable('workflow_event_triggers')) {
            return;
        }

        try {
            $eventNames = WorkflowEventTrigger::where('is_active', true)
                ->pluck('event_name')
                ->unique();

            foreach ($eventNames as $eventName) {
                if (
                    str_starts_with($eventName, 'Illuminate\\') ||
                    str_starts_with($eventName, 'eloquent.') ||
                    str_starts_with($eventName, 'bootstrapping: ') ||
                    str_starts_with($eventName, 'bootstrapped: ') ||
                    str_starts_with($eventName, 'artisan.') ||
                    str_starts_with($eventName, 'console.') ||
                    str_starts_with($eventName, 'cache.') ||
                    str_starts_with($eventName, 'queue.')
                ) {
                    continue;
                }

                Event::listen($eventName, function ($eventObj = null) use ($eventName) {
                    $payload = [];
                    if ($eventObj) {
                        if (is_object($eventObj)) {
                            if (method_exists($eventObj, 'toArray')) {
                                $payload = $eventObj->toArray();
                            } else {
                                $payload = json_decode(json_encode($eventObj), true) ?: [];
                            }
                        } elseif (is_array($eventObj)) {
                            $payload = $eventObj;
                        } else {
                            $payload = json_decode(json_encode($eventObj), true) ?: [];
                        }
                    }

                    $this->handleEvent($eventName, $payload);
                });
            }
        } catch (\Throwable $e) {
            // Prevent failure during service bootstrapping
        }
    }
}
