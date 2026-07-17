<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\CreateTaskAction;
use App\Actions\Tasks\UpdateTaskAction;
use App\Actions\Tasks\DeleteTaskAction;
use App\Actions\Tasks\ChangeTaskStatusAction;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Models\Task;
use App\Models\Project;
use App\Models\Tag;
use App\Models\TaskDependency;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks visible to the user.
     */
    public function index(Authenticatable $user): View
    {
        $tasks = Task::visibleTo($user)
            ->with(['project', 'creator', 'assignments'])
            ->orderBy('last_activity_at', 'desc')
            ->paginate(20);

        return view('tasks.index', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(Authenticatable $user): View
    {
        if (($user->role ?? 'viewer') === 'viewer') {
            abort(403);
        }

        $projects = Project::where('status', 'active')
            ->orderBy('name')
            ->get();

        $priorities = array_map(fn ($p) => $p->value, TaskPriority::cases());
        $statuses = array_map(fn ($s) => $s->value, TaskStatus::cases());

        return view('tasks.create', [
            'projects' => $projects,
            'priorities' => $priorities,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(StoreTaskRequest $request, CreateTaskAction $action): RedirectResponse
    {
        try {
            $actor = $request->user();

            $task = $action->execute(
                actor: $actor,
                data: [
                    'title' => $request->string('title'),
                    'description' => $request->string('description', ''),
                    'project_id' => $request->integer('project_id', null) ?: null,
                    'priority' => $request->string('priority', 'normal'),
                    'due_at' => $request->date('due_at'),
                    'assignee_ids' => $request->input('assignee_ids', []),
                ],
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return redirect()->route('tasks.show', $task)
                ->with('status', 'Task created successfully.');
        } catch (AuthorizationException $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task, Authenticatable $user): View
    {
        // Check visibility
        if (!$user->can('view', $task)) {
            abort(403);
        }

        $task->load([
            'project',
            'creator',
            'assignments.user',
            'comments',
            'attachments.versions',
            'timeEntries.user',
            'tags',
            'dependencies.dependentTask',
            'subtasks.task',
        ]);

        $availableTags = Tag::all();
        $allTasks = Task::where('id', '!=', $task->id)->get();

        return view('tasks.show', [
            'task' => $task,
            'availableTags' => $availableTags,
            'allTasks' => $allTasks,
        ]);
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task, Authenticatable $user): View
    {
        // Only admin, editor (creator), or creator can edit
        if (($user->role ?? 'viewer') === 'viewer') {
            abort(403);
        }

        if (($user->role ?? 'viewer') === 'editor' && $task->created_by !== $user->id) {
            abort(403);
        }

        $projects = Project::where('status', 'active')
            ->orderBy('name')
            ->get();

        $priorities = array_map(fn ($p) => $p->value, TaskPriority::cases());

        return view('tasks.edit', [
            'task' => $task,
            'projects' => $projects,
            'priorities' => $priorities,
        ]);
    }

    /**
     * Update the specified task in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task, UpdateTaskAction $action): RedirectResponse
    {
        try {
            $actor = $request->user();

            $action->execute(
                actor: $actor,
                task: $task,
                data: [
                    'title' => $request->string('title', null),
                    'description' => $request->string('description', null),
                    'priority' => $request->string('priority', null),
                    'due_at' => $request->date('due_at'),
                    'project_id' => $request->integer('project_id', null) ?: null,
                    'assignee_ids' => $request->input('assignee_ids', []),
                ],
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return redirect()->route('tasks.show', $task)
                ->with('status', 'Task updated successfully.');
        } catch (AuthorizationException $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Change task status (from Kanban board)
     */
    public function changeStatus(Request $request, Task $task, ChangeTaskStatusAction $action): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|string|in:created,accepted,in_progress,in_review,done',
                'from_ordered_task_ids' => 'required|array',
                'to_ordered_task_ids' => 'required|array',
            ]);

            $toStatus = TaskStatus::from($request->status);

            // Usar la action para cambiar status (que notifica)
            $action->execute(
                actor: $request->user(),
                task: $task,
                toStatus: $toStatus,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            // Actualizar orden de las tareas
            $this->updateTaskOrder(
                TaskStatus::from($task->status->value),
                $request->from_ordered_task_ids
            );
            $this->updateTaskOrder(
                $toStatus,
                $request->to_ordered_task_ids
            );

            return response()->json(['success' => true]);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Move task endpoint (drag & drop within same status)
     */
    public function move(Request $request, Task $task): JsonResponse
    {
        try {
            $request->validate([
                'to_status' => 'required|string|in:created,accepted,in_progress,in_review,done',
                'from_ordered_task_ids' => 'required|array',
                'to_ordered_task_ids' => 'required|array',
            ]);

            $toStatus = TaskStatus::from($request->to_status);

            // Si el status cambió, usar changeStatus
            if ($task->status->value !== $toStatus->value) {
                $action = app(ChangeTaskStatusAction::class);
                $action->execute(
                    actor: $request->user(),
                    task: $task,
                    toStatus: $toStatus,
                    ipAddress: $request->ip(),
                    userAgent: $request->userAgent(),
                );
            }

            // Actualizar orden
            $this->updateTaskOrder(
                TaskStatus::from($task->status->value),
                $request->from_ordered_task_ids
            );

            if ($task->status->value !== $toStatus->value) {
                $this->updateTaskOrder(
                    $toStatus,
                    $request->to_ordered_task_ids
                );
            }

            return response()->json(['success' => true]);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    private function updateTaskOrder(TaskStatus $status, array $taskIds): void
    {
        foreach ($taskIds as $index => $taskId) {
            Task::where('id', $taskId)
                ->update(['sort_order' => ($index + 1) * 10]);
        }
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task, Authenticatable $user): RedirectResponse
    {
        $action = app(DeleteTaskAction::class);

        try {
            $action->execute(
                actor: $user,
                task: $task,
                ipAddress: request()->ip(),
                userAgent: request()->userAgent(),
            );

            return redirect()->route('tasks.index')
                ->with('status', 'Task deleted successfully.');
        } catch (AuthorizationException $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}