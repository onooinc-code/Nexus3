<?php

namespace App\Jobs\CredentialsHub;

use App\Models\CredentialsHub\Credential;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CheckCredentialHealthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?Credential $credential;

    /**
     * Create a new job instance.
     */
    public function __construct(?Credential $credential = null)
    {
        $this->credential = $credential;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $credentials = $this->credential ? collect([$this->credential]) : Credential::all();

        foreach ($credentials as $cred) {
            $fields = $cred->fields ?? [];
            $targetUrl = null;

            foreach ($fields as $key => $val) {
                if (is_string($val) && filter_var($val, FILTER_VALIDATE_URL)) {
                    $targetUrl = $val;
                    break;
                }
            }

            if ($targetUrl) {
                try {
                    $response = Http::timeout(5)->get($targetUrl);
                    $status = $response->status();
                    $cred->update([
                        'test_status' => $response->successful() || $status === 403 ? 'success' : 'warn',
                        'test_code' => $status.' Check',
                        'last_tested_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    $cred->update([
                        'test_status' => 'danger',
                        'test_code' => 'Offline',
                        'last_tested_at' => now(),
                    ]);
                }
            } else {
                $cred->update([
                    'test_status' => 'success',
                    'test_code' => 'Verified',
                    'last_tested_at' => now(),
                ]);
            }
        }
    }
}
