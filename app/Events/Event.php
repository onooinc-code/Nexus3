<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Base Event class for all Nexus events
 */
abstract class Event
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Event metadata
     */
    public array $metadata = [];

    /**
     * Event timestamp
     *
     * @var Carbon
     */
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        $this->timestamp = now();
    }

    /**
     * Set event metadata
     *
     * @return $this
     */
    public function withMetadata(array $metadata): static
    {
        $this->metadata = array_merge($this->metadata, $metadata);

        return $this;
    }

    /**
     * Get event metadata
     */
    public function getMetadata(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->metadata;
        }

        return $this->metadata[$key] ?? $default;
    }
}
