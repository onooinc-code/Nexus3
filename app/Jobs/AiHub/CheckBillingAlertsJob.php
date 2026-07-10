<?php

namespace App\Jobs\AiHub;

use App\Models\Budget;
use App\Models\UsageLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckBillingAlertsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $providerId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $budget = Budget::where('type', 'daily')->first();
        if (! $budget) {
            return;
        }

        $todayCost = UsageLog::whereDate('created_at', now()->toDateString())
            ->where('provider_id', $this->providerId)
            ->sum('total_cost');

        $threshold = $budget->limit * 0.90; // 90% threshold

        if ($todayCost >= $threshold) {
            Log::warning('AI Provider Billing Alert', [
                'provider' => $this->providerId,
                'cost' => $todayCost,
                'threshold' => $threshold,
            ]);

            // In a real scenario, we would trigger an event or send a notification:
            // event(new \App\Events\BillingAlertTriggered($this->providerId, $todayCost));
        }
    }
}
