<?php

namespace App\Events\PeopleConnect;

use App\Models\PeopleConnect\PeopleConnectMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public PeopleConnectMessage $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('peopleconnect.conversation.'.$this->message->conversation_id),
            new PrivateChannel('peopleconnect.hub'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.received';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'contact_id' => $this->message->contact_id,
            'body' => $this->message->body,
            'direction' => $this->message->direction,
            'sender_type' => $this->message->sender_type,
            'status' => $this->message->status,
            'delivered_at' => $this->message->delivered_at?->toIso8601String(),
        ];
    }
}
