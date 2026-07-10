<?php

declare(strict_types=1);

namespace App\Jobs\AiHub;

use App\Models\AiAuditTrail;
use App\Models\UsageLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SimulateAiJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $providerId,
        public string $modelId,
        public string $message
    ) {}

    public function handle(): void
    {
        Log::info('Simulating AI job processing', [
            'provider_id' => $this->providerId,
            'model_id' => $this->modelId,
            'message' => $this->message,
        ]);

        // Simulate some processing time
        sleep(2);

        $inputTokens = max(5, strlen($this->message) * 4);
        $outputTokens = rand(150, 450);
        $totalTokens = $inputTokens + $outputTokens;

        $inputCost = $inputTokens * 0.000015;
        $outputCost = $outputTokens * 0.000030;
        $totalCost = $inputCost + $outputCost;

        UsageLog::create([
            'provider_id' => $this->providerId,
            'model_id' => $this->modelId,
            'intent_name' => 'job_simulation',
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'input_cost' => $inputCost,
            'output_cost' => $outputCost,
            'total_cost' => $totalCost,
        ]);

        AiAuditTrail::create([
            'event_type' => 'route_executed',
            'provider_id' => $this->providerId,
            'model_id' => $this->modelId,
            'intent' => 'job_simulation',
            'status' => 'success',
            'latency_ms' => rand(300, 900),
            'estimated_cost' => $totalCost,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'metadata' => ['cache_hit' => (rand(0, 10) > 8)],
        ]);

        Log::info('AI job simulation completed');
    }
}
