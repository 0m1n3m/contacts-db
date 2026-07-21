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

class CreateTaskAction
{
    /**
     * Creates a task with sensible defaults and assigns an initial sort_order
     * within its Kanban column (project_id + status).
     *
     * Expected $data keys (typical):
     * - title (string, required)
     * - description (string|null)
     * - project_id (int|null)
     * - priority (TaskPriority|string|null)
     * - status (TaskStatus|string|null)   // default: created
     * - due_at (datetime|string|null)
     * - assignee_ids (array<int>|null)    // optional multi-assign
     */
    public function execute(
        User $actor,
        array $data,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Task {
        // If you have a dedicated policy later, swap this.
        if (($actor->role ?? 'viewer') === 'viewer') {
            throw new AuthorizationException('Not allowed to create tasks.');
        }

        $status = $this->toStatus($data['status'] ?? null) ?? TaskStatus::Created;
        $priority = $this->toPriority($data['priority'] ?? null) ?? TaskPriority::Normal;

        $projectId = array_key_exists('project_id', $data) ? $data['project_id'] : null;

        return DB::transaction(function () use ($actor, $data, $status, $priority, $projectId, $ipAddress, $userAgent) {
            // Compute initial sort order for the target column.
            // We use gaps of 10 to allow insertions between items later.
            $max = Task::query()
                ->select('sort_order')
                ->where('project_id', $projectId)
                ->where('status', $status->value)
                ->orderByDesc('sort_order')
                ->lockForUpdate()
                ->value('sort_order');

            $nextSortOrder = ((int) $max) + 10;
            if ($nextSortOrder <= 0) {
                $nextSortOrder = 10;
            }

            $task = Task::create([
                'project_id' => $projectId,
                'created_by' => $actor->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'priority' => $priority,
                'status' => $status,
                'sort_order' => $nextSortOrder,
                'due_at' => $data['due_at'] ?? null,
                'last_activity_at' => now(),
            ]);

            // Optional: assign users at creation time
            $assigneeIds = $data['assignee_ids'] ?? null;
            if (is_array($assigneeIds) && count($assigneeIds) > 0) {
                (new AssignUsersToTaskAction())->execute(
                    actor: $actor,
                    task: $task,
                    userIds: $assigneeIds,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent
                );
            }

            // Optional system comment for creation (handy for audit trail in UI)
            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $actor->id,
                'type' => TaskComment::TYPE_SYSTEM,
                'body' => 'Task created.',
                'meta' => [
                    'event' => 'task_created',
                ],
            ]);

            AuditLog::create([
                'actor_id' => $actor->id,
                'project_id' => $task->project_id,
                'entity_type' => Task::class,
                'entity_id' => $task->id,
                'action' => 'task.created',
                'before' => null,
                'after' => [
                    'title' => $task->title,
                    'status' => $task->status->value,
                    'priority' => $task->priority->value,
                    'project_id' => $task->project_id,
                    'sort_order' => $task->sort_order,
                ],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? Str::limit($userAgent, 255, '') : null,
            ]);

            return $task->refresh();
        });
    }

    private function toStatus(TaskStatus|string|null $value): ?TaskStatus
    {
        if ($value instanceof TaskStatus) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return TaskStatus::from($value);
        }

        return null;
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