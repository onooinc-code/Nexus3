<?php

namespace App\Providers;

use App\Events\ContactAnalysisCompleted;
use App\Events\ContactIdentityConflictDetected;
use App\Events\ContactImportCompleted;
use App\Events\ContactReplyModeChanged;
use App\Events\HedraSoul\HedraSoulApprovalApproved;
use App\Events\HedraSoul\HedraSoulApprovalRejected;
use App\Events\HedraSoul\HedraSoulApprovalRequested;
use App\Events\HedraSoul\HedraSoulAutonomyChanged;
use App\Events\HedraSoul\HedraSoulCommandDetected;
use App\Events\HedraSoul\HedraSoulCommandExecuted;
use App\Events\HedraSoul\HedraSoulInstructionChanged;
use App\Events\HedraSoul\HedraSoulMemoryApproved;
use App\Events\HedraSoul\HedraSoulMemorySuggested;
use App\Events\HedraSoul\HedraSoulMessageCreated;
use App\Events\HedraSoul\HedraSoulMessageProcessed;
use App\Events\HedraSoul\HedraSoulModelChanged;
use App\Events\HedraSoul\HedraSoulNotificationCreated;
use App\Events\TaskCompletedEvent;
use App\Events\TaskFailedEvent;
use App\Events\TaskMovedToDLQEvent;
use App\Events\TaskStatusChangedEvent;
use App\Listeners\HandleContactAnalysisCompleted;
use App\Listeners\HandleContactIdentityConflict;
use App\Listeners\HandleContactImportCompleted;
use App\Listeners\HandleContactReplyModeChanged;
use App\Listeners\HandleTaskCompleted;
use App\Listeners\HandleTaskFailed;
use App\Listeners\LogDeadLetterTask;
use App\Models\AgentTask;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        TaskCompletedEvent::class => [
            HandleTaskCompleted::class,
        ],
        TaskFailedEvent::class => [
            HandleTaskFailed::class,
        ],
        TaskMovedToDLQEvent::class => [
            LogDeadLetterTask::class,
        ],
        // TaskStatusChangedEvent can be handled by adding listeners as needed
        ContactImportCompleted::class => [
            HandleContactImportCompleted::class,
        ],
        ContactAnalysisCompleted::class => [
            HandleContactAnalysisCompleted::class,
        ],
        ContactIdentityConflictDetected::class => [
            HandleContactIdentityConflict::class,
        ],
        ContactReplyModeChanged::class => [
            HandleContactReplyModeChanged::class,
        ],
        // HedraSoul Hub Events (13 broadcasting events)
        HedraSoulMessageCreated::class => [],
        HedraSoulMessageProcessed::class => [],
        HedraSoulCommandDetected::class => [],
        HedraSoulCommandExecuted::class => [],
        HedraSoulApprovalRequested::class => [],
        HedraSoulApprovalApproved::class => [],
        HedraSoulApprovalRejected::class => [],
        HedraSoulInstructionChanged::class => [],
        HedraSoulModelChanged::class => [],
        HedraSoulMemorySuggested::class => [],
        HedraSoulMemoryApproved::class => [],
        HedraSoulAutonomyChanged::class => [],
        HedraSoulNotificationCreated::class => [],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Handle task status changes from the Task model
        AgentTask::updated(function ($task) {
            // Check if status changed
            if ($task->wasChanged('status')) {
                event(new TaskStatusChangedEvent(
                    $task,
                    $task->getOriginal('status'),
                    $task->status
                ));
            }
        });
    }

    /**
     * Register any events with broadcasting.
     */
    public function broadcastOn(): void
    {
        // Events are broadcasted via the event classes themselves
    }
}
