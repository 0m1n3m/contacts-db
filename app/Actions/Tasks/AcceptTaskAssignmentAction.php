<?php
// app/Actions/Tasks/AcceptTaskAssignmentAction.php

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

class AcceptTaskAssignmentAction
{
    public function execute(
        User $actor,
        Task $task,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        // Solo puede aceptar si está asignado (admin/editor también pasan si quieres, pero mantengamos regla estricta)
        $assignment = TaskAssignment::where('task_id', $task->id)
            ->where('user_id', $actor->id)
            ->first();

        if (! $assignment) {
            throw new AuthorizationException('You are not assigned to this task.');
        }

        // ya aceptada
        if ($assignment->accepted_at) {
            return;
        }

        DB::transaction(function () use ($actor, $task, $assignment, $ipAddress, $userAgent) {
            $assignment->accepted_at = now();
            $assignment->save();

            $before = ['status' => $task->status?->value ?? (string) $task->status];

            // Si está created, al aceptar pasa a accepted
            if (($task->status?->value ?? (string) $task->status) === TaskStatus::Created->value) {
                $task->status = TaskStatus::Accepted;
            }

            $task->last_activity_at = now();
            $task->save();

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
                'before' => $before,
                'after' => ['status' => $task->status?->value ?? (string) $task->status],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? Str::limit($userAgent, 255, '') : null,
            ]);
        });
    }
}