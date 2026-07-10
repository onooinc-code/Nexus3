<?php

namespace App\Events\PeopleConnect;

use App\Models\PeopleConnect\PeopleConnectMessage;
use App\Models\PeopleConnect\PeopleConnectMessageAnalysis;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageAnalyzed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PeopleConnectMessage $message,
        public PeopleConnectMessageAnalysis $analysis
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('peopleconnect.conversation.'.$this->message->conversation_id)];
    }

    public function broadcastAs(): string
    {
        return 'message.analyzed';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'intent' => $this->analysis->intent,
            'sentiment' => $this->analysis->sentiment,
            'emotional_tone' => $this->analysis->emotional_tone,
            'confidence_score' => $this->analysis->confidence_score,
        ];
    }
}
