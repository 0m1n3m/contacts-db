<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\CreateTaskAction;
use App\Actions\Tasks\UpdateTaskAction;
use App\Actions\Tasks\DeleteTaskAction;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
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
        ]);

        return view('tasks.show', [
            'task' => $task,
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