<?php

namespace App\Services\PeopleConnect;

use App\Models\Contact;
use App\Services\SettingCacheService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendContactMessageAction
{
    public function __construct(
        protected PeopleConnectConversationService $conversationService,
        protected PeopleConnectSessionService $sessionService,
        protected PeopleConnectMessageService $messageService,
        protected PeopleConnectRealtimeBroadcaster $broadcaster,
        protected FirestoreSyncService $firestoreSyncService,
        protected PeopleConnectContactResolver $contactResolver
    ) {}

    public function execute(array $validated): array
    {
        $chatId = null;
        $contact = null;

        if (! empty($validated['waha_chat_id'])) {
            $chatId = $validated['waha_chat_id'];
            if (! str_ends_with($chatId, '@c.us') && ! str_ends_with($chatId, '@g.us') && ! str_ends_with($chatId, '@lid')) {
                $chatId .= '@c.us';
            }
            $phone = preg_replace('/@(c\.us|g\.us|lid|broadcast|s\.whatsapp\.net)$/i', '', $chatId);
            $contact = $this->contactResolver->resolve($chatId, $phone, $phone);
        } elseif (! empty($validated['contact_id'])) {
            $contact = Contact::findOrFail($validated['contact_id']);
            $phone = $contact->phone;
            if (empty($phone)) {
                throw new \InvalidArgumentException('Contact does not have a valid phone number.');
            }
            $chatId = $phone;
            if (! str_ends_with($chatId, '@c.us') && ! str_ends_with($chatId, '@g.us') && ! str_ends_with($chatId, '@lid')) {
                $chatId .= '@c.us';
            }
        } else {
            throw new \InvalidArgumentException('Either waha_chat_id or contact_id is required.');
        }

        // Call WAHA API with dynamic SettingCacheService integration & graceful fallback
        $settings = app(SettingCacheService::class);
        $wahaUrl = rtrim((string) $settings->get('waha_url', config('waha.api_url', config('services.waha.api_url', 'http://localhost:3000'))), '/');
        $wahaSession = (string) $settings->get('waha_session', config('waha.default_session', config('services.waha.session', 'default')));
        $wahaKey = (string) $settings->get('waha_api_key', config('waha.api_key', config('services.waha.api_key', '')));

        $headers = [
            'X-Api-Key' => $wahaKey,
            'Authorization' => 'Bearer '.$wahaKey,
            'Accept' => 'application/json',
        ];

        $status = 'delivered';
        try {
            $response = Http::timeout(5)->withHeaders($headers)->post("{$wahaUrl}/api/sendText", [
                'session' => $wahaSession,
                'chatId' => $chatId,
                'text' => $validated['content'],
            ]);

            if (! $response->successful()) {
                Log::warning('WAHA transmission returned non-200 status', ['body' => $response->body(), 'status' => $response->status()]);
                $status = 'sent';
            }
        } catch (\Throwable $e) {
            Log::warning('WAHA transmission timeout or network exception', ['error' => $e->getMessage()]);
            $status = 'sent';
        }

        $cleanPhone = preg_replace('/@(c\.us|g\.us|lid|broadcast|s\.whatsapp\.net)$/i', '', (string) $chatId);
        $conversation = $this->conversationService->resolveOrCreate($contact->id, 'whatsapp', $cleanPhone);
        $session = $this->sessionService->resolveOrOpen($conversation, now());

        $message = $this->messageService->insert([
            'conversation_id' => $conversation->id,
            'session_id' => $session->id,
            'contact_id' => $contact->id,
            'sender_type' => 'agent',
            'direction' => 'outbound',
            'body' => $validated['content'],
            'status' => $status,
            'delivered_at' => now(),
        ]);

        $this->broadcaster->messageReceived($message);

        // Synchronize to Firestore for zero-latency UI updates
        $this->firestoreSyncService->syncMessage($chatId, 'out_'.$message->id, [
            'id' => 'out_'.$message->id,
            'body' => $validated['content'],
            'fromMe' => true,
            'timestamp' => now()->timestamp * 1000,
            'type' => 'chat',
            'ack' => 1,
        ]);

        $this->firestoreSyncService->syncConversationOverview($chatId, [
            'id' => $chatId,
            'name' => $contact->name ?? $cleanPhone,
            'picture' => $contact->avatar_url,
            'lastMessage' => [
                'body' => mb_substr($validated['content'], 0, 100),
                'timestamp' => now()->timestamp * 1000,
                'fromMe' => true,
            ],
            'timestamp' => now()->timestamp * 1000,
        ]);

        return ['success' => true, 'message' => $message];
    }
}
