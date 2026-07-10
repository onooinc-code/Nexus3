<?php

namespace App\Jobs;

use App\Events\ContactIdentityConflictDetected;
use App\Models\ContactAuditEvent;
use App\Models\ContactIdentifier;
use App\Models\ContactImportBatch;
use Throwable;

class ResolveContactImportIdentitiesJob extends BaseJob
{
    public $queue = 'contacts';

    public function __construct(
        public ContactImportBatch $batch
    ) {}

    public function handle(): void
    {
        $contact = $this->batch->contact;
        if (! $contact) {
            return;
        }

        $identifiers = $contact->identifiers()->pluck('value')->toArray();
        if (! empty($identifiers)) {
            $conflicts = ContactIdentifier::whereIn('value', $identifiers)
                ->where('contact_id', '!=', $contact->id)
                ->pluck('contact_id')
                ->unique()
                ->toArray();

            if (! empty($conflicts)) {
                event(new ContactIdentityConflictDetected($contact, $conflicts));
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        ContactAuditEvent::create([
            'contact_id' => $this->batch->contact_id,
            'action' => 'resolve_identities_failed',
            'description' => "Resolve identities for batch {$this->batch->id} failed: ".$exception->getMessage(),
        ]);
    }
}
