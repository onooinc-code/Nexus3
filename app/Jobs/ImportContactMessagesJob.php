<?php

namespace App\Jobs;

use App\Events\ContactImportCompleted;
use App\Models\ContactAuditEvent;
use App\Models\ContactImportBatch;
use App\Services\Contact\ContactImportPipeline;
use Illuminate\Support\Facades\Redis;
use Throwable;

class ImportContactMessagesJob extends BaseJob
{
    public $queue = 'contacts';

    public function __construct(
        public ContactImportBatch $batch,
        public string $content,
        public string $format,
        public ?string $timezone = 'UTC'
    ) {}

    public function handle(ContactImportPipeline $pipeline): void
    {
        $result = $pipeline->commit($this->batch, $this->content, $this->format, $this->timezone);

        if ($result['success']) {
            event(new ContactImportCompleted($this->batch->contact, $result['created'], $this->batch->source, 'completed'));

            try {
                $redis = Redis::connection('cache');
                $prefix = config('cache.prefix');
                $keys = $redis->keys("*{$prefix}:contact_{$this->batch->contact_id}_messages_*");
                foreach ($keys as $key) {
                    $redis->del(str_replace($prefix.':', '', $key));
                }
            } catch (\Exception $e) {
                // Ignore
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        $this->batch->update([
            'status' => 'failed',
            'metadata' => array_merge($this->batch->metadata ?? [], ['error' => $exception->getMessage()]),
        ]);

        ContactAuditEvent::create([
            'contact_id' => $this->batch->contact_id,
            'action' => 'import_failed',
            'description' => "Import batch {$this->batch->id} failed: ".$exception->getMessage(),
        ]);
    }
}
