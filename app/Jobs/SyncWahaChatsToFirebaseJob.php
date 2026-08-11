<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\PeopleConnect\PeopleConnectMessage;
use App\Models\PeopleConnect\PeopleConnectSession;
use App\Services\PeopleConnect\FirestoreSyncService;
use App\Services\PeopleConnect\PeopleConnectContactResolver;
use App\Services\PeopleConnect\PeopleConnectConversationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncWahaChatsToFirebaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    protected ?string $specificChatId;

    /**
     * Create a new job instance.
     *
     * @param  string|null  $specificChatId  If provided, syncs only this chat. Otherwise, syncs all chats (overview).
     */
    public function __construct(?string $specificChatId = null)
    {
        $this->specificChatId = $specificChatId;
    }

    /**
     * Execute the job.
     */
    public function handle(
        PeopleConnectContactResolver $contactResolver,
        PeopleConnectConversationService $conversationService,
        FirestoreSyncService $firestoreSyncService
    ): void {
        if (! $firestoreSyncService->isConfigured()) {
            Log::warning('SyncWahaChatsToFirebaseJob skipped: Firebase DB URL not configured.');

            return;
        }

        $wahaUrl = config('services.waha.api_url', 'http://localhost:3000');
        $apiKey = config('services.waha.api_key');

        // Dynamically fetch active session from WAHA
        $session = config('services.waha.session', 'default');
        try {
            $sessionsResponse = Http::withHeaders(['X-Api-Key' => $apiKey, 'Accept' => 'application/json'])
                ->get("{$wahaUrl}/api/sessions");

            if ($sessionsResponse->successful() && ! empty($sessionsResponse->json())) {
                $activeSessions = $sessionsResponse->json();
                // Pick the first WORKING session
                foreach ($activeSessions as $activeSession) {
                    if (isset($activeSession['status']) && $activeSession['status'] === 'WORKING') {
                        $session = $activeSession['name'];
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to discover dynamic WAHA session. Falling back to config.', ['error' => $e->getMessage()]);
        }

        if ($this->specificChatId) {
            $this->syncSpecificChat($this->specificChatId, $wahaUrl, $session, $contactResolver, $conversationService, $firestoreSyncService);
        } else {
            $this->syncAllChats($wahaUrl, $session, $contactResolver, $conversationService, $firestoreSyncService);
        }
    }

    protected function syncAllChats($wahaUrl, $session, $contactResolver, $conversationService, $firestoreSyncService)
    {
        try {
            $apiKey = config('services.waha.api_key');
            $request = Http::withHeaders([
                'X-Api-Key' => $apiKey,
                'Accept' => 'application/json',
            ]);

            // Fetch chats overview
            $response = $request->get("{$wahaUrl}/api/{$session}/chats/overview", [
                'limit' => 50, // Sync the 50 most active chats every hour
            ]);

            if (! $response->successful()) {
                Log::error('WAHA chats/overview fetch failed', ['status' => $response->status(), 'body' => $response->body()]);

                return;
            }

            $chats = $response->json();

            foreach ($chats as $chat) {
                $this->syncSpecificChat($chat['id'], $wahaUrl, $session, $contactResolver, $conversationService, $firestoreSyncService, $chat);
            }

        } catch (\Exception $e) {
            Log::error('WAHA to Firebase global sync exception', ['error' => $e->getMessage()]);
        }
    }

    protected function syncSpecificChat($chatId, $wahaUrl, $session, $contactResolver, $conversationService, $firestoreSyncService, $chatOverview = null)
    {
        $phone = str_replace(['@c.us', '@g.us', '@lid'], '', $chatId);
        $pushName = $chatOverview['name'] ?? null;

        // 1. Resolve Contact
        $contact = $contactResolver->resolve($chatId, $phone, $pushName);

        // 2. Generate Firebase UID if missing
        if (empty($contact->firebase_uid)) {
            $contact->firebase_uid = Str::uuid()->toString();
            $contact->save();
        }

        // 3. Resolve Conversation to update local unread/preview
        $conversation = $conversationService->resolveOrCreate($contact->id, 'whatsapp', $chatId);

        if ($chatOverview && isset($chatOverview['lastMessage'])) {
            $timestamp = $chatOverview['lastMessage']['timestamp'] ?? null;
            $conversation->update([
                'last_message_at' => $timestamp ? Carbon::createFromTimestamp($timestamp) : now(),
                'last_message_preview' => mb_substr($chatOverview['lastMessage']['body'] ?? '', 0, 100),
            ]);
        }

        $apiKey = config('services.waha.api_key');
        $request = Http::withHeaders([
            'X-Api-Key' => $apiKey,
            'Accept' => 'application/json',
        ]);

        // 4. Fetch Messages for this Chat from WAHA
        $messagesResponse = $request->get("{$wahaUrl}/api/{$session}/chats/{$chatId}/messages", [
            'limit' => 20,
            'downloadMedia' => false,
        ]);

        $messages = [];
        if ($messagesResponse->successful()) {
            $messages = $messagesResponse->json();

            // Save historical messages to local Nexus DB
            $dbSession = PeopleConnectSession::firstOrCreate(
                ['conversation_id' => $conversation->id, 'status' => 'open'],
                ['started_at' => now(), 'channel' => 'whatsapp', 'contact_id' => $contact->id]
            );

            foreach ($messages as $msg) {
                $wahaId = $msg['id'] ?? null;
                if (! $wahaId) {
                    continue;
                }

                $isFromMe = $msg['fromMe'] ?? false;

                PeopleConnectMessage::updateOrCreate(
                    ['waha_message_id' => $wahaId],
                    [
                        'conversation_id' => $conversation->id,
                        'session_id' => $dbSession->id,
                        'contact_id' => $contact->id,
                        'sender_type' => $isFromMe ? 'agent' : 'contact',
                        'direction' => $isFromMe ? 'outbound' : 'inbound',
                        'body' => $msg['body'] ?? '',
                        'status' => 'delivered',
                        'delivered_at' => isset($msg['timestamp']) ? Carbon::createFromTimestamp($msg['timestamp']) : now(),
                    ]
                );
            }
        }

        // 5. Sync to Firestore
        $firestoreSyncService->syncContact($contact->firebase_uid, [
            'id' => $chatId,
            'nexus_contact_id' => $contact->id,
            'name' => $contact->name ?? $phone,
            'phone' => $phone,
            'avatar_url' => $contact->avatar_url,
        ]);

        $lastMessageBody = $chatOverview['lastMessage']['body'] ?? ($messages[0]['body'] ?? '');
        $lastMessageType = $chatOverview['lastMessage']['type'] ?? ($messages[0]['type'] ?? '');
        if ($lastMessageType === 'call_log') {
            $lastMessageBody = '📞 Missed/Failed Call';
        }

        $firestoreSyncService->syncConversationOverview($chatId, [
            'id' => $chatId,
            'name' => $contact->name ?? $phone,
            'picture' => $chatOverview['picture'] ?? $contact->avatar_url,
            'unreadCount' => $chatOverview['_chat']['unreadCount'] ?? $conversation->unread_count ?? 0,
            'isPinned' => $chatOverview['isPinned'] ?? false,
            'isMuted' => $chatOverview['isMuted'] ?? false,
            'isGroup' => $chatOverview['isGroup'] ?? false,
            'lastMessage' => [
                'body' => $lastMessageBody,
                'timestamp' => ($chatOverview['lastMessage']['timestamp'] ?? ($messages[0]['timestamp'] ?? time())) * 1000,
                'fromMe' => $chatOverview['lastMessage']['fromMe'] ?? ($messages[0]['fromMe'] ?? false),
            ],
            'timestamp' => ($chatOverview['lastMessage']['timestamp'] ?? ($messages[0]['timestamp'] ?? time())) * 1000,
        ]);

        foreach ($messages as $msg) {
            $msgId = $msg['id'] ?? null;
            if (! $msgId) {
                continue;
            }

            $msgTimestamp = $msg['timestamp'] ?? time();
            $firestoreSyncService->syncMessage($chatId, $msgId, [
                'id' => $msgId,
                'timestamp' => $msgTimestamp * 1000,
                'body' => ($msg['type'] ?? '') === 'call_log' ? '📞 Missed/Failed Call' : ($msg['body'] ?? ''),
                'fromMe' => $msg['fromMe'] ?? false,
                'hasMedia' => $msg['hasMedia'] ?? false,
                'type' => $msg['type'] ?? 'chat',
                'ack' => $msg['ack'] ?? 0,
            ]);
        }
    }
}
