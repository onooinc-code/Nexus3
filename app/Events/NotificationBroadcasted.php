<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationBroadcasted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;

    public array $notification;

    public string $type;

    /**
     * Create a new event instance.
     */
    public function __construct(int $userId, array $notification, string $type = 'info')
    {
        $this->userId = $userId;
        $this->notification = $notification;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("notifications.{$this->userId}"),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => uniqid(),
            'type' => $this->type,
            'title' => $this->notification['title'] ?? 'Notification',
            'body' => $this->notification['body'] ?? '',
            'icon' => $this->notification['icon'] ?? null,
            'badge' => $this->notification['badge'] ?? null,
            'actions' => $this->notification['actions'] ?? [],
            'data' => $this->notification['data'] ?? [],
            'timestamp' => now()->toIso8601String(),
            'requireInteraction' => $this->notification['requireInteraction'] ?? false,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'notification.received';
    }
}
