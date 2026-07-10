<?php

namespace App\Services\HedraSoul;

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
use App\Models\HedrasoulApprovalRequest;
use App\Models\HedrasoulMessage;
use App\Models\HedrasoulNotification;

/**
 * HedraSoulRealtimeBroadcaster: Broadcasts all HedraSoulHub realtime events to Reverb.
 * Each event is broadcast on the private channel hedrasoul.hub.{user_id}.
 */
class HedraSoulRealtimeBroadcaster
{
    /**
     * Broadcast message created event.
     */
    public function broadcastMessageCreated(HedrasoulMessage $message, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }
        try {
            broadcast(new HedraSoulMessageCreated($message, $userId))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast message created: '.$e->getMessage());
        }
    }

    /**
     * Broadcast message processed event.
     */
    public function broadcastMessageProcessed(HedrasoulMessage $message, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }
        try {
            broadcast(new HedraSoulMessageProcessed($message, $userId))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast message processed: '.$e->getMessage());
        }
    }

    /**
     * Broadcast command detected event.
     */
    public function broadcastCommandDetected(array $payload, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }
        try {
            broadcast(new HedraSoulCommandDetected(
                $userId,
                $payload['message_id'] ?? 0,
                $payload['intent'] ?? '',
                $payload['risk_level'] ?? 'low',
                (array) ($payload['policy_result'] ?? [])
            ))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast command detected: '.$e->getMessage());
        }
    }

    /**
     * Broadcast command executed event.
     */
    public function broadcastCommandExecuted(array $payload, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }
        try {
            broadcast(new HedraSoulCommandExecuted(
                $userId,
                $payload['trace_id'] ?? '',
                $payload['selected_action'] ?? '',
                $payload['tasks_created'] ?? null,
                $payload['workflows_triggered'] ?? null
            ))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast command executed: '.$e->getMessage());
        }
    }

    /**
     * Broadcast approval requested event.
     */
    public function broadcastApprovalRequested(HedrasoulApprovalRequest $approval, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }
        try {
            broadcast(new HedraSoulApprovalRequested($userId, $approval))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast approval requested: '.$e->getMessage());
        }
    }

    /**
     * Broadcast approval approved event.
     */
    public function broadcastApprovalApproved(array $payload, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }
        try {
            broadcast(new HedraSoulApprovalApproved(
                $userId,
                $payload['approval_id'] ?? 0,
                $payload['decided_by'] ?? null
            ))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast approval approved: '.$e->getMessage());
        }
    }

    /**
     * Broadcast approval rejected event.
     */
    public function broadcastApprovalRejected(array $payload, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }
        try {
            broadcast(new HedraSoulApprovalRejected(
                $userId,
                $payload['approval_id'] ?? 0,
                $payload['decided_by'] ?? null
            ))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast approval rejected: '.$e->getMessage());
        }
    }

    /**
     * Broadcast instruction changed event.
     */
    public function broadcastInstructionChanged(array $payload, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }
        try {
            broadcast(new HedraSoulInstructionChanged(
                $userId,
                $payload['version_id'] ?? 0,
                $payload['version_number'] ?? 0,
                $payload['status'] ?? 'active',
                $payload['activated_by'] ?? null
            ))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast instruction changed: '.$e->getMessage());
        }
    }

    /**
     * Broadcast model changed event.
     */
    public function broadcastModelChanged(array $payload, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }
        try {
            broadcast(new HedraSoulModelChanged(
                $userId,
                $payload['model_instance_id'] ?? 0,
                $payload['changed_at'] ?? null
            ))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast model changed: '.$e->getMessage());
        }
    }

    /**
     * Broadcast memory suggested event.
     */
    public function broadcastMemorySuggested(array $payload, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }
        try {
            broadcast(new HedraSoulMemorySuggested(
                $userId,
                $payload['suggestion_id'] ?? 0,
                $payload['memory_type'] ?? ''
            ))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast memory suggested: '.$e->getMessage());
        }
    }

    /**
     * Broadcast memory approved event.
     */
    public function broadcastMemoryApproved(array $payload, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }
        try {
            broadcast(new HedraSoulMemoryApproved(
                $userId,
                $payload['fact_id'] ?? 0,
                $payload['suggestion_id'] ?? 0,
                $payload['memory_type'] ?? ''
            ))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast memory approved: '.$e->getMessage());
        }
    }

    /**
     * Broadcast autonomy changed event.
     */
    public function broadcastAutonomyChanged(array $payload, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }
        try {
            broadcast(new HedraSoulAutonomyChanged(
                $userId,
                $payload['autonomy_mode'] ?? 'unknown',
                $payload['changed_by'] ?? null,
                $payload['changed_at'] ?? null
            ))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast autonomy changed: '.$e->getMessage());
        }
    }

    /**
     * Broadcast notification created event.
     */
    public function broadcastNotificationCreated(HedrasoulNotification $notification, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }
        try {
            broadcast(new HedraSoulNotificationCreated($userId, $notification))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast notification created: '.$e->getMessage());
        }
    }
}
