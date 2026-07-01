<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\CommentTaskAction;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    /**
     * Store a new comment
     */
    public function store(Request $request, Task $task): RedirectResponse
    {
        \Log::info('Comment store called', [
            'user_id' => auth()->id(),
            'task_id' => $task->id,
            'body' => $request->input('body'),
        ]);

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        \Log::info('Validation passed', $validated);

        try {
            \Log::info('Calling CommentTaskAction');
            (new CommentTaskAction())->execute(
                actor: auth()->user(),
                task: $task,
                body: $validated['body'],
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            \Log::info('Comment created successfully');
            return redirect()->route('tasks.show', $task)
                ->with('success', 'Comment added.');
        } catch (\Exception $e) {
            \Log::error('Comment creation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return redirect()->route('tasks.show', $task)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a comment
     */
    public function destroy(Task $task, TaskComment $comment): RedirectResponse
    {
        // Solo el autor o admin pueden eliminar
        if (auth()->id() !== $comment->user_id && auth()->user()->role !== 'admin') {
            abort(403, 'You can only delete your own comments.');
        }

        $comment->delete();

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Comment deleted.');
    }
}