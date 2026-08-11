<?php

namespace App\Jobs;

use App\Exceptions\PeopleConnect\DuplicateMessageException;
use App\Jobs\PeopleConnect\AnalyzePeopleConnectMessageJob;
use App\Models\PeopleConnect\PeopleConnectRawProviderEvent;
use App\Services\PeopleConnect\FirestoreSyncService;
use App\Services\PeopleConnect\PeopleConnectContactResolver;
use App\Services\PeopleConnect\PeopleConnectConversationService;
use App\Services\PeopleConnect\PeopleConnectMessageService;
use App\Services\PeopleConnect\PeopleConnectRealtimeBroadcaster;
use App\Services\PeopleConnect\PeopleConnectSessionService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessWahaWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $payload;

    protected ?int $rawEventId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload, ?int $rawEventId = null)
    {
        $this->payload = $payload;
        $this->rawEventId = $rawEventId;
    }

    /**
     * Execute the job.
     */
    public function handle(
        PeopleConnectContactResolver $contactResolver,
        PeopleConnectConversationService $conversationService,
        PeopleConnectSessionService $sessionService,
        PeopleConnectMessageService $messageService,
        PeopleConnectRealtimeBroadcaster $broadcaster,
        FirestoreSyncService $firestoreSyncService
    ): void {
        $event = $this->payload['event'] ?? 'unknown';

        if ($event === 'session.status') {
            $firestoreSyncService->syncSession($this->payload['payload']);
            $this->markRawEventStatus('processed');

            return;
        }
        $isFromMe = (bool) ($this->payload['payload']['fromMe'] ?? false);
        $chatId = $this->payload['payload']['chatId'] ?? null;

        // Fallback for chatId if missing from WAHA payload (e.g., WEBJS engine)
        if (! $chatId) {
            $chatId = $isFromMe
                ? ($this->payload['payload']['to'] ?? null)
                : ($this->payload['payload']['from'] ?? null);
        }

        // Determine raw phone identifier
        $rawPhone = $isFromMe
            ? ($this->payload['payload']['to'] ?? $chatId)
            : ($this->payload['payload']['from'] ?? $chatId);

        $pushName = $this->payload['payload']['pushname']
            ?? $this->payload['payload']['_data']['notifyName']
            ?? '';
        $body = $this->payload['payload']['body'] ?? '';
        $timestamp = $this->payload['payload']['timestamp'] ?? time();
        $wahaMessageId = $this->payload['payload']['id'] ?? null;

        if (! $chatId || ! $rawPhone) {
            $this->markRawEventStatus('error');

            return;
        }

        // Strip WhatsApp suffixes (@c.us, @g.us, @lid, @broadcast) cleanly
        $phone = preg_replace('/@(c\.us|g\.us|lid|broadcast|s\.whatsapp\.net)$/i', '', (string) $rawPhone);
        $phone = trim($phone);
        if ($phone === '' && $chatId) {
            $phone = preg_replace('/@(c\.us|g\.us|lid|broadcast|s\.whatsapp\.net)$/i', '', (string) $chatId);
        }

        // 1. Resolve Contact
        $contact = $contactResolver->resolve($chatId, $phone, $pushName);

        // 2. Resolve Conversation
        $conversation = $conversationService->resolveOrCreate($contact->id, 'whatsapp', $chatId);

        // 3. Resolve Session
        $session = $sessionService->resolveOrOpen($conversation, Carbon::createFromTimestamp($timestamp));

        // 4. Insert Message
        try {
            $message = $messageService->insert([
                'conversation_id' => $conversation->id,
                'session_id' => $session->id,
                'contact_id' => $contact->id,
                'sender_type' => $isFromMe ? 'user' : 'contact',
                'direction' => $isFromMe ? 'outbound' : 'inbound',
                'body' => $body,
                'status' => 'delivered',
                'waha_message_id' => $wahaMessageId,
                'provider_payload_hash' => hash('sha256', json_encode($this->payload)),
                'delivered_at' => Carbon::createFromTimestamp($timestamp),
            ]);

            // Update conversation last message preview
            $conversation->update([
                'last_message_at' => Carbon::createFromTimestamp($timestamp),
                'last_message_preview' => mb_substr($body, 0, 100),
                'unread_count' => $conversation->unread_count + 1,
            ]);

            // Update session count
            $session->increment('message_count');

            // 5. Dispatch AnalyzePeopleConnectMessageJob
            AnalyzePeopleConnectMessageJob::dispatch($message);

            // 6. Push to Firestore
            $isFromMe = ($this->payload['payload']['fromMe'] ?? false);

            // Sync Conversation Overview
            $firestoreSyncService->syncConversationOverview($chatId, [
                'id' => $chatId,
                'name' => $contact->name ?? $phone,
                'picture' => $contact->avatar_url,
                'unreadCount' => $conversation->unread_count,
                'lastMessage' => [
                    'body' => mb_substr($body, 0, 100),
                    'timestamp' => $timestamp * 1000,
                    'fromMe' => $isFromMe,
                ],
                'timestamp' => $timestamp * 1000,
            ]);

            // Sync Message
            $firestoreSyncService->syncMessage($chatId, $wahaMessageId, [
                'id' => $wahaMessageId,
                'timestamp' => $timestamp * 1000,
                'body' => $body,
                'fromMe' => $isFromMe,
                'hasMedia' => isset($this->payload['payload']['hasMedia']) ? $this->payload['payload']['hasMedia'] : false,
                'type' => $this->payload['payload']['type'] ?? 'chat',
                'ack' => $this->payload['payload']['ack'] ?? 1,
            ]);

            // 6. Realtime Broadcasting
            $broadcaster->messageReceived($message);

            $this->markRawEventStatus('processed');

        } catch (DuplicateMessageException $e) {
            $this->markRawEventStatus('processed');
        } catch (Throwable $e) {
            $this->markRawEventStatus('error');
            throw $e;
        }
    }

    private function markRawEventStatus(string $status): void
    {
        if ($this->rawEventId) {
            PeopleConnectRawProviderEvent::where('id', $this->rawEventId)
                ->update([
                    'processed_at' => now(),
                    'processing_status' => $status,
                ]);
        }
    }

    public function failed(Throwable $exception)
    {
        $this->markRawEventStatus('error');
    }
}
