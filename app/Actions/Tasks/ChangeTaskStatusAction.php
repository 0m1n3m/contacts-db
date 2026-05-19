<?php

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Models\AuditLog;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ChangeTaskStatusAction
{
    /**
     * @throws AuthorizationException
     */
    public function execute(
        User $actor,
        Task $task,
        TaskStatus $toStatus,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Task {
        // Policy-level auth
        if (! $actor->can('changeStatus', [$task, $toStatus])) {
            throw new AuthorizationException('Not allowed to change task status.');
        }

        $fromStatus = $task->status; // casted to TaskStatus
        $from = $fromStatus instanceof TaskStatus ? $fromStatus : TaskStatus::from((string) $fromStatus);

        return DB::transaction(function () use ($actor, $task, $from, $toStatus, $ipAddress, $userAgent) {
            // Auto-accept rule:
            // viewer moving/confirming in_progress => accept assignment if needed
            // IMPORTANT: this must run even if status doesn't change (no-op),
            // otherwise accepted_at can remain null when task is already in_progress.
            if (($actor->role ?? 'viewer') === 'viewer' && $toStatus === TaskStatus::InProgress) {
                $assignment = TaskAssignment::where('task_id', $task->id)
                    ->where('user_id', $actor->id)
                    ->lockForUpdate()
                    ->first();

                // Policy should have ensured viewer is assigned, but keep a defensive guard.
                if (! $assignment) {
                    throw new AuthorizationException('You are not assigned to this task.');
                }

                if (! $assignment->accepted_at) {
                    $assignment->accepted_at = now();
                    $assignment->save();

                    TaskComment::create([
                        'task_id' => $task->id,
                        'user_id' => $actor->id,
                        'type' => TaskComment::TYPE_SYSTEM,
                        'body' => 'Assignment accepted.',
                        'meta' => [
                            'event' => 'assignment_accepted',
                            'user_id' => $actor->id,
                        ],
                    ]);

                    AuditLog::create([
                        'actor_id' => $actor->id,
                        'project_id' => $task->project_id,
                        'entity_type' => Task::class,
                        'entity_id' => $task->id,
                        'action' => 'task.assignment_accepted',
                        'before' => [
                            'accepted_at' => null,
                        ],
                        'after' => [
                            'accepted_at' => $assignment->accepted_at->toISOString(),
                        ],
                        'ip_address' => $ipAddress,
                        'user_agent' => $userAgent ? Str::limit($userAgent, 255, '') : null,
                    ]);
                }
            }

            // No-op AFTER auto-accept
            if ($from === $toStatus) {
                return $task->refresh();
            }

            // Transition validation (business rule)
            $this->assertTransitionAllowed($actor, $from, $toStatus);

            $before = [
                'status' => $from->value,
            ];

            // Persist status change
            $task->status = $toStatus;
            $task->last_activity_at = now();
            $task->save();

            // System comment: status changed
            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $actor->id,
                'type' => TaskComment::TYPE_SYSTEM,
                'body' => sprintf('Status changed: %s → %s', $from->value, $toStatus->value),
                'meta' => [
                    'event' => 'status_changed',
                    'from' => $from->value,
                    'to' => $toStatus->value,
                ],
            ]);

            // Audit log: status changed
            AuditLog::create([
                'actor_id' => $actor->id,
                'project_id' => $task->project_id,
                'entity_type' => Task::class,
                'entity_id' => $task->id,
                'action' => 'task.status_changed',
                'before' => $before,
                'after' => [
                    'status' => $toStatus->value,
                ],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? Str::limit($userAgent, 255, '') : null,
            ]);

            return $task->refresh();
        });
    }

    private function assertTransitionAllowed(User $actor, TaskStatus $from, TaskStatus $to): void
    {
        $role = $actor->role ?? 'viewer';

        // Viewer: only allowed target is in_progress (policy checks this too).
        if ($role === 'viewer') {
            if ($to !== TaskStatus::InProgress) {
                throw new RuntimeException('Viewer can only move tasks to in_progress.');
            }

            return;
        }

        // admin/editor transitions
        $allowed = match ($from) {
            TaskStatus::Created => [TaskStatus::Accepted, TaskStatus::InProgress],
            TaskStatus::Accepted => [TaskStatus::InProgress],
            TaskStatus::InProgress => [TaskStatus::InReview, TaskStatus::Done],
            TaskStatus::InReview => [TaskStatus::InProgress, TaskStatus::Done],
            TaskStatus::Done => [TaskStatus::InProgress], // re-open allowed for admin/editor
        };

        if (! in_array($to, $allowed, true)) {
            throw new RuntimeException("Invalid task status transition: {$from->value} -> {$to->value}");
        }
    }
}