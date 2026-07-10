<?php

namespace App\Events;

use App\Models\AgentTask;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a task status changes
 */
class TaskStatusChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AgentTask $task;

    public string $oldStatus;

    public string $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(AgentTask $task, string $oldStatus, string $newStatus)
    {
        $this->task = $task;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('task.'.$this->task->id),
            new Channel('tasks'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'task.status_changed';
    }
}
