<?php

namespace App\Jobs;

use App\Models\AIModel;
use App\Models\AIProvider;
use App\Services\AiModelsHub\DynamicRestProvider;
use App\Services\AiModelsHub\EncryptedApiKeyStorage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncProviderModelsJob implements ShouldQueue
{
    use Queueable;

    protected $providerId;

    /**
     * Create a new job instance.
     */
    public function __construct($providerId)
    {
        $this->providerId = $providerId;
    }

    /**
     * Execute the job.
     */
    public function handle(EncryptedApiKeyStorage $keyStorage): void
    {
        $provider = AIProvider::find($this->providerId);
        if (! $provider || ! $provider->is_active) {
            return;
        }

        try {
            $restProvider = new DynamicRestProvider($this->providerId, $keyStorage);
            $models = $restProvider->getAvailableModels();

            if (! empty($models)) {
                foreach ($models as $modelData) {
                    AIModel::updateOrCreate(
                        [
                            'name' => $modelData['id'] ?? $modelData['name'],
                            'provider_id' => $this->providerId,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'last_synced_at' => now(),
                        ]
                    );
                }
                AIProvider::where('id', $this->providerId)->update(['last_synced_at' => now()]);
            }
        } catch (\Exception $e) {
            Log::error('Auto-sync failed for provider '.$this->providerId.': '.$e->getMessage());
        }
    }
}
