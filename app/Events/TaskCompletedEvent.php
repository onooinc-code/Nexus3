<?php

namespace App\Events;

use App\Models\AgentTask;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a task is completed successfully
 */
class TaskCompletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AgentTask $task;

    /**
     * Create a new event instance.
     */
    public function __construct(AgentTask $task)
    {
        $this->task = $task;
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
        return 'task.completed';
    }
}
