<?php

namespace App\Console\Commands;

use App\Services\AiHubService;
use Illuminate\Console\Command;

class AiApiKeyRotateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai-hub:rotate-keys {--force : Force rotation regardless of schedule}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotate AI API keys based on rotation policy';

    /**
     * Execute the console command.
     */
    public function handle(AiHubService $service)
    {
        $this->info('Starting AI API Key Rotation...');

        try {
            $rotatedCount = $service->rotateKeys($this->option('force'));
            $this->info("Successfully rotated {$rotatedCount} keys.");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to rotate keys: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
