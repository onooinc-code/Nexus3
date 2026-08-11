<?php

namespace App\Services\PeopleConnect;

use App\Models\Agent;
use App\Models\AIApiKey;
use App\Models\AIProvider;
use App\Models\Contact;
use App\Models\PeopleConnect\PeopleConnectConversation;
use App\Models\PeopleConnect\PeopleConnectMessage;
use App\Models\PeopleConnect\PeopleConnectSession;
use Illuminate\Database\Eloquent\Collection;

class PeopleConnectConversationService
{
    /**
     * Resolves an existing conversation or creates a new one.
     *
     * @param  string  $channel  e.g., 'whatsapp'
     * @param  string  $chatId  WAHA chatId
     */
    public function resolveOrCreate(int $contactId, string $channel, string $chatId): PeopleConnectConversation
    {
        $provider = 'waha'; // Assuming waha is the only provider for now

        $conversation = PeopleConnectConversation::where('contact_id', $contactId)
            ->where('channel', $channel)
            ->where('provider', $provider)
            ->first();

        if (! $conversation) {
            $conversation = PeopleConnectConversation::where('provider_conversation_id', $chatId)->first();
        }

        if ($conversation) {
            $updates = [];
            if ($conversation->contact_id !== $contactId) {
                $updates['contact_id'] = $contactId;
            }
            if (empty($conversation->provider_conversation_id) || ($chatId && str_contains($chatId, '@c.us'))) {
                $updates['provider_conversation_id'] = $chatId;
            }
            if (! empty($updates)) {
                $conversation->update($updates);
            }

            return $conversation;
        }

        return PeopleConnectConversation::create([
            'contact_id' => $contactId,
            'channel' => $channel,
            'provider' => $provider,
            'provider_conversation_id' => $chatId,
            'status' => 'active',
            'unread_count' => 0,
            'reply_mode_effective' => 'manual',
        ]);
    }

    /**
     * Get system-wide statistics for PeopleConnect hub.
     */
    public function getSystemStats(): array
    {
        $totalContacts = Contact::whereHas('peopleConnectConversations')->count();
        $activeSessions = PeopleConnectSession::where('status', 'open')->count();
        $unreadConversations = PeopleConnectConversation::where('unread_count', '>', 0)->count();

        $activeAgent = Agent::where('is_active', true)->orderBy('id', 'desc')->first()
            ?? Agent::first()
            ?? new Agent(['name' => 'Souly AI Engine']);

        $activeProviders = AIProvider::where('is_active', true)->pluck('name');
        $fallbackChain = $activeProviders->filter(fn ($name) => in_array($name, ['Google Gemini', 'OpenAI', 'Anthropic', 'DeepSeek', 'Groq', 'Mistral AI', 'Perplexity AI']))->take(3)->implode(' → ');
        if (empty($fallbackChain)) {
            $fallbackChain = $activeProviders->take(3)->implode(' → ') ?: 'OpenAI → Gemini → Anthropic';
        }

        $totalKeys = AIApiKey::count();
        $totalMessages = PeopleConnectMessage::count();
        $totalConversations = PeopleConnectConversation::count();

        return [
            'total_contacts' => $totalContacts,
            'active_sessions' => $activeSessions,
            'unread_conversations' => $unreadConversations,
            'status' => 'healthy',
            'active_agent_name' => $activeAgent->name,
            'fallback_chain' => $fallbackChain,
            'api_keys_pool_status' => $totalKeys > 0 ? "{$totalKeys} Keys Monitored" : 'Pool Active & Protected',
            'pipeline_status' => "{$totalConversations} Chats / {$totalMessages} Msgs",
            'total_conversations' => $totalConversations,
            'total_messages' => $totalMessages,
        ];
    }

    /**
     * Search conversations or return recent ones if query is empty.
     */
    public function searchConversations(?string $query): Collection
    {
        if (empty($query)) {
            return PeopleConnectConversation::with('contact')
                ->orderBy('last_message_at', 'desc')
                ->take(20)
                ->get();
        }

        return Contact::whereHas('peopleConnectConversations')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('whatsapp_number', 'like', "%{$query}%");
            })
            ->with(['peopleConnectConversations' => function ($q) {
                $q->select('id', 'contact_id', 'channel', 'status', 'unread_count', 'last_message_preview', 'last_message_at');
            }])
            ->take(20)
            ->get();
    }

    /**
     * Retrieve a full conversation detail with sessions and messages.
     */
    public function getConversationDetails(int|string $id): PeopleConnectConversation
    {
        $query = PeopleConnectConversation::with([
            'contact',
            'sessions' => function ($q) {
                $q->orderBy('opened_at', 'desc')->take(5);
            },
            'messages' => function ($q) {
                $q->orderBy('created_at', 'desc')->take(100);
            },
        ]);

        if (is_numeric($id)) {
            $conv = (clone $query)->where('id', (int) $id)->first();
            if ($conv) {
                return $conv;
            }
        }

        // 1. Try exact match on provider_conversation_id
        $conv = (clone $query)->where('provider_conversation_id', (string) $id)->first();
        if ($conv) {
            return $conv;
        }

        // 2. Try match without suffix or via contact phone
        $cleanPhone = preg_replace('/@(c\.us|g\.us|lid|broadcast|s\.whatsapp\.net)$/i', '', (string) $id);
        $cleanPhone = trim($cleanPhone);

        if (! empty($cleanPhone)) {
            $conv = (clone $query)->where(function ($q) use ($cleanPhone) {
                $q->where('provider_conversation_id', 'like', "%{$cleanPhone}%")
                    ->orWhereHas('contact', function ($cq) use ($cleanPhone) {
                        $cq->where('phone', 'like', "%{$cleanPhone}%")
                            ->orWhere('whatsapp_number', 'like', "%{$cleanPhone}%");
                    });
            })->first();

            if ($conv) {
                return $conv;
            }
        }

        // 3. Fallback: resolve or create contact & conversation
        $contact = Contact::where('phone', 'like', "%{$cleanPhone}%")
            ->orWhere('whatsapp_number', 'like', "%{$cleanPhone}%")
            ->first();

        if (! $contact) {
            $contact = Contact::create([
                'name' => "WhatsApp User ({$cleanPhone})",
                'phone' => $cleanPhone,
                'whatsapp_number' => (string) $id,
            ]);
        }

        return $this->resolveOrCreate($contact->id, 'whatsapp', (string) $id);
    }

    /**
     * Update the effective reply mode for a conversation.
     */
    public function updateReplyMode(int|string $id, ?string $replyMode): PeopleConnectConversation
    {
        if (is_numeric($id)) {
            $conversation = PeopleConnectConversation::where('id', $id)->first()
                ?? PeopleConnectConversation::where('provider_conversation_id', $id)->firstOrFail();
        } else {
            $conversation = PeopleConnectConversation::where('provider_conversation_id', $id)->firstOrFail();
        }

        $conversation->update([
            'reply_mode_effective' => $replyMode,
        ]);

        return $conversation;
    }
}
