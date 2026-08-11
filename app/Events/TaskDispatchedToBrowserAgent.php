<?php

namespace App\Events;

use App\Models\AgentTask;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskDispatchedToBrowserAgent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AgentTask $task;

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
            new Channel('nexus-browser-agent'),
            new Channel('nexus-agent-tasks.'.$this->task->target_agent_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'task.dispatched_to_browser_agent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->task->id,
            'title' => $this->task->title,
            'description' => $this->task->description,
            'status' => $this->task->status,
            'type' => $this->task->type,
            'origin_agent_id' => $this->task->origin_agent_id,
            'target_agent_id' => $this->task->target_agent_id,
            'task_type' => $this->task->task_type,
            'dynamic_system_instruction' => $this->task->dynamic_system_instruction,
            'dom_event_trigger' => $this->task->dom_event_trigger,
            'payload_data' => $this->task->payload_data,
            'created_at' => $this->task->created_at?->toIso8601String(),
        ];
    }
}
