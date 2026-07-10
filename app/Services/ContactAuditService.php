<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\ContactAuditEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ContactAuditService
{
    /**
     * Log a contact-related audit event.
     */
    public function logEvent(
        Contact $contact,
        string $action,
        ?array $before = null,
        ?array $after = null,
        ?string $traceId = null
    ): ContactAuditEvent {
        $actor = Auth::user();

        return ContactAuditEvent::create([
            'contact_id' => $contact->id,
            'actor_type' => $actor ? get_class($actor) : 'system',
            'actor_id' => $actor ? $actor->id : null,
            'action' => $action,
            'before_state' => $before,
            'after_state' => $after,
            'trace_id' => $traceId ?: Request::header('X-Trace-Id'),
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Retrieve audit events for a contact.
     *
     * @return Collection
     */
    public function getEvents(Contact $contact, int $limit = 50)
    {
        return ContactAuditEvent::where('contact_id', $contact->id)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
