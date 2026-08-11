<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessMessageReceived extends Listener implements ShouldQueue
{
    public bool $shouldQueue = true;

    public string $queue = 'default';

    public function handle(object $event): void
    {
        try {
            $conversationId = property_exists($event, 'message') && $event->message
                ? $event->message->conversation_id
                : ($event->conversationId ?? 'unknown');

            $this->log("Processing message received for conversation {$conversationId}");
            // Real-time chat messages are handled through broadcast events.
            // Additional memory extraction or indexing is now managed by background jobs.
        } catch (\Exception $e) {
            $this->log('Error processing message: '.$e->getMessage(), 'error');
            throw $e;
        }
    }
}
