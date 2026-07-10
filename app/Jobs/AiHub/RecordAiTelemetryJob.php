<?php

namespace App\Jobs\AiHub;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RecordAiTelemetryJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $providerId,
        public string $modelId,
        public float $latency,
        public bool $isSuccess
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Here we would typically save to an AiTelemetry or ProviderHealth model
        Log::info('AI Telemetry Recorded', [
            'provider' => $this->providerId,
            'model' => $this->modelId,
            'latency_ms' => $this->latency,
            'success' => $this->isSuccess,
        ]);

        // Mocking saving logic
        // \App\Models\ProviderHealth::record($this->providerId, $this->latency, $this->isSuccess);
    }
}
