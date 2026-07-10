<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettingsActivityLogged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The type of activity (created, updated, deleted, bulk_updated, etc.)
     */
    public string $actionType;

    /**
     * Context data related to the setting activity.
     */
    public array $context;

    /**
     * Optional message explaining the activity.
     */
    public ?string $message;

    /**
     * Timestamp of the activity.
     */
    public string $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(string $actionType, array $context = [], ?string $message = null)
    {
        $this->actionType = $actionType;
        $this->context = $context;
        $this->message = $message;
        $this->timestamp = now()->toIso8601String();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        // Broadcast on a private channel for security, or public if appropriate for the dashboard
        return new Channel('settings.activity');
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'action' => $this->actionType,
            'context' => $this->context,
            'message' => $this->message,
            'timestamp' => $this->timestamp,
        ];
    }
}
