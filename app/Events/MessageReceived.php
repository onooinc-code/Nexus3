<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MessageReceived extends Event implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $conversationId,
        public string $messageId,
        public string $agentId,
        public array $responseData = [],
    ) {
        parent::__construct();
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("conversation.{$this->conversationId}");
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->messageId,
            'agent_id' => $this->agentId,
            'data' => $this->responseData,
            'timestamp' => $this->timestamp->toDateTimeString(),
        ];
    }
}
