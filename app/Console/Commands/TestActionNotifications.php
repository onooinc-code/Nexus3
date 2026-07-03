<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Notifications\ActionButtonNotification;
use Illuminate\Notifications\Notifiable;

#[Signature('app:test-action-notifications {--type=approval : Action type (approval, rejection, redirect, etc.)}')]
#[Description('Test notifications with action buttons')]
class TestActionNotifications extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $actionType = $this->option('type');

        $this->info('═══════════════════════════════════════════════════════');
        $this->info('   Nexus Notification Action Button Test');
        $this->info('═══════════════════════════════════════════════════════');
        $this->line('');

        try {
            // Create a notifiable object
            $notifiable = new class {
                use Notifiable;

                public function routeNotificationForPushbullet(): string
                {
                    return 'pushbullet';
                }
            };

            // Test different action scenarios
            $testCases = [
                'approval' => [
                    'title' => 'Approval Request',
                    'message' => 'Your document requires approval before publishing.',
                    'actions' => [
                        ['label' => 'Approve', 'url' => 'https://soulyeg.online/approve'],
                        ['label' => 'Reject', 'url' => 'https://soulyeg.online/reject'],
                        ['label' => 'Review', 'url' => 'https://soulyeg.online/review'],
                    ],
                ],
                'rejection' => [
                    'title' => 'Action Rejected',
                    'message' => 'Your submission has been rejected. Click below to review feedback.',
                    'actions' => [
                        ['label' => 'View Feedback', 'url' => 'https://soulyeg.online/feedback'],
                        ['label' => 'Resubmit', 'url' => 'https://soulyeg.online/resubmit'],
                    ],
                ],
                'redirect' => [
                    'title' => 'Redirect Action',
                    'message' => 'Click an action below to proceed.',
                    'actions' => [
                        ['label' => 'Dashboard', 'url' => 'https://soulyeg.online/dashboard'],
                        ['label' => 'Settings', 'url' => 'https://soulyeg.online/settings'],
                        ['label' => 'Help', 'url' => 'https://soulyeg.online/help'],
                    ],
                ],
                'alert' => [
                    'title' => 'System Alert',
                    'message' => 'An important system alert requires your attention.',
                    'actions' => [
                        ['label' => 'Acknowledge', 'url' => 'https://soulyeg.online/acknowledge'],
                    ],
                ],
            ];

            if (! isset($testCases[$actionType])) {
                $this->warn("Unknown action type: {$actionType}");
                $this->line('Available types: '.implode(', ', array_keys($testCases)));

                return self::FAILURE;
            }

            $testCase = $testCases[$actionType];

            $this->info("Testing: {$actionType}");
            $this->line('');
            $this->line("Title: {$testCase['title']}");
            $this->line("Message: {$testCase['message']}");
            $this->line('');
            $this->line('Actions:');
            foreach ($testCase['actions'] as $index => $action) {
                $this->line("  ".($index + 1).". {$action['label']} → {$action['url']}");
            }
            $this->line('');

            // Send the notification
            $notification = new ActionButtonNotification(
                title: $testCase['title'],
                message: $testCase['message'],
                actions: $testCase['actions'],
                actionType: $actionType
            );

            $notifiable->notify($notification);

            $this->info('✓ Action notification sent successfully to all devices!');
            $this->line('');
            $this->info('Status: Notification delivered via Pushbullet');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to send notification: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
