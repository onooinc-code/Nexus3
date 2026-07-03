<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\PushbulletChannel;

class TestPushbulletNotification extends Notification
{
    use Queueable;

    protected string $action;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $action = 'hedra')
    {
        $this->action = $action;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [PushbulletChannel::class];
    }

    /**
     * Get the Pushbullet representation of the notification.
     * Sends to all devices by default.
     */
    public function toPushbullet(object $notifiable): array
    {
        return [
            'type' => 'note',
            'title' => 'Nexus Test Notification',
            'body' => sprintf(
                'Test notification with action: %s. This message was sent to all your devices.',
                $this->action
            ),
            // Omitting device_iden sends to all devices
            // To send to a specific device, add: 'device_iden' => 'your_device_id'
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
            'action' => $this->action,
            'message' => 'Test notification sent via Pushbullet',
        ];
    }
}
