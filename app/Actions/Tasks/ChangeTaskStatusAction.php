<?php

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Models\AuditLog;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskComment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ChangeTaskStatusAction
{
    /**
     * Update task status with role-based validation, auto-accept for viewers,
     * and notifications to relevant users.
     *
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
        if (!$actor->can('changeStatus', [$task, $toStatus])) {
            throw new AuthorizationException('Not allowed to change task status.');
        }

        $fromStatus = $task->status;
        $from = $fromStatus instanceof TaskStatus ? $fromStatus : TaskStatus::from((string) $fromStatus);

        return DB::transaction(function () use ($actor, $task, $from, $toStatus, $ipAddress, $userAgent) {
            $role = $actor->role ?? 'viewer';

            // 🚫 Viewer cannot reorder (same-status "move")
            if ($role === 'viewer' && $from === $toStatus) {
                throw new AuthorizationException('Viewer cannot reorder tasks within the same status.');
            }

            // Auto-accept rule for viewer moving to in_progress
            if ($role === 'viewer' && $toStatus === TaskStatus::InProgress) {
                $assignment = TaskAssignment::where('task_id', $task->id)
                    ->where('user_id', $actor->id)
                    ->lockForUpdate()
                    ->first();

                if (!$assignment) {
                    throw new AuthorizationException('You are not assigned to this task.');
                }

                if (!$assignment->accepted_at) {
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
                        'before' => ['accepted_at' => null],
                        'after' => ['accepted_at' => $assignment->accepted_at->toISOString()],
                        'ip_address' => $ipAddress,
                        'user_agent' => $userAgent ? Str::limit($userAgent, 255, '') : null,
                    ]);
                }
            }

            // Viewer must be assigned to move to in_review
            if ($role === 'viewer' && $toStatus === TaskStatus::InReview) {
                $assigned = TaskAssignment::where('task_id', $task->id)
                    ->where('user_id', $actor->id)
                    ->exists();

                if (!$assigned) {
                    throw new AuthorizationException('You are not assigned to this task.');
                }
            }

            // No-op after viewer reorder guard + auto-accept
            if ($from === $toStatus) {
                return $task->refresh();
            }

            // Validate transition is allowed for this role
            $this->assertTransitionAllowed($actor, $from, $toStatus);

            $before = ['status' => $from->value];

            // Update task status
            $task->status = $toStatus;
            $task->last_activity_at = now();
            $task->save();

            // Create system comment
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

            // 🔔 Notify assigned users
            foreach ($task->assignments as $assignment) {
                NotificationService::notifyStatusChange(
                    recipientUser: $assignment->user,
                    triggeredBy: $actor,
                    task: $task,
                    oldStatus: $from->value,
                    newStatus: $toStatus->value,
                );
            }

            // 🔔 Notify creator (if not the one changing status)
            if ($task->created_by !== $actor->id) {
                NotificationService::notifyStatusChange(
                    recipientUser: $task->creator,
                    triggeredBy: $actor,
                    task: $task,
                    oldStatus: $from->value,
                    newStatus: $toStatus->value,
                );
            }

            // Create audit log
            AuditLog::create([
                'actor_id' => $actor->id,
                'project_id' => $task->project_id,
                'entity_type' => Task::class,
                'entity_id' => $task->id,
                'action' => 'task.status_changed',
                'before' => $before,
                'after' => ['status' => $toStatus->value],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? Str::limit($userAgent, 255, '') : null,
            ]);

            return $task->refresh();
        });
    }

    private function assertTransitionAllowed(User $actor, TaskStatus $from, TaskStatus $to): void
    {
        $role = $actor->role ?? 'viewer';

        if ($role === 'admin') {
            return; // any -> any
        }

        if ($role === 'viewer') {
            if ($to === TaskStatus::InProgress && in_array($from, [TaskStatus::Created, TaskStatus::Accepted], true)) {
                return;
            }

            if ($to === TaskStatus::InReview && $from === TaskStatus::InProgress) {
                return;
            }

            throw new RuntimeException('Viewer can only move: created/accepted -> in_progress, and in_progress -> in_review.');
        }

        if ($role === 'editor') {
            $allowed = match ($from) {
                TaskStatus::Created => [TaskStatus::Accepted, TaskStatus::InProgress],
                TaskStatus::InProgress => [TaskStatus::InReview, TaskStatus::Done],
                TaskStatus::Done => [TaskStatus::InReview, TaskStatus::InProgress],
                default => [],
            };

            if (!in_array($to, $allowed, true)) {
                throw new RuntimeException("Invalid task status transition: {$from->value} -> {$to->value}");
            }

            return;
        }

        throw new RuntimeException('Invalid role for status transition.');
    }
}