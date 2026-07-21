<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\AddTagToTaskAction;
use App\Actions\Tasks\RemoveTagFromTaskAction;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskTagController extends Controller
{
    public function attach(Request $request, Task $task, AddTagToTaskAction $action): JsonResponse|RedirectResponse
    {
        // Solo quien puede editar la tarea puede agregar tags
        if (auth()->user()->role === 'viewer' || (auth()->user()->role !== 'admin' && $task->created_by !== auth()->id())) {
            abort(403, 'You cannot modify this task.');
        }

        $validated = $request->validate([
            'tag_id' => 'required|exists:tags,id',
        ]);

        $tag = Tag::find($validated['tag_id']);
        $action->execute($task, $tag);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Etiqueta agregada');
    }

    public function detach(Request $request, Task $task, Tag $tag, RemoveTagFromTaskAction $action): JsonResponse|RedirectResponse
    {
        // Solo quien puede editar la tarea puede remover tags
        if (auth()->user()->role === 'viewer' || (auth()->user()->role !== 'admin' && $task->created_by !== auth()->id())) {
            abort(403, 'You cannot modify this task.');
        }

        $action->execute($task, $tag);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Etiqueta removida');
    }

    public function getTags(Task $task): JsonResponse
    {
        $tags = $task->tags()->get();
        return response()->json($tags);
    }
}