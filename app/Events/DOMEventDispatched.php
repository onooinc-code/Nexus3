<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DOMEventDispatched implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $eventData;

    public function __construct(array $eventData)
    {
        $this->eventData = $eventData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('nexus-dom-events'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'dom.event_dispatched';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'event_name' => $this->eventData['event_name'] ?? 'mutation_observer',
            'dom_selector' => $this->eventData['dom_selector'] ?? null,
            'page_url' => $this->eventData['page_url'] ?? null,
            'payload' => $this->eventData['payload'] ?? [],
            'timestamp' => $this->eventData['timestamp'] ?? now()->toIso8601String(),
        ];
    }
}
