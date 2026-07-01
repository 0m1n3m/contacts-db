<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskBoardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()?->id;

        $statuses = [
            TaskStatus::Created,
            TaskStatus::Accepted,
            TaskStatus::InProgress,
            TaskStatus::InReview,
            TaskStatus::Done,
        ];

        $columns = [];
        foreach ($statuses as $status) {
            $query = Task::query()
                ->whereNull('project_id')
                ->where('status', $status->value)
                ->orderBy('sort_order')
                ->orderBy('id');

            if ($userId) {
                $query->withExists([
                    'assignments as assigned_to_me' => fn ($q) => $q->where('user_id', $userId),
                ]);
            }

            $tasks = $query->get(['id', 'title', 'sort_order', 'status']);

            // Keep consistent shape + force boolean
            $tasks->each(function (Task $task) use ($userId) {
                $task->assigned_to_me = $userId ? (bool) $task->assigned_to_me : false;
            });

            $columns[$status->value] = $tasks->values();
        }

        return view('tasks.board', [
            'columns' => $columns,
            'auth' => ['user' => $request->user()],
        ]);
    }
}