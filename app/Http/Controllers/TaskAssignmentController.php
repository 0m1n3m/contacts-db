<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\ChangeTaskStatusAction;
use App\Enums\TaskStatus;
use App\Models\TaskAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;

class TaskAssignmentController extends Controller
{
    /**
     * Accept a task assignment
     */
    public function accept(TaskAssignment $assignment): RedirectResponse
    {
        if (auth()->id() !== $assignment->user_id) {
            abort(403, 'You can only accept your own assignments.');
        }

        $task = $assignment->task;

        DB::transaction(function () use ($assignment, $task) {
            $assignment->update(['accepted_at' => now()]);

            if ($task->status->value === 'created') {
                (new ChangeTaskStatusAction())->execute(
                    actor: auth()->user(),
                    task: $task,
                    toStatus: TaskStatus::Accepted,
                    ipAddress: request()->ip(),
                    userAgent: request()->userAgent(),
                );
            }
        });

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Assignment accepted.');
    }

    /**
     * Reject a task assignment
     */
    public function reject(TaskAssignment $assignment): RedirectResponse
    {
        // Solo el usuario asignado puede rechazar
        if (auth()->id() !== $assignment->user_id) {
            abort(403, 'You can only accept your own assignments.');
        }

        DB::transaction(function () use ($assignment) {
            $task = $assignment->task;
            $assignment->delete();
            $task->forceFill(['last_activity_at' => now()])->save();
        });

        return redirect()->route('tasks.show', $assignment->task)
            ->with('success', 'Assignment rejected.');
    }

    /**
     * Remove a user from a task (admin/creator only)
     */
    public function destroy(TaskAssignment $assignment): RedirectResponse
    {
        $task = $assignment->task;
        $actor = auth()->user();

        // Solo admin o el creador de la tarea pueden desasignar
        if ($actor->role !== 'admin' && $task->created_by !== $actor->id) {
            abort(403, 'You cannot unassign users from this task.');
        }

        DB::transaction(function () use ($assignment) {
            $task = $assignment->task;
            $assignment->delete();
            $task->forceFill(['last_activity_at' => now()])->save();
        });

        return redirect()->route('tasks.show', $task)
            ->with('success', 'User unassigned from task.');
    }
}