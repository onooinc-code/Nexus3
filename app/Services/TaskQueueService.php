<?php

namespace App\Services;

use App\Models\AgentTask;
use Illuminate\Support\Facades\Redis;

class TaskQueueService
{
    protected LogService $logService;

    // Redis keys
    const KEY_QUEUED = 'nexus:tasks:queued';

    const KEY_PROCESSING = 'nexus:tasks:processing';

    const KEY_COMPLETED = 'nexus:tasks:completed';

    const KEY_FAILED = 'nexus:tasks:failed';

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function enqueue(AgentTask $task, array $options = []): AgentTask
    {
        $id = $task->id;
        $task->update([
            'status' => 'pending', // or 'todo' based on mapping
            'metadata' => array_merge($task->metadata ?? [], [
                'queued_at' => now()->toISOString(),
                'queue_options' => $options,
            ]),
        ]);

        $fresh = AgentTask::find($id);

        // Add to Redis list (Right push)
        Redis::rpush(self::KEY_QUEUED, $fresh->id);

        $this->logService->info('Task enqueued to Redis', [
            'channel' => 'task',
            'type' => 'queue',
            'related_id' => $fresh->id,
            'related_type' => 'App\Models\AgentTask',
            'context' => ['title' => $fresh->title],
        ]);

        return $fresh;
    }

    public function dequeue(): ?AgentTask
    {
        // Atomically pop from queued and push to processing
        // LPOPRPUSH is deprecated in newer Redis, RPOPLPUSH or similar. In Laravel we can use transaction or simpler approach.
        // For simplicity, we'll use LPOP and then SADD/RPUSH.
        $taskId = Redis::lpop(self::KEY_QUEUED);

        if (! $taskId) {
            return null;
        }

        Redis::sadd(self::KEY_PROCESSING, $taskId);

        $task = AgentTask::find($taskId);

        if ($task) {
            $task->update(['status' => 'running']);

            $this->logService->info('Task dequeued from Redis', [
                'channel' => 'task',
                'type' => 'dequeue',
                'related_id' => $task->id,
                'related_type' => 'App\Models\AgentTask',
                'context' => ['title' => $task->title],
            ]);
        }

        return $task;
    }

    public function complete(AgentTask $task, array $result = []): AgentTask
    {
        $task->update([
            'status' => 'completed',
            'progress' => 100,
            'metadata' => array_merge($task->metadata ?? [], [
                'completed_at' => now()->toISOString(),
                'result' => $result,
            ]),
        ]);

        Redis::srem(self::KEY_PROCESSING, $task->id);
        Redis::sadd(self::KEY_COMPLETED, $task->id);

        $this->logService->info('Task completed in Redis', [
            'channel' => 'task',
            'type' => 'complete',
            'related_id' => $task->id,
            'related_type' => 'App\Models\AgentTask',
            'context' => ['title' => $task->title, 'result' => $result],
        ]);

        return $task;
    }

    public function fail(AgentTask $task, ?string $error = null): AgentTask
    {
        $task->update([
            'status' => 'failed',
            'metadata' => array_merge($task->metadata ?? [], [
                'failed_at' => now()->toISOString(),
                'error' => $error,
            ]),
        ]);

        Redis::srem(self::KEY_PROCESSING, $task->id);
        Redis::sadd(self::KEY_FAILED, $task->id);

        $this->logService->error('Task failed in Redis', [
            'channel' => 'task',
            'type' => 'fail',
            'related_id' => $task->id,
            'related_type' => 'App\Models\AgentTask',
            'context' => ['title' => $task->title, 'error' => $error],
        ]);

        return $task;
    }

    public function cancel(AgentTask $task): AgentTask
    {
        $id = $task->id;
        $task->update(['status' => 'cancelled']);

        Redis::lrem(self::KEY_QUEUED, 0, $id);
        Redis::srem(self::KEY_PROCESSING, $id);

        $fresh = AgentTask::find($id);

        if ($fresh) {
            $this->logService->info('Task cancelled in Redis', [
                'channel' => 'task',
                'type' => 'cancel',
                'related_id' => $fresh->id,
                'related_type' => 'App\Models\AgentTask',
                'context' => ['title' => $fresh->title],
            ]);

            return $fresh;
        }

        return $task;
    }

    public function pause(AgentTask $task): AgentTask
    {
        $task->update(['status' => 'paused']); // or blocked

        $this->logService->info('Task paused', [
            'channel' => 'task',
            'type' => 'pause',
            'related_id' => $task->id,
            'related_type' => 'App\Models\AgentTask',
            'context' => ['title' => $task->title],
        ]);

        return $task;
    }

    public function resume(AgentTask $task): AgentTask
    {
        $task->update(['status' => 'pending']);
        $this->enqueue($task);

        $this->logService->info('Task resumed', [
            'channel' => 'task',
            'type' => 'resume',
            'related_id' => $task->id,
            'related_type' => 'App\Models\AgentTask',
            'context' => ['title' => $task->title],
        ]);

        return $task;
    }

    public function getQueueSize(): int
    {
        return Redis::llen(self::KEY_QUEUED) ?? 0;
    }

    public function getProcessingSize(): int
    {
        return Redis::scard(self::KEY_PROCESSING) ?? 0;
    }

    public function getCompletedCount(): int
    {
        return Redis::scard(self::KEY_COMPLETED) ?? 0;
    }

    public function getFailedCount(): int
    {
        return Redis::scard(self::KEY_FAILED) ?? 0;
    }

    public function getStats(): array
    {
        $queued = $this->getQueueSize();
        $processing = $this->getProcessingSize();
        $completed = $this->getCompletedCount();
        $failed = $this->getFailedCount();

        return [
            'queued' => $queued,
            'processing' => $processing,
            'completed' => $completed,
            'failed' => $failed,
            'total' => $queued + $processing + $completed + $failed,
        ];
    }

    public function clearQueue(): void
    {
        Redis::del(self::KEY_QUEUED);
        $this->logService->info('Task queue cleared from Redis', ['channel' => 'task', 'type' => 'clear']);
    }

    public function clearCompleted(): void
    {
        Redis::del(self::KEY_COMPLETED);
        $this->logService->info('Completed tasks cleared from Redis', ['channel' => 'task', 'type' => 'clear']);
    }

    public function clearFailed(): void
    {
        Redis::del(self::KEY_FAILED);
        $this->logService->info('Failed tasks cleared from Redis', ['channel' => 'task', 'type' => 'clear']);
    }

    public function getQueuedTaskIds(): array
    {
        return Redis::lrange(self::KEY_QUEUED, 0, -1) ?: [];
    }

    public function getProcessingTaskIds(): array
    {
        return Redis::smembers(self::KEY_PROCESSING) ?: [];
    }
}
