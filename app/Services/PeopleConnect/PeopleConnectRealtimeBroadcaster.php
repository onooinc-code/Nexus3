<?php

namespace App\Services\PeopleConnect;

use App\Events\PeopleConnect\AutopilotBlocked;
use App\Events\PeopleConnect\MessageAnalyzed;
use App\Events\PeopleConnect\MessageDelivered;
use App\Events\PeopleConnect\MessageFailed;
use App\Events\PeopleConnect\MessageReceived;
use App\Events\PeopleConnect\ReplyDraftCreated;
use App\Events\PeopleConnect\SessionClosed;
use App\Events\PeopleConnect\SessionOpened;
use App\Models\PeopleConnect\PeopleConnectMessage;
use App\Models\PeopleConnect\PeopleConnectMessageAnalysis;
use App\Models\PeopleConnect\PeopleConnectReplyDraft;
use App\Models\PeopleConnect\PeopleConnectSession;
use Illuminate\Support\Facades\Log;
use Throwable;

class PeopleConnectRealtimeBroadcaster
{
    public function messageReceived(PeopleConnectMessage $message): void
    {
        $this->safeBroadcast(new MessageReceived($message), 'MessageReceived');
    }

    public function messageAnalyzed(PeopleConnectMessage $message, PeopleConnectMessageAnalysis $analysis): void
    {
        $this->safeBroadcast(new MessageAnalyzed($message, $analysis), 'MessageAnalyzed');
    }

    public function messageDelivered(PeopleConnectMessage $message): void
    {
        $this->safeBroadcast(new MessageDelivered($message), 'MessageDelivered');
    }

    public function messageFailed(PeopleConnectMessage $message, string $reason = ''): void
    {
        $this->safeBroadcast(new MessageFailed($message, $reason), 'MessageFailed');
    }

    public function sessionOpened(PeopleConnectSession $session): void
    {
        $this->safeBroadcast(new SessionOpened($session), 'SessionOpened');
    }

    public function sessionClosed(PeopleConnectSession $session): void
    {
        $this->safeBroadcast(new SessionClosed($session), 'SessionClosed');
    }

    public function replyDraftCreated(PeopleConnectReplyDraft $draft): void
    {
        $this->safeBroadcast(new ReplyDraftCreated($draft), 'ReplyDraftCreated');
    }

    public function autopilotBlocked(int $conversationId, string $reason): void
    {
        $this->safeBroadcast(new AutopilotBlocked($conversationId, $reason), 'AutopilotBlocked');
    }

    protected function safeBroadcast(object $event, string $eventName): void
    {
        try {
            event($event);
        } catch (Throwable $e) {
            Log::warning("PeopleConnect realtime broadcast failed for [{$eventName}]: {$e->getMessage()}");
        }
    }
}
