<?php

namespace App\Actions\Tasks;

use App\Models\AuditLog;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskComment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssignUsersToTaskAction
{
    /**
     * Synchronize users assigned to a task (replaces previous assignments).
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

        DB::transaction(function () use ($actor, $task, $userIds, $ipAddress, $userAgent) {
            $existing = TaskAssignment::where('task_id', $task->id)
                ->pluck('user_id')
                ->all();

            // Si no hay cambios, retorna
            if (array_values(array_unique($existing)) === $userIds) {
                return;
            }

            // Calcular qué agregar y qué eliminar
            $toAdd = array_diff($userIds, $existing);
            $toRemove = array_diff($existing, $userIds);

            // Eliminar asignaciones no seleccionadas
            if (count($toRemove) > 0) {
                TaskAssignment::where('task_id', $task->id)
                    ->whereIn('user_id', $toRemove)
                    ->delete();
            }

            // Agregar nuevas asignaciones y notificar
            foreach ($toAdd as $uid) {
                TaskAssignment::create([
                    'task_id' => $task->id,
                    'user_id' => $uid,
                    'assigned_by' => $actor->id,
                    'accepted_at' => null,
                ]);

                // Notificar al usuario asignado
                $assignedUser = User::find($uid);
                if ($assignedUser) {
                    NotificationService::notifyAssignment(
                        assignedUser: $assignedUser,
                        triggeredBy: $actor,
                        task: $task,
                    );
                }
            }

            $task->forceFill(['last_activity_at' => now()])->save();

            // Mensaje de comentario basado en qué cambió
            $message = 'Task assignments updated.';
            if (count($toAdd) > 0 && count($toRemove) > 0) {
                $message = 'Users assigned and unassigned.';
            } elseif (count($toAdd) > 0) {
                $message = 'Users assigned.';
            } elseif (count($toRemove) > 0) {
                $message = 'Users unassigned.';
            }

            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $actor->id,
                'type' => TaskComment::TYPE_SYSTEM,
                'body' => $message,
                'meta' => [
                    'event' => 'task_assignments_updated',
                    'added_user_ids' => array_values($toAdd),
                    'removed_user_ids' => array_values($toRemove),
                ],
            ]);

            AuditLog::create([
                'actor_id' => $actor->id,
                'project_id' => $task->project_id,
                'entity_type' => Task::class,
                'entity_id' => $task->id,
                'action' => 'task.assignments_updated',
                'before' => ['assigned_user_ids' => $existing],
                'after' => ['assigned_user_ids' => $userIds],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? Str::limit($userAgent, 255, '') : null,
            ]);
        });
    }
}