<?php

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MoveTaskAction
{
    public function __construct(
        private ChangeTaskStatusAction $changeStatus,
    ) {}

    public function execute(
        User $actor,
        Task $task,
        TaskStatus $toStatus,
        array $fromOrderedTaskIds,
        array $toOrderedTaskIds,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        $fromStatus = $task->status instanceof TaskStatus
            ? $task->status
            : TaskStatus::from((string) $task->status);

        // must include moved id in destination order (both reorder and cross-column)
        if (! in_array($task->id, $toOrderedTaskIds, true)) {
            throw new RuntimeException('to_ordered_task_ids must include the moved task id.');
        }

        $isSameColumn = ($fromStatus === $toStatus);

        // Only enforce the "from must not include moved id" rule for cross-column moves
        if (! $isSameColumn && ! empty($fromOrderedTaskIds) && in_array($task->id, $fromOrderedTaskIds, true)) {
            throw new RuntimeException('from_ordered_task_ids must NOT include the moved task id.');
        }

        DB::transaction(function () use (
            $actor,
            $task,
            $fromStatus,
            $toStatus,
            $fromOrderedTaskIds,
            $toOrderedTaskIds,
            $ipAddress,
            $userAgent,
            $isSameColumn,
        ) {
            // For same-column reorder, status change is a no-op, but keep it safe:
            // - admin/editor allowed anyway
            // - viewer policy might deny "changeStatus" to same status; ChangeTaskStatusAction handles no-op after policy.
            $this->changeStatus->execute(
                actor: $actor,
                task: $task,
                toStatus: $toStatus,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );

            // Reorder destination column
            $this->reorderColumnTolerant(
                projectId: $task->project_id,
                status: $toStatus,
                orderedTaskIds: $toOrderedTaskIds,
            );

            // Reorder source column only for cross-column moves and only when provided
            if (! $isSameColumn && ! empty($fromOrderedTaskIds)) {
                $this->reorderColumnTolerant(
                    projectId: $task->project_id,
                    status: $fromStatus,
                    orderedTaskIds: $fromOrderedTaskIds,
                );
            }
        });
    }

    /**
     * Tolerant reorder:
     * - Validate ids belong to the same board scope (project_id matches / is null)
     * - Reorder only ids that are actually in the requested status at ordering time
     */
    private function reorderColumnTolerant(?int $projectId, TaskStatus $status, array $orderedTaskIds): void
    {
        if (count($orderedTaskIds) === 0) {
            return;
        }

        $columnQuery = Task::query()
            ->where('status', $status->value)
            ->whereIn('id', $orderedTaskIds);

        if (is_null($projectId)) {
            $columnQuery->whereNull('project_id');
        } else {
            $columnQuery->where('project_id', $projectId);
        }

        $validIds = $columnQuery->pluck('id')->all();

        $pos = 1;
        foreach (array_values($orderedTaskIds) as $id) {
            if (! in_array($id, $validIds, true)) {
                continue;
            }

            Task::query()->where('id', $id)->update([
                'sort_order' => $pos * 10,
            ]);

            $pos++;
        }
    }
}