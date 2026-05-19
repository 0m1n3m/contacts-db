<?php

namespace App\Actions\Tasks;

use App\Models\AuditLog;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssignUsersToTaskAction
{
    /**
     * Assign multiple users to a task (idempotent).
     *
     * @param  array<int>  $userIds
     */
    public function execute(
        User $actor,
        Task $task,
        array $userIds,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        // Por ahora: quien puede ver puede asignar? (lo endurecemos luego si quieres)
        if (! $actor->can('view', $task)) {
            throw new AuthorizationException('Not allowed to assign users to this task.');
        }

        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        if (count($userIds) === 0) {
            return;
        }

        DB::transaction(function () use ($actor, $task, $userIds, $ipAddress, $userAgent) {
            $existing = TaskAssignment::where('task_id', $task->id)
                ->pluck('user_id')
                ->all();

            $toAdd = array_values(array_diff($userIds, $existing));
            if (count($toAdd) === 0) {
                return;
            }

            foreach ($toAdd as $uid) {
                TaskAssignment::create([
                    'task_id' => $task->id,
                    'user_id' => $uid,
                    'assigned_by' => $actor->id,
                    'accepted_at' => null,
                ]);
            }

            $task->forceFill(['last_activity_at' => now()])->save();

            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $actor->id,
                'type' => TaskComment::TYPE_SYSTEM,
                'body' => 'Users assigned.',
                'meta' => [
                    'event' => 'users_assigned',
                    'user_ids' => $toAdd,
                ],
            ]);

            AuditLog::create([
                'actor_id' => $actor->id,
                'project_id' => $task->project_id,
                'entity_type' => Task::class,
                'entity_id' => $task->id,
                'action' => 'task.users_assigned',
                'before' => ['assigned_user_ids' => $existing],
                'after' => ['assigned_user_ids' => array_values(array_unique(array_merge($existing, $toAdd)))],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? Str::limit($userAgent, 255, '') : null,
            ]);
        });
    }
}