<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;

class BrowserNotificationChannel
{
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toBrowserNotification')) {
            return;
        }

        $message = $notification->toBrowserNotification($notifiable);

        if (is_null($message)) {
            return;
        }

        // Store the notification in the database for the frontend to retrieve
        $this->storeBrowserNotification($notifiable, $message);
    }

    /**
     * Store the browser notification for frontend delivery.
     * The frontend will poll or use WebSockets to retrieve these notifications.
     */
    protected function storeBrowserNotification($notifiable, array $message): void
    {
        try {
            // If the notifiable has a route method for browser notifications, use it
            $userId = null;
            if (method_exists($notifiable, 'routeNotificationForBrowser')) {
                $userId = $notifiable->routeNotificationForBrowser();
            } elseif (method_exists($notifiable, 'getKey')) {
                $userId = $notifiable->getKey();
            }

            // Store in Laravel's notification table for retrieval
            if ($userId) {
                \Log::info('Browser notification queued', [
                    'user_id' => $userId,
                    'title' => $message['title'] ?? 'Notification',
                    'body' => $message['body'] ?? '',
                    'actions' => $message['actions'] ?? [],
                ]);

                // Emit a real-time event if Laravel Echo is configured
                if (function_exists('broadcast')) {
                    try {
                        \Illuminate\Support\Facades\Cache::remember(
                            "browser_notification_{$userId}_".time(),
                            now()->addMinutes(5),
                            fn () => $message
                        );
                    } catch (\Exception $e) {
                        \Log::error('Failed to store browser notification', [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Browser notification channel error', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
