<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\MoveTaskAction;
use App\Enums\TaskStatus;
use App\Http\Requests\Tasks\MoveTaskRequest;
use App\Models\Task;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class TaskMoveController extends Controller
{
    public function __invoke(MoveTaskRequest $request, Task $task, MoveTaskAction $action): JsonResponse
    {
        try {
            $actor = $request->user();

            $toStatus = TaskStatus::from($request->string('to_status')->toString());

            $fromOrderedTaskIds = $request->input('from_ordered_task_ids', []);
            $toOrderedTaskIds = $request->input('to_ordered_task_ids', []);

            $action->execute(
                actor: $actor,
                task: $task,
                toStatus: $toStatus,
                fromOrderedTaskIds: is_array($fromOrderedTaskIds) ? $fromOrderedTaskIds : [],
                toOrderedTaskIds: is_array($toOrderedTaskIds) ? $toOrderedTaskIds : [],
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return response()->json(['ok' => true]);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}