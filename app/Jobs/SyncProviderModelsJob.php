<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
    public function handle(\App\Services\AiModelsHub\EncryptedApiKeyStorage $keyStorage): void
    {
        $provider = \App\Models\AIProvider::find($this->providerId);
        if (!$provider || !$provider->is_active) return;

        try {
            $restProvider = new \App\Services\AiModelsHub\DynamicRestProvider($this->providerId, $keyStorage);
            $models = $restProvider->getAvailableModels();

            if (!empty($models)) {
                foreach ($models as $modelData) {
                    \App\Models\AIModel::updateOrCreate(
                        [
                            'name' => $modelData['id'] ?? $modelData['name'],
                            'provider_id' => $this->providerId,
                        ],
                        [
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'last_synced_at' => now(),
                        ]
                    );
                }
                \App\Models\AIProvider::where('id', $this->providerId)->update(['last_synced_at' => now()]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Auto-sync failed for provider ' . $this->providerId . ': ' . $e->getMessage());
        }
    }
}
