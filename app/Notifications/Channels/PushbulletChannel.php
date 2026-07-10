<?php

namespace App\Notifications\Channels;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Notifications\Notification;

class PushbulletChannel
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toPushbullet')) {
            return;
        }

        $message = $notification->toPushbullet($notifiable);

        if (is_null($message)) {
            return;
        }

        $this->dispatch($message);
    }

    /**
     * Dispatch the notification to Pushbullet.
     */
    protected function dispatch($message): void
    {
        $apiKey = config('services.pushbullet.key');

        if (! $apiKey) {
            throw new \Exception('Pushbullet API key not configured');
        }

        try {
            $this->client->post('https://api.pushbullet.com/v2/pushes', [
                'headers' => [
                    'Access-Token' => $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $this->buildPayload($message),
            ]);
        } catch (RequestException $e) {
            \Log::error('Pushbullet notification failed', [
                'error' => $e->getMessage(),
                'payload' => $this->buildPayload($message),
            ]);

            throw $e;
        }
    }

    /**
     * Build the payload for Pushbullet API.
     */
    protected function buildPayload($message): array
    {
        $payload = [
            'type' => $message['type'] ?? 'note',
            'title' => $message['title'] ?? '',
            'body' => $message['body'] ?? '',
        ];

        // Send to all devices if no device_iden is specified
        if (! isset($message['device_iden'])) {
            // Omit device_iden to send to all devices
        } else {
            $payload['device_iden'] = $message['device_iden'];
        }

        // Add optional fields
        if (isset($message['url'])) {
            $payload['url'] = $message['url'];
        }

        if (isset($message['file_name'])) {
            $payload['file_name'] = $message['file_name'];
            $payload['file_type'] = $message['file_type'] ?? 'application/octet-stream';
            $payload['file_url'] = $message['file_url'];
        }

        return $payload;
    }
}
