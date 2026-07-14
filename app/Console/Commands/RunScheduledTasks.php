<?php

namespace App\Console\Commands;

use App\Jobs\ExecuteScheduledTask;
use App\Models\SchedulerJob;
use Carbon\Carbon;
use Cron\CronExpression;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunScheduledTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nexus:run-scheduler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run due scheduled tasks from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for due scheduled tasks...');
        $now = Carbon::now();

        DB::transaction(function () use ($now) {
            // Atomic claim using SELECT FOR UPDATE
            $jobs = SchedulerJob::where('status', 'active')
                ->where('is_running', false)
                ->where(function ($q) use ($now) {
                    $q->whereNull('next_run_at')
                        ->orWhere('next_run_at', '<=', $now);
                })
                ->lockForUpdate()
                ->get();

            if ($jobs->isEmpty()) {
                $this->info('No tasks due at this time.');

                return;
            }

            foreach ($jobs as $job) {
                try {
                    $this->info("Dispatching scheduled job: {$job->name}");

                    $cron = new CronExpression($job->cron_expression);

                    // Mark as running (claimed)
                    $job->is_running = true;
                    $job->save();

                    // Dispatch execution job
                    ExecuteScheduledTask::dispatch($job);

                    // Update next run time and mark as not running (since it is successfully dispatched)
                    $job->last_run_at = $now;
                    $job->next_run_at = Carbon::instance($cron->getNextRunDate($now));
                    $job->is_running = false;
                    $job->save();
                } catch (\Exception $e) {
                    $this->error("Error processing job {$job->id}: {$e->getMessage()}");
                    $job->is_running = false;
                    $job->status = 'failing';
                    $job->save();
                }
            }
        });

        $this->info('Finished dispatching tasks.');
    }
}
