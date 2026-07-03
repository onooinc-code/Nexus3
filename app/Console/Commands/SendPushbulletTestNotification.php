<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Notifications\TestPushbulletNotification;
use Illuminate\Notifications\Notifiable;

#[Signature('app:send-pushbullet-test-notification {--action=hedra : Action key for the test notification}')]
#[Description('Send a test notification to all Pushbullet devices')]
class SendPushbulletTestNotification extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->option('action');

        $this->info("Sending Pushbullet test notification with action: {$action}");

        try {
            // Create a simple notifiable object
            $notifiable = new class {
                use Notifiable;

                public function routeNotificationForPushbullet(): string
                {
                    return 'pushbullet';
                }
            };

            // Send the notification
            $notifiable->notify(new TestPushbulletNotification($action));

            $this->info('✓ Test notification sent successfully to all devices!');
            $this->line('');
            $this->info("Action: {$action}");
            $this->info('Status: Sent to all Pushbullet devices');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to send notification: {$e->getMessage()}");
            $this->line('');
            $this->info('Troubleshooting:');
            $this->line('1. Verify PUSHBULLET_API_KEY is set in .env');
            $this->line('2. Check that the token is correct');
            $this->line('3. Ensure you have devices registered with Pushbullet');

            return self::FAILURE;
        }
    }
}
