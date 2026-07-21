<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\CreateTaskDependencyAction;
use App\Actions\Tasks\DeleteTaskDependencyAction;
use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskDependencyController extends Controller
{
    public function store(Request $request, Task $task, CreateTaskDependencyAction $action): JsonResponse|RedirectResponse
    {
        // Solo quien puede editar la tarea puede crear dependencias
        if (auth()->user()->role === 'viewer' || (auth()->user()->role !== 'admin' && $task->created_by !== auth()->id())) {
            abort(403, 'You cannot modify this task.');
        }

        $validated = $request->validate([
            'dependent_task_id' => 'required|exists:tasks,id',
            'type' => 'nullable|in:depends_on,blocks,relates_to',
        ]);

        $dependentTask = Task::find($validated['dependent_task_id']);

        try {
            $dependency = $action->execute(
                task: $task,
                dependentTask: $dependentTask,
                type: $validated['type'] ?? 'depends_on',
            );
        } catch (\RuntimeException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return redirect()->route('tasks.show', $task)->withErrors(['error' => $e->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json($dependency, 201);
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Dependencia agregada');
    }

    public function destroy(Request $request, Task $task, TaskDependency $dependency): JsonResponse|RedirectResponse
    {
        // Validar que la dependencia pertenece a esta tarea
        if ($dependency->task_id !== $task->id) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'No autorizado'], 403);
            }
            abort(403, 'Unauthorized');
        }

        // Solo quien puede editar la tarea puede eliminar dependencias
        if (auth()->user()->role === 'viewer' || (auth()->user()->role !== 'admin' && $task->created_by !== auth()->id())) {
            abort(403, 'You cannot modify this task.');
        }

        (new DeleteTaskDependencyAction())->execute($dependency);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Dependencia eliminada');
    }

    public function getDependencies(Task $task): JsonResponse
    {
        $dependencies = $task->dependencies()
            ->with('dependentTask')
            ->get();

        $subtasks = $task->subtasks()
            ->with('task')
            ->get();

        return response()->json([
            'dependencies' => $dependencies,
            'subtasks' => $subtasks,
        ]);
    }
}