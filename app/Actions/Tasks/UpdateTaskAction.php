<?php

namespace App\Actions\Tasks;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\AuditLog;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateTaskAction
{
    /**
     * Updates a task's mutable fields.
     *
     * Expected $data keys:
     * - title (string|null)
     * - description (string|null)
     * - priority (TaskPriority|string|null)
     * - due_at (datetime|string|null)
     * - assignee_ids (array<int>|null)
     */
    public function execute(
        User $actor,
        Task $task,
        array $data,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Task {
        // Only admin, editor, or creator can update
        if (($actor->role ?? 'viewer') === 'viewer') {
            throw new AuthorizationException('Viewer cannot update tasks.');
        }

        // If editor, must be creator of the task
        if (($actor->role ?? 'viewer') === 'editor' && $task->created_by !== $actor->id) {
            throw new AuthorizationException('Editor can only update their own tasks.');
        }

        $before = [
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority?->value,
            'due_at' => $task->due_at?->toIso8601String(),
            'project_id' => $task->project_id,
        ];

        return DB::transaction(function () use ($actor, $task, $data, $before, $ipAddress, $userAgent) {
            $changed = false;

            // Update title
            if (array_key_exists('title', $data) && $data['title'] !== null) {
                $task->title = $data['title'];
                $changed = true;
            }

            // Update description
            if (array_key_exists('description', $data)) {
                $task->description = $data['description'];
                $changed = true;
            }

            // Update priority
            if (array_key_exists('priority', $data) && $data['priority'] !== null) {
                $priority = $this->toPriority($data['priority']);
                if ($priority) {
                    $task->priority = $priority;
                    $changed = true;
                }
            }

            // Update due_at
            if (array_key_exists('due_at', $data)) {
                $task->due_at = $data['due_at'];
                $changed = true;
            }

            // Update project_id
            if (array_key_exists('project_id', $data)) {
                $task->project_id = $data['project_id'];
                $changed = true;
            }

            if ($changed) {
                $task->last_activity_at = now();
                $task->save();
            }

            // Handle assignee changes
            if (array_key_exists('assignee_ids', $data)) {
                $assigneeIds = $data['assignee_ids'] ?? [];
                if (!is_array($assigneeIds)) {
                    $assigneeIds = [];
                }

                (new AssignUsersToTaskAction())->execute(
                    actor: $actor,
                    task: $task,
                    userIds: $assigneeIds,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent
                );

                $changed = true;
            }

            if (!$changed) {
                return $task;
            }

            // System comment
            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $actor->id,
                'type' => TaskComment::TYPE_SYSTEM,
                'body' => 'Task updated.',
                'meta' => [
                    'event' => 'task_updated',
                ],
            ]);

            // Audit log
            $after = [
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority?->value,
                'due_at' => $task->due_at?->toIso8601String(),
                'project_id' => $task->project_id,
            ];

            AuditLog::create([
                'actor_id' => $actor->id,
                'project_id' => $task->project_id,
                'entity_type' => Task::class,
                'entity_id' => $task->id,
                'action' => 'task.updated',
                'before' => $before,
                'after' => $after,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? Str::limit($userAgent, 255, '') : null,
            ]);

            return $task->refresh();
        });
    }

    private function toPriority(TaskPriority|string|null $value): ?TaskPriority
    {
        if ($value instanceof TaskPriority) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return TaskPriority::from($value);
        }

        return null;
    }
}