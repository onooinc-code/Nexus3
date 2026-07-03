<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Notifications\BrowserTestNotification;
use Illuminate\Notifications\Notifiable;

#[Signature('app:test-browser-notification {--type=basic : Notification type (basic, interactive, warning, etc.)}')]
#[Description('Test Chrome/Browser notifications')]
class TestBrowserNotification extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $notificationType = $this->option('type');

        $this->info('═══════════════════════════════════════════════════════');
        $this->info('   Nexus Browser Notification Test');
        $this->info('═══════════════════════════════════════════════════════');
        $this->line('');

        try {
            // Create a notifiable object
            $notifiable = new class {
                use Notifiable;

                public function routeNotificationForBrowser(): string
                {
                    return 'browser-user-1';
                }
            };

            // Define test scenarios
            $testCases = [
                'basic' => [
                    'title' => 'Nexus Notification',
                    'message' => 'This is a basic browser notification.',
                    'actions' => [],
                ],
                'interactive' => [
                    'title' => 'Task Notification',
                    'message' => 'You have a new task assigned.',
                    'actions' => [
                        ['action' => 'open', 'title' => 'View Task'],
                        ['action' => 'dismiss', 'title' => 'Dismiss'],
                    ],
                ],
                'warning' => [
                    'title' => 'Warning Alert',
                    'message' => 'This requires your immediate attention!',
                    'actions' => [
                        ['action' => 'confirm', 'title' => 'I Understand'],
                    ],
                ],
                'success' => [
                    'title' => 'Success',
                    'message' => 'Your operation completed successfully.',
                    'actions' => [],
                ],
                'error' => [
                    'title' => 'Error Notification',
                    'message' => 'An error occurred. Please check the details.',
                    'actions' => [
                        ['action' => 'retry', 'title' => 'Retry'],
                        ['action' => 'report', 'title' => 'Report'],
                    ],
                ],
            ];

            if (! isset($testCases[$notificationType])) {
                $this->warn("Unknown notification type: {$notificationType}");
                $this->line('Available types: '.implode(', ', array_keys($testCases)));

                return self::FAILURE;
            }

            $testCase = $testCases[$notificationType];

            $this->info("Testing: {$notificationType}");
            $this->line('');
            $this->line("Title: {$testCase['title']}");
            $this->line("Message: {$testCase['message']}");

            if (! empty($testCase['actions'])) {
                $this->line('');
                $this->line('Actions:');
                foreach ($testCase['actions'] as $index => $action) {
                    $this->line("  ".($index + 1).". {$action['title']} (action: {$action['action']})");
                }
            }

            $this->line('');

            // Send the browser notification
            $notification = new BrowserTestNotification(
                title: $testCase['title'],
                message: $testCase['message'],
                actions: $testCase['actions']
            );

            $notifiable->notify($notification);

            $this->info('✓ Browser notification queued successfully!');
            $this->line('');
            $this->info('Status: Notification ready for browser display');
            $this->info('Type: '.$notificationType);
            $this->line('');
            $this->line('Note: Browser notifications require:');
            $this->line('  • User browser permission enabled');
            $this->line('  • Active browser connection to your app');
            $this->line('  • Web Notifications API support');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to queue notification: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
