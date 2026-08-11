<?php

namespace App\Jobs;

use App\Models\AgentTask;
use App\Services\ReActAgentEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessReActStep implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    public function __construct(
        public AgentTask $task,
        public array $observation
    ) {}

    public function handle(ReActAgentEngine $engine)
    {
        $engine->evaluateStep($this->task, $this->observation);
    }
}
