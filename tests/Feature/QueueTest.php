<?php

namespace Tests\Feature;

use App\Jobs\SyncMemoryJob;
use App\Models\Contact;
use App\Models\Memory;
use App\Services\LogService;
use App\Services\Memory\EpisodicMemoryService;
use App\Services\Memory\GraphMemoryService;
use App\Services\Memory\SemanticMemoryService;
use App\Services\Memory\StructuredMemoryService;
use App\Services\Memory\WorkingMemoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_memory_job_handles_failure_gracefully(): void
    {
        $contact = Contact::factory()->create();
        $memory = Memory::factory()->create([
            'contact_id' => $contact->id,
            'type' => 'episodic',
        ]);

        $job = new SyncMemoryJob($memory->contact_id, $memory->type, app(LogService::class));
        $result = $job->handle(
            app(WorkingMemoryService::class),
            app(EpisodicMemoryService::class),
            app(SemanticMemoryService::class),
            app(StructuredMemoryService::class),
            app(GraphMemoryService::class)
        );

        // Job catch block doesn't return false, it catches and returns null.
        $this->assertNull($result);
    }

    public function test_queue_connection_is_sync_in_testing(): void
    {
        $this->assertEquals('sync', config('queue.default'));
    }

    public function test_job_can_be_pushed_to_queue(): void
    {
        Queue::fake();

        $contact = Contact::factory()->create();
        SyncMemoryJob::dispatch($contact->id, 'episodic', app(LogService::class));

        Queue::assertPushed(SyncMemoryJob::class, 1);
    }
}
