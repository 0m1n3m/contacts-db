<?php

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Models\AuditLog;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class MoveTaskAction
{
    /**
     * Move a task to a status column and reorder the whole destination column.
     *
     * @param array<int> $orderedTaskIds Full ordered list of task IDs for the destination column.
     */
    public function execute(
        User $actor,
        Task $task,
        TaskStatus $toStatus,
        array $orderedTaskIds,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        $orderedTaskIds = array_values(array_unique(array_map('intval', $orderedTaskIds)));

        if (count($orderedTaskIds) === 0) {
            throw new RuntimeException('orderedTaskIds cannot be empty.');
        }

        if (! in_array((int) $task->id, $orderedTaskIds, true)) {
            throw new RuntimeException('orderedTaskIds must include the moved task id.');
        }

        // Basic authorization: if user can change status, they can move/reorder in that column
        if (! $actor->can('changeStatus', [$task, $toStatus])) {
            throw new AuthorizationException('Not allowed to move/reorder this task.');
        }

        DB::transaction(function () use ($actor, $task, $toStatus, $orderedTaskIds, $ipAddress, $userAgent) {
            $task->refresh();

            $fromStatus = $task->status instanceof TaskStatus ? $task->status : TaskStatus::from((string) $task->status);
            $from = [
                'status' => $fromStatus->value,
                'sort_order' => $task->sort_order,
            ];

            // 1) Change status if needed (and run its side-effects)
            if ($fromStatus !== $toStatus) {
                (new ChangeTaskStatusAction())->execute($actor, $task, $toStatus, $ipAddress, $userAgent);
                $task->refresh();
            }

            // 2) Load all tasks in the destination column that match orderedTaskIds
            /** @var Collection<int, Task> $tasks */
            $tasks = Task::query()
                ->whereIn('id', $orderedTaskIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($tasks->count() !== count($orderedTaskIds)) {
                $missing = array_values(array_diff($orderedTaskIds, $tasks->keys()->map(fn ($v) => (int) $v)->all()));
                throw new RuntimeException('Some task IDs do not exist: ' . implode(',', $missing));
            }

            // 3) Validate all tasks are in the destination column (project_id + status)
            $projectId = $task->project_id; // no cross-project move for now
            foreach ($orderedTaskIds as $id) {
                $t = $tasks[(int) $id];

                if ((int) $t->project_id !== (int) $projectId) {
                    throw new RuntimeException("Task {$id} is not in the destination project.");
                }

                $st = $t->status instanceof TaskStatus ? $t->status : TaskStatus::from((string) $t->status);
                if ($st !== $toStatus) {
                    throw new RuntimeException("Task {$id} is not in destination status column.");
                }
            }

            // 4) Reassign sort_order as 10,20,30...
            $i = 1;
            foreach ($orderedTaskIds as $id) {
                $newSort = $i * 10;
                $i++;

                $t = $tasks[(int) $id];
                if ((int) $t->sort_order !== $newSort) {
                    $t->sort_order = $newSort;
                    $t->save();
                }
            }

            // Update last_activity_at on moved task (optional but useful)
            $task->forceFill(['last_activity_at' => now()])->save();

            // 5) System comment + AuditLog (only one entry)
            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $actor->id,
                'type' => TaskComment::TYPE_SYSTEM,
                'body' => 'Task moved/reordered.',
                'meta' => [
                    'event' => 'task_moved',
                    'to_status' => $toStatus->value,
                    'ordered_task_ids' => $orderedTaskIds,
                ],
            ]);

            AuditLog::create([
                'actor_id' => $actor->id,
                'project_id' => $task->project_id,
                'entity_type' => Task::class,
                'entity_id' => $task->id,
                'action' => 'task.moved',
                'before' => $from,
                'after' => [
                    'status' => $toStatus->value,
                    'ordered_task_ids' => $orderedTaskIds,
                ],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? Str::limit($userAgent, 255, '') : null,
            ]);
        });
    }
}