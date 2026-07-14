<?php

namespace App\Jobs;

use App\Models\SchedulerJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExecuteScheduledTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public SchedulerJob $job;

    /**
     * Create a new job instance.
     */
    public function __construct(SchedulerJob $job)
    {
        $this->job = $job;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Executing scheduled job: {$this->job->name} (Type: {$this->job->type})");

        try {
            $payload = $this->job->payload ?? [];

            switch ($this->job->type) {
                case 'command':
                    $command = $payload['command'] ?? null;
                    if (! $command) {
                        throw new \Exception('Command name is missing in payload.');
                    }
                    $parameters = $payload['parameters'] ?? [];
                    Artisan::call($command, $parameters);
                    Log::info("Artisan command executed successfully: {$command}");
                    break;

                case 'job':
                    $jobClass = $payload['job_class'] ?? null;
                    if (! $jobClass) {
                        throw new \Exception('Job class is missing in payload.');
                    }
                    if (! class_exists($jobClass)) {
                        throw new \Exception("Job class {$jobClass} does not exist.");
                    }
                    $jobData = $payload['data'] ?? [];
                    dispatch(new $jobClass(...$jobData));
                    Log::info("Job dispatched successfully: {$jobClass}");
                    break;

                case 'webhook':
                    $url = $payload['url'] ?? null;
                    if (! $url) {
                        throw new \Exception('Webhook URL is missing in payload.');
                    }
                    $method = strtoupper($payload['method'] ?? 'POST');
                    $headers = $payload['headers'] ?? [];
                    $data = $payload['data'] ?? [];

                    $response = Http::withHeaders($headers)->send($method, $url, [
                        'json' => $data,
                    ]);

                    if ($response->failed()) {
                        throw new \Exception('Webhook failed with status: '.$response->status());
                    }
                    Log::info("Webhook sent successfully to: {$url}");
                    break;

                default:
                    throw new \Exception("Unsupported scheduler job type: {$this->job->type}");
            }

            Log::info("Scheduled job completed successfully: {$this->job->name}");
        } catch (\Exception $e) {
            Log::error("Scheduled job failed: {$this->job->name}. Error: {$e->getMessage()}");
            throw $e;
        }
    }
}
