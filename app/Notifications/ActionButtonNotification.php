<?php

namespace App\Notifications;

use App\Notifications\Channels\PushbulletChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ActionButtonNotification extends Notification
{
    use Queueable;

    protected string $title;

    protected string $message;

    protected array $actions;

    protected string $actionType;

    /**
     * Create a new notification instance.
     *
     * @param  string  $title  The notification title
     * @param  string  $message  The notification message
     * @param  array  $actions  Array of action buttons with 'label' and 'url'
     * @param  string  $actionType  Type of action (open, approve, reject, etc.)
     */
    public function __construct(
        string $title = 'Action Required',
        string $message = 'This is an action notification',
        array $actions = [],
        string $actionType = 'open'
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->actions = $actions;
        $this->actionType = $actionType;
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
     * Get the Pushbullet representation of the notification with action buttons.
     */
    public function toPushbullet(object $notifiable): array
    {
        $payload = [
            'type' => 'note',
            'title' => $this->title,
            'body' => $this->buildBodyWithActions(),
        ];

        // Add the first action URL if available
        if (! empty($this->actions) && isset($this->actions[0]['url'])) {
            $payload['url'] = $this->actions[0]['url'];
        }

        return $payload;
    }

    /**
     * Build the body text with action information.
     */
    protected function buildBodyWithActions(): string
    {
        $body = $this->message."\n\n";
        $body .= "Action Type: {$this->actionType}\n";

        if (! empty($this->actions)) {
            $body .= "\nAvailable Actions:\n";
            foreach ($this->actions as $index => $action) {
                $body .= '  '.($index + 1).". {$action['label']}";
                if (isset($action['url'])) {
                    $body .= " - {$action['url']}";
                }
                $body .= "\n";
            }
        }

        return $body;
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
            'action_type' => $this->actionType,
            'actions' => $this->actions,
        ];
    }
}
