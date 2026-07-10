<?php

namespace App\Services;

use App\Models\UserPushToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebasePushService
{
    public function sendToTokens(array $tokens, array $payload, array $options = []): bool
    {
        $serverKey = config('notifications.fcm.server_key');

        if (empty($serverKey)) {
            throw new \RuntimeException('Firebase server key is not configured.');
        }

        $tokens = array_values(array_unique(array_filter($tokens)));

        if (empty($tokens)) {
            return false;
        }

        $notification = [
            'title' => $payload['title'] ?? 'Notification',
            'body' => $payload['body'] ?? '',
            'icon' => $payload['icon'] ?? null,
            'badge' => $payload['badge'] ?? null,
        ];

        $message = [
            'registration_ids' => $tokens,
            'priority' => 'high',
            'notification' => array_filter($notification),
            'data' => $payload['data'] ?? [],
            'webpush' => [
                'headers' => [
                    'Urgency' => 'high',
                ],
                'notification' => array_filter(array_merge(
                    $notification,
                    [
                        'click_action' => $options['click_action'] ?? url('/'),
                    ]
                )),
            ],
        ];

        if (! empty($options['data']) && is_array($options['data'])) {
            $message['data'] = array_merge($message['data'], $options['data']);
        }

        if (! empty($options['notification']) && is_array($options['notification'])) {
            $message['notification'] = array_merge($message['notification'], $options['notification']);
            $message['webpush']['notification'] = array_merge($message['webpush']['notification'], $options['notification']);
        }

        $response = Http::withHeaders([
            'Authorization' => "key {$serverKey}",
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $message);

        if (! $response->successful()) {
            Log::error('Firebase push failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'tokens' => $tokens,
            ]);

            return false;
        }

        return true;
    }

    public function sendToUsers(array $userIds, array $payload, array $options = []): bool
    {
        $tokens = UserPushToken::whereIn('user_id', $userIds)
            ->pluck('token')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $this->sendToTokens($tokens, $payload, $options);
    }
}
