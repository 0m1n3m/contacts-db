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
        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        try {
            (new CommentTaskAction())->execute(
                actor: auth()->user(),
                task: $task,
                body: $validated['body'],
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return redirect()->route('tasks.show', $task)
                ->with('success', 'Comment added.');
        } catch (\Exception $e) {
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