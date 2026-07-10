<?php

namespace App\Events\PeopleConnect;

use App\Models\PeopleConnect\PeopleConnectReplyDraft;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReplyDraftCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public PeopleConnectReplyDraft $draft) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('peopleconnect.conversation.'.$this->draft->conversation_id)];
    }

    public function broadcastAs(): string
    {
        return 'reply.draft.created';
    }

    public function broadcastWith(): array
    {
        return [
            'draft_id' => $this->draft->id,
            'conversation_id' => $this->draft->conversation_id,
            'body' => $this->draft->body,
            'status' => $this->draft->status,
        ];
    }
}
