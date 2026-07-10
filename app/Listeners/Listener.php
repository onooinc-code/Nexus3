<?php

namespace App\Listeners;

use App\Services\LogService;

/**
 * Base Listener class for event handling
 */
abstract class Listener
{
    /**
     * Determine if the listener should be queued
     */
    public bool $shouldQueue = false;

    /**
     * The name of the queue connection to use
     */
    public ?string $connection = 'redis';

    /**
     * The name of the queue to use
     */
    public string $queue = 'default';

    /**
     * The number of seconds the job can run before timing out
     */
    public int $timeout = 0;

    /**
     * The number of times the queued listener may be attempted
     */
    public int $tries = 1;

    /**
     * The log service instance.
     */
    protected LogService $logService;

    /**
     * Create the event listener.
     */
    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    /**
     * Get the name of the listener
     */
    public function getName(): string
    {
        return class_basename(static::class);
    }

    /**
     * Log a message
     */
    protected function log(string $message, string $level = 'info'): void
    {
        $this->logService->log($level, "[{$this->getName()}] ".$message);
    }

    /**
     * Dispatch another event
     */
    protected function dispatchEvent(object $event): mixed
    {
        return event($event);
    }

    /**
     * Handle failure
     */
    public function failed(\Exception $exception): void
    {
        $this->log('Failed: '.$exception->getMessage(), 'error');
    }
}
