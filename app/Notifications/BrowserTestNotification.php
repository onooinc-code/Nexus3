<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\BrowserNotificationChannel;

class BrowserTestNotification extends Notification
{
    use Queueable;

    protected string $title;

    protected string $message;

    protected array $actions;

    protected ?string $icon;

    protected ?string $badge;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $title = 'Browser Notification Test',
        string $message = 'This is a test browser notification',
        array $actions = [],
        ?string $icon = null,
        ?string $badge = null
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->actions = $actions;
        $this->icon = $icon ?? asset('favicon.ico');
        $this->badge = $badge ?? asset('favicon.ico');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [BrowserNotificationChannel::class];
    }

    /**
     * Get the browser notification representation.
     * This format follows the Web Notifications API standard.
     */
    public function toBrowserNotification(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->message,
            'icon' => $this->icon,
            'badge' => $this->badge,
            'tag' => 'nexus-notification',
            'requireInteraction' => ! empty($this->actions),
            'actions' => $this->actions,
            'data' => [
                'dateOfArrival' => now()->toIso8601String(),
                'primaryKey' => 'nexus-browser-notification',
            ],
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'actions' => $this->actions,
        ];
    }
}
