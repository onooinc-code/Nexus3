<?php

namespace App\Events;

use App\Models\AgentTask;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a task fails
 */
class TaskFailedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AgentTask $task;

    public string $error;

    /**
     * Create a new event instance.
     */
    public function __construct(AgentTask $task, string $error = '')
    {
        $this->task = $task;
        $this->error = $error;
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
        return 'task.failed';
    }
}
