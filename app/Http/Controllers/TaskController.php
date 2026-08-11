<?php

namespace App\Http\Controllers;

use App\Events\NotificationBroadcasted;
use App\Events\TaskDispatchedToBrowserAgent;
use App\Jobs\ProcessReActStep;
use App\Models\Agent;
use App\Models\AgentTask;
use App\Services\HedraSoul\HedraSoulNotificationService;
use App\Services\LogService;
use App\Services\TaskExecutionService;
use App\Services\TaskLogService;
use App\Services\TaskManagementService;
use App\Services\TaskQueueService;
use App\Services\TaskRoutingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function __construct(
        protected LogService $logService,
        protected TaskQueueService $queue,
        protected TaskRoutingService $router,
        protected TaskManagementService $taskManagementService,
        protected TaskExecutionService $taskExecutionService,
        protected TaskLogService $taskLogService
    ) {}

    public function index(Request $request)
    {
        $query = AgentTask::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('workflow_id')) {
            $query->where('workflow_id', $request->workflow_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by metadata token (used for optimistic creation correlation)
        if ($request->filled('metadata_token')) {
            $token = $request->metadata_token;
            $query->where('metadata->client_token', $token);
        }

        $tasks = $query->with(['agent', 'workflow'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $input = $request->all();

        // Normalize legacy parameters
        if ($request->has('due_at') && ! $request->has('due_date')) {
            $input['due_date'] = $request->input('due_at');
        }
        if ($request->has('dueDate') && ! $request->has('due_date')) {
            $input['due_date'] = $request->input('dueDate');
        }
        if ($request->has('metadata') && ! $request->has('payload_data')) {
            $metadata = $request->input('metadata');
            $input['payload_data'] = is_array($metadata) ? json_encode($metadata) : $metadata;
        }
        if (! $request->has('type')) {
            $input['type'] = 'agent';
        }

        if ($request->has('priority') && is_string($request->input('priority'))) {
            $priorityMap = ['low' => 2, 'medium' => 5, 'high' => 8];
            if (isset($priorityMap[$request->input('priority')])) {
                $input['priority'] = $priorityMap[$request->input('priority')];
            }
        }

        try {
            $task = $this->taskManagementService->create($input, $request->user()?->id);

            // Execute or dispatch based on target agent / type
            if ($task->target_agent_id === 'ertugrul_browser_agent' || Str::contains($task->target_agent_id ?? '', 'browser')) {
                // Initialize ReAct Engine loop by dispatching the first step
                ProcessReActStep::dispatch($task, []);
                TaskDispatchedToBrowserAgent::dispatch($task);
            } elseif ($task->type === 'agent') {
                $this->taskExecutionService->execute($task);
            } elseif ($task->type === 'system') {
                $this->taskExecutionService->executeNow($task);
            }

            return response()->json([
                'data' => $task,
                'message' => 'Task created and queued',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            $this->logService->error('Failed to create task in store', [
                'channel' => 'task',
                'type' => 'store_error',
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(AgentTask $task)
    {
        $task->load(['agent', 'workflow', 'steps']);

        return response()->json(['data' => $task]);
    }

    public function update(Request $request, AgentTask $task)
    {
        $input = $request->all();

        // Normalize legacy parameters
        if ($request->has('due_at') && ! $request->has('due_date')) {
            $input['due_date'] = $request->input('due_at');
        }
        if ($request->has('dueDate') && ! $request->has('due_date')) {
            $input['due_date'] = $request->input('dueDate');
        }
        if ($request->has('metadata') && ! $request->has('payload_data')) {
            $metadata = $request->input('metadata');
            $input['payload_data'] = is_array($metadata) ? json_encode($metadata) : $metadata;
        }
        if ($request->has('status')) {
            $statusMap = [
                'pending' => 'todo',
                'running' => 'in-progress',
                'paused' => 'blocked',
            ];
            $input['status'] = $statusMap[$request->input('status')] ?? $request->input('status');
        }

        if ($request->has('priority') && is_string($request->input('priority'))) {
            $priorityMap = ['low' => 2, 'medium' => 5, 'high' => 8];
            if (isset($priorityMap[$request->input('priority')])) {
                $input['priority'] = $priorityMap[$request->input('priority')];
            }
        }

        try {
            $updatedTask = $this->taskManagementService->update($task, $input, $request->user()?->id);

            return response()->json([
                'data' => $updatedTask,
                'message' => 'Task updated successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            $this->logService->error('Failed to update task in store', [
                'channel' => 'task',
                'type' => 'update_error',
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(AgentTask $task)
    {
        $taskId = $task->id;
        $this->queue->cancel($task);
        $task->delete();

        $this->logService->info('Task deleted', [
            'channel' => 'task',
            'type' => 'delete',
            'related_id' => $taskId,
            'related_type' => 'App\Models\AgentTask',
            'user_id' => request()->user()?->id,
        ]);

        return response()->json(['message' => 'Task deleted successfully']);
    }

    public function cancel(AgentTask $task)
    {
        $task = $this->queue->cancel($task);

        $this->logService->warning('Task cancelled', [
            'channel' => 'task',
            'type' => 'cancel',
            'related_id' => $task->id,
            'related_type' => 'App\Models\AgentTask',
            'user_id' => request()->user()?->id,
        ]);

        return response()->json(['data' => $task, 'message' => 'Task cancelled']);
    }

    public function pause(AgentTask $task)
    {
        $this->queue->pause($task);

        $this->logService->info('Task paused', [
            'channel' => 'task',
            'type' => 'pause',
            'related_id' => $task->id,
            'related_type' => 'App\Models\AgentTask',
            'user_id' => request()->user()?->id,
        ]);

        return response()->json(['data' => $task, 'message' => 'Task paused']);
    }

    public function resume(AgentTask $task)
    {
        $this->queue->resume($task);

        $this->logService->info('Task resumed', [
            'channel' => 'task',
            'type' => 'resume',
            'related_id' => $task->id,
            'related_type' => 'App\Models\AgentTask',
            'user_id' => request()->user()?->id,
        ]);

        return response()->json(['data' => $task, 'message' => 'Task resumed']);
    }

    public function getStats(Request $request)
    {
        $query = AgentTask::query();

        if ($request->has('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->has('workflow_id')) {
            $query->where('workflow_id', $request->workflow_id);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'todo')->count(),
            'running' => (clone $query)->where('status', 'in-progress')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'paused' => (clone $query)->where('status', 'blocked')->count(),
            'todo' => (clone $query)->where('status', 'todo')->count(),
            'in_progress' => (clone $query)->where('status', 'in-progress')->count(),
            'blocked' => (clone $query)->where('status', 'blocked')->count(),
            'queue_stats' => $this->queue->getStats(),
        ];

        return response()->json(['data' => $stats]);
    }

    public function getStatsByType(Request $request)
    {
        $counts = AgentTask::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        return response()->json(['data' => $counts]);
    }

    public function getExecutionTimeline(Request $request)
    {
        $days = collect(range(6, 0))->map(function ($i) {
            return now()->subDays($i)->format('Y-m-d');
        });

        $labels = $days->map(fn ($d) => Carbon::parse($d)->format('D'))->toArray();

        $completedData = [];
        $failedData = [];

        foreach ($days as $day) {
            $completedData[] = AgentTask::where('status', 'completed')
                ->whereDate('updated_at', $day)
                ->count();
            $failedData[] = AgentTask::where('status', 'failed')
                ->whereDate('updated_at', $day)
                ->count();
        }

        return response()->json([
            'data' => [
                'labels' => $labels,
                'completed' => $completedData,
                'failed' => $failedData,
            ],
        ]);
    }

    public function getAgentPerformance(Request $request)
    {
        $agents = Agent::take(5)->get();
        $labels = [];
        $data = [];

        if ($agents->isEmpty()) {
            $labels = ['AutoAgent', 'SpecAgent', 'ReflectAgent', 'Supervisor'];
            $data = [100, 100, 100, 100];
        } else {
            foreach ($agents as $agent) {
                $total = AgentTask::where('agent_id', $agent->id)->count();
                $completed = AgentTask::where('agent_id', $agent->id)->where('status', 'completed')->count();
                $rate = $total > 0 ? round(($completed / $total) * 100) : 100;

                $labels[] = $agent->name ?? "Agent #{$agent->id}";
                $data[] = $rate;
            }
        }

        return response()->json([
            'data' => [
                'labels' => $labels,
                'data' => $data,
            ],
        ]);
    }

    public function getActive(Request $request)
    {
        $activeTasks = AgentTask::with(['agent', 'workflow'])
            ->whereIn('status', ['todo', 'in-progress'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['data' => $activeTasks]);
    }

    public function getQueueStats()
    {
        return response()->json(['data' => $this->queue->getStats()]);
    }

    public function getRoutingStats()
    {
        return response()->json(['data' => $this->router->getStats()]);
    }

    /**
     * Manually force execution of a task
     */
    public function execute(Request $request, AgentTask $task)
    {
        try {
            $this->taskExecutionService->execute($task);

            return response()->json([
                'data' => $task->refresh(),
                'message' => 'Task execution initiated',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            $this->logService->error('Error executing task', [
                'channel' => 'task',
                'type' => 'execute_error',
                'related_id' => $task->id,
                'related_type' => 'App\Models\AgentTask',
                'context' => ['error' => $e->getMessage()],
            ]);

            return response()->json([
                'error' => 'Failed to execute task',
            ], 500);
        }
    }

    /**
     * Get execution logs for a task
     */
    public function logs(Request $request, AgentTask $task)
    {
        $limit = $request->query('limit', 100);
        $logs = $this->taskLogService->getLogs($task->id, $limit);

        return response()->json([
            'data' => $logs,
        ]);
    }

    /**
     * Update task status via state machine
     */
    public function updateStatus(Request $request, AgentTask $task)
    {
        $input = $request->all();

        // Normalize status
        if (isset($input['status'])) {
            $statusMap = [
                'pending' => 'todo',
                'running' => 'in-progress',
                'paused' => 'blocked',
            ];
            $input['status'] = $statusMap[$input['status']] ?? $input['status'];
        }

        $validator = Validator::make($input, [
            'status' => 'required|in:todo,in-progress,blocked,completed,failed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newStatus = $validator->validated()['status'];

        try {
            // Validate the transition
            $this->taskManagementService->validateStatusTransition($task->status, $newStatus);

            // Update the task
            $task->update(['status' => $newStatus]);

            $this->logService->info('Task status updated via state machine', [
                'channel' => 'task',
                'type' => 'status_update',
                'related_id' => $task->id,
                'related_type' => 'App\Models\AgentTask',
                'user_id' => $request->user()?->id,
                'context' => [
                    'from_status' => $task->getOriginal('status'),
                    'to_status' => $newStatus,
                ],
            ]);

            // Phase 4: Trigger Notifications for completed/failed tasks
            if (in_array($newStatus, ['failed', 'completed'])) {
                $type = $newStatus === 'failed' ? 'error' : 'success';
                $title = $newStatus === 'failed' ? 'Task Failed' : 'Task Completed';
                $priority = $newStatus === 'failed' ? 'high' : 'normal';

                // 1. Create HedraSoul Notification (Persistent DB)
                app(HedraSoulNotificationService::class)->create(
                    type: $type,
                    priority: $priority,
                    title: $title,
                    body: "Task #{$task->id} '{$task->title}' has been {$newStatus}.",
                    relatedId: $task->id,
                    relatedType: 'App\Models\AgentTask',
                    actionButtons: [['label' => 'View Task', 'url' => url('/hub/tasks/'.$task->id)]]
                );

                // 2. Broadcast via NotificationBroadcasted for Web & FCM
                NotificationBroadcasted::dispatch(
                    $request->user()?->id ?? 1,
                    [
                        'title' => $title,
                        'body' => "Task #{$task->id} '{$task->title}' has been {$newStatus}.",
                        'actions' => [['label' => 'View Task', 'url' => url('/hub/tasks/'.$task->id)]],
                        'data' => ['click_action' => url('/hub/tasks/'.$task->id)],
                    ],
                    $type
                );
            }

            return response()->json([
                'data' => $task->refresh(),
                'message' => 'Task status updated successfully',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            $this->logService->error('Error updating task status', [
                'channel' => 'task',
                'type' => 'status_update_error',
                'related_id' => $task->id,
                'related_type' => 'App\Models\AgentTask',
                'context' => ['error' => $e->getMessage()],
            ]);

            return response()->json([
                'error' => 'Failed to update task status',
            ], 500);
        }
    }

    /**
     * Create a manual task
     */
    public function createManual(Request $request)
    {
        try {
            $data = $request->all();
            $data['type'] = 'manual';

            $task = $this->taskManagementService->create($data, $request->user()?->id);

            return response()->json([
                'data' => $task,
                'message' => 'Manual task created successfully',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            $this->logService->error('Error creating manual task', [
                'channel' => 'task',
                'type' => 'create_manual_error',
                'context' => ['error' => $e->getMessage()],
            ]);

            return response()->json([
                'error' => 'Failed to create manual task',
            ], 500);
        }
    }

    /**
     * Create an agentic task (auto-execute)
     */
    public function createAgent(Request $request)
    {
        try {
            $data = $request->all();
            $data['type'] = 'agent';

            $task = $this->taskManagementService->create($data, $request->user()?->id);

            if ($task->target_agent_id === 'ertugrul_browser_agent' || Str::contains($task->target_agent_id ?? '', 'browser')) {
                ProcessReActStep::dispatch($task, []);
                TaskDispatchedToBrowserAgent::dispatch($task);
            } else {
                // Agent tasks are queued for execution automatically
                $this->taskExecutionService->execute($task);
            }

            return response()->json([
                'data' => $task,
                'message' => 'Agentic task created and queued for execution',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            $this->logService->error('Error creating agentic task', [
                'channel' => 'task',
                'type' => 'create_agent_error',
                'context' => ['error' => $e->getMessage()],
            ]);

            return response()->json([
                'error' => 'Failed to create agentic task',
            ], 500);
        }
    }

    /**
     * Create a system task (auto-execute)
     */
    public function createSystem(Request $request)
    {
        try {
            $data = $request->all();
            $data['type'] = 'system';

            $task = $this->taskManagementService->create($data, $request->user()?->id);

            // System tasks start execution immediately
            $this->taskExecutionService->executeNow($task);

            return response()->json([
                'data' => $task,
                'message' => 'System task created and executed',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            $this->logService->error('Error creating system task', [
                'channel' => 'task',
                'type' => 'create_system_error',
                'context' => ['error' => $e->getMessage()],
            ]);

            return response()->json([
                'error' => 'Failed to create system task',
            ], 500);
        }
    }

    /**
     * Get tasks by type
     */
    public function getByType(string $type)
    {
        // Validate task type
        if (! in_array($type, ['manual', 'agent', 'system'], true)) {
            return response()->json([
                'error' => 'Invalid task type',
            ], 422);
        }

        $tasks = $this->taskManagementService->getByType($type);

        return response()->json([
            'data' => $tasks,
        ]);
    }

    /**
     * Add a subtask to an AgentTask
     */
    public function addSubtask(Request $request, AgentTask $task)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $metadata = $task->metadata ?? [];
        $subtasks = $metadata['subtasks'] ?? [];

        $newSubtask = [
            'id' => (string) Str::uuid(),
            'title' => $request->title,
            'completed' => false,
            'created_at' => now()->toDateTimeString(),
        ];

        $subtasks[] = $newSubtask;
        $metadata['subtasks'] = $subtasks;

        $task->update(['metadata' => $metadata]);

        return response()->json([
            'data' => $task->refresh(),
            'subtask' => $newSubtask,
            'message' => 'Subtask added successfully',
        ]);
    }

    /**
     * Toggle a subtask completion status
     */
    public function toggleSubtask(Request $request, AgentTask $task, string $subtaskId)
    {
        $metadata = $task->metadata ?? [];
        $subtasks = $metadata['subtasks'] ?? [];

        foreach ($subtasks as &$st) {
            if (($st['id'] ?? null) === $subtaskId) {
                $st['completed'] = ! ($st['completed'] ?? false);
                break;
            }
        }

        $metadata['subtasks'] = $subtasks;
        $task->update(['metadata' => $metadata]);

        return response()->json([
            'data' => $task->refresh(),
            'message' => 'Subtask toggled successfully',
        ]);
    }

    /**
     * Delete a subtask from an AgentTask
     */
    public function deleteSubtask(Request $request, AgentTask $task, string $subtaskId)
    {
        $metadata = $task->metadata ?? [];
        $subtasks = $metadata['subtasks'] ?? [];

        $subtasks = array_values(array_filter($subtasks, fn ($st) => ($st['id'] ?? null) !== $subtaskId));

        $metadata['subtasks'] = $subtasks;
        $task->update(['metadata' => $metadata]);

        return response()->json([
            'data' => $task->refresh(),
            'message' => 'Subtask deleted successfully',
        ]);
    }

    /**
     * Get pending tasks for a target browser agent (e.g. ertugrul_browser_agent)
     *
     * GET /api/v1/agent-tasks/pending
     */
    public function getPendingBrowserTasks(Request $request)
    {
        $targetAgentId = $request->query('target_agent_id', 'ertugrul_browser_agent');

        $staleThreshold = now()->subMinutes(5);

        $tasks = AgentTask::query()
            ->where('target_agent_id', $targetAgentId)
            ->where(function ($query) use ($staleThreshold) {
                $query->whereIn('status', [AgentTask::STATUS_TODO, 'pending'])
                    ->orWhere(function ($q) use ($staleThreshold) {
                        $q->whereIn('status', [AgentTask::STATUS_IN_PROGRESS, 'in-progress', 'in_progress'])
                            ->where('updated_at', '<', $staleThreshold);
                    });
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'target_agent_id' => $targetAgentId,
            'count' => $tasks->count(),
            'data' => $tasks,
        ]);
    }

    /**
     * Update task status with execution proof (screenshots, logs, dom results)
     *
     * POST /api/v1/agent-tasks/{task}/status
     */
    public function updateStatusWithProof(Request $request, AgentTask $task)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:todo,in-progress,in_progress,blocked,completed,failed,cancelled',
            'execution_proof' => 'nullable|array',
            'result_data' => 'nullable|array',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        $statusMap = [
            'in_progress' => AgentTask::STATUS_IN_PROGRESS,
            'pending' => AgentTask::STATUS_TODO,
        ];

        $newStatus = $statusMap[$validated['status']] ?? $validated['status'];

        $updateData = ['status' => $newStatus];

        if (isset($validated['execution_proof'])) {
            $updateData['execution_proof'] = $validated['execution_proof'];
        }

        if (isset($validated['result_data'])) {
            $updateData['result_data'] = $validated['result_data'];
        }

        if (isset($validated['progress'])) {
            $updateData['progress'] = $validated['progress'];
        }

        if ($newStatus === AgentTask::STATUS_COMPLETED) {
            $updateData['progress'] = 100;
        }

        if ($newStatus === AgentTask::STATUS_COMPLETED && ($task->target_agent_id === 'ertugrul_browser_agent' || Str::contains($task->target_agent_id ?? '', 'browser'))) {
            // Prevent extension from overwriting the ReAct memory steps
            unset($updateData['execution_proof']);
            // Re-set to in-progress while LLM thinks, to prevent duplicate browser pickups
            $updateData['status'] = AgentTask::STATUS_IN_PROGRESS;

            $task->update($updateData);

            $observation = $validated['execution_proof']['action_result'] ?? $validated['execution_proof'] ?? [];
            ProcessReActStep::dispatch($task, $observation);
        } else {
            $task->update($updateData);
        }

        $this->logService->info('Browser Task status updated with proof', [
            'channel' => 'task',
            'type' => 'browser_task_status_update',
            'related_id' => $task->id,
            'context' => [
                'status' => $newStatus,
                'proof' => $validated['execution_proof'] ?? null,
            ],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Task status updated with execution proof',
            'data' => $task->refresh(),
        ]);
    }
}
