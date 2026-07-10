<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class WorkflowCompleted extends Event implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $workflowId,
        public array $result,
        public array $metadata = [],
    ) {
        parent::__construct();
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("workflow.{$this->workflowId}");
    }

    public function broadcastAs(): string
    {
        return 'workflow.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->workflowId,
            'result' => $this->result,
            'metadata' => $this->metadata,
            'completed_at' => $this->timestamp->toDateTimeString(),
        ];
    }
}
