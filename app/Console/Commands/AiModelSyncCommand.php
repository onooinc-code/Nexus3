<?php

namespace App\Console\Commands;

use App\Services\AiHubService;
use Illuminate\Console\Command;

class AiModelSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai-hub:sync-models {--provider= : Sync a specific provider by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync AI models from configured providers to local database';

    /**
     * Execute the console command.
     */
    public function handle(AiHubService $service)
    {
        $this->info('Starting AI Models Synchronization...');

        $providerId = $this->option('provider');

        try {
            $syncedCount = $service->syncModels($providerId);
            $this->info("Successfully synced {$syncedCount} models.");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to sync models: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
