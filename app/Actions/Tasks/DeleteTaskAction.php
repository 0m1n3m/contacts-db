<?php

namespace App\Actions\Tasks;

use App\Models\AuditLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeleteTaskAction
{
    /**
     * Soft deletes a task (if supported) or hard deletes.
     * Only admin or creator can delete.
     */
    public function execute(
        User $actor,
        Task $task,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        $role = $actor->role ?? 'viewer';

        // Only admin or creator can delete
        if ($role !== 'admin' && $task->created_by !== $actor->id) {
            throw new AuthorizationException('Not allowed to delete this task.');
        }

        DB::transaction(function () use ($actor, $task, $ipAddress, $userAgent) {
            AuditLog::create([
                'actor_id' => $actor->id,
                'project_id' => $task->project_id,
                'entity_type' => Task::class,
                'entity_id' => $task->id,
                'action' => 'task.deleted',
                'before' => [
                    'title' => $task->title,
                    'status' => $task->status->value,
                ],
                'after' => null,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? Str::limit($userAgent, 255, '') : null,
            ]);

            $task->delete();
        });
    }
}