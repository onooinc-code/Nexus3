<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\DOMEventDispatched;
use App\Events\TaskDispatchedToBrowserAgent;
use App\Http\Controllers\Controller;
use App\Services\LogService;
use App\Services\TaskManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DomEventTriggerController extends Controller
{
    public function __construct(
        protected LogService $logService,
        protected TaskManagementService $taskManagementService
    ) {}

    /**
     * Handle incoming DOM events from Chrome Extension and trigger automation rules/tasks.
     *
     * POST /api/v1/events/dom-trigger
     */
    public function handle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'dom_selector' => 'nullable|string|max:500',
            'page_url' => 'nullable|string|max:1000',
            'payload' => 'nullable|array',
            'origin_agent_id' => 'nullable|string|max:255',
            'target_agent_id' => 'nullable|string|max:255',
            'auto_task' => 'nullable|boolean',
            'dynamic_system_instruction' => 'nullable|string',
        ]);

        $eventData = [
            'event_name' => $validated['event_name'],
            'dom_selector' => $validated['dom_selector'] ?? null,
            'page_url' => $validated['page_url'] ?? null,
            'payload' => $validated['payload'] ?? [],
            'timestamp' => now()->toIso8601String(),
        ];

        // 1. Broadcast DOM Event to Reverb channel
        DOMEventDispatched::dispatch($eventData);

        $this->logService->info('DOM Event received and dispatched', [
            'channel' => 'browser_agent',
            'type' => 'dom_event_trigger',
            'context' => $eventData,
        ]);

        $createdTask = null;

        // 2. Automatically spawn Task if auto_task is true or by default for trigger events
        if (! empty($validated['auto_task']) || ! empty($validated['dynamic_system_instruction'])) {
            $targetAgentId = $validated['target_agent_id'] ?? 'ertugrul_browser_agent';
            $originAgentId = $validated['origin_agent_id'] ?? 'chrome_extension';

            $taskData = [
                'title' => 'Browser Action: '.$validated['event_name'],
                'description' => 'Automated task generated from DOM event trigger at '.($validated['page_url'] ?? 'unknown page'),
                'type' => 'agent',
                'task_type' => 'event_driven',
                'origin_agent_id' => $originAgentId,
                'target_agent_id' => $targetAgentId,
                'dynamic_system_instruction' => $validated['dynamic_system_instruction'] ?? 'Respond to DOM event: '.$validated['event_name'],
                'dom_event_trigger' => $eventData,
                'payload_data' => json_encode($eventData),
            ];

            $createdTask = $this->taskManagementService->create($taskData, $request->user()?->id);

            // Broadcast task to browser agent
            TaskDispatchedToBrowserAgent::dispatch($createdTask);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'DOM Event processed and broadcasted',
            'event' => $eventData,
            'task' => $createdTask,
        ], 200);
    }
}
