<?php

namespace App\Services;

use App\Models\AgentTask;
use App\Models\TaskLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskLogService
{
    public function log(AgentTask $task, string $level, string $message, array $context = []): void
    {
        Log::log($level, "[Task {$task->id}] {$message}", $context);
        $this->persistLog($task, $level, $message, $context);
    }

    protected function persistLog(AgentTask $task, string $level, string $message, array $context = []): void
    {
        try {
            TaskLog::create([
                'task_id' => $task->id,
                'level' => $level,
                'message' => $message,
                'context' => $context,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to persist task log: '.$e->getMessage());
        }
    }

    public function info(AgentTask $task, string $message, array $context = []): void
    {
        $this->log($task, 'info', $message, $context);
    }

    public function warning(AgentTask $task, string $message, array $context = []): void
    {
        $this->log($task, 'warning', $message, $context);
    }

    public function error(AgentTask $task, string $message, array $context = []): void
    {
        $this->log($task, 'error', $message, $context);
    }

    public function debug(AgentTask $task, string $message, array $context = []): void
    {
        $this->log($task, 'debug', $message, $context);
    }

    public function getLogs(int $taskId, int $limit = 100): array
    {
        return TaskLog::where('task_id', $taskId)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'task_id' => $log->task_id,
                    'level' => $log->level,
                    'message' => $log->message,
                    'context' => $log->context,
                    'time' => $log->created_at->toISOString(),
                    'timestamp' => $log->created_at->toIso8601String(),
                ];
            })
            ->toArray();
    }

    public function getLogsByLevel(string $level, int $limit = 100): array
    {
        return TaskLog::where('level', $level)
            ->latest()
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getRecentLogs(int $limit = 100): array
    {
        return TaskLog::latest()
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function clearLogs(?int $taskId = null): void
    {
        if ($taskId) {
            TaskLog::where('task_id', $taskId)->delete();
        } else {
            TaskLog::truncate();
        }

        Log::info('Task logs cleared', ['task_id' => $taskId]);
    }

    public function getStats(): array
    {
        $stats = TaskLog::select('level', DB::raw('count(*) as total'))
            ->groupBy('level')
            ->pluck('total', 'level')
            ->toArray();

        $levels = [
            'emergency' => 0,
            'alert' => 0,
            'critical' => 0,
            'error' => 0,
            'warning' => 0,
            'notice' => 0,
            'info' => 0,
            'debug' => 0,
        ];

        // Merge DB stats into default array
        foreach ($stats as $level => $count) {
            if (array_key_exists($level, $levels)) {
                $levels[$level] = $count;
            } else {
                $levels[$level] = $count; // Fallback for custom levels
            }
        }

        return [
            'total' => array_sum($levels),
            'by_level' => $levels,
            'max_capacity' => 'unlimited (database)',
        ];
    }
}
