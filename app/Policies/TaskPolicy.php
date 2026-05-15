<?php

namespace App\Policies;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        if (($user->role ?? 'viewer') === 'admin') {
            return true;
        }

        if (($user->role ?? 'viewer') === 'viewer') {
            return $task->assignments()->where('user_id', $user->id)->exists();
        }

        // editor
        return $task->created_by === $user->id
            || $task->assignments()->where('user_id', $user->id)->exists()
            || $task->mentions()->where('mentioned_user_id', $user->id)->exists();
    }

    public function comment(User $user, Task $task): bool
    {
        // Same as view for now
        return $this->view($user, $task);
    }

    public function uploadAttachment(User $user, Task $task): bool
    {
        // Viewer is allowed if assigned (handled by view())
        return $this->view($user, $task);
    }

    /**
     * Rule:
     * - admin/editor: can change to any status (you can harden transitions in Actions later)
     * - viewer: only if assigned AND target is in_progress
     *   (and we will auto-accept in Action if needed)
     */
    public function changeStatus(User $user, Task $task, TaskStatus|string $toStatus): bool
    {
        $role = $user->role ?? 'viewer';

        if ($role === 'admin' || $role === 'editor') {
            return true;
        }

        // viewer
        $to = $toStatus instanceof TaskStatus ? $toStatus->value : $toStatus;

        if ($to !== TaskStatus::InProgress->value) {
            return false;
        }

        return $task->assignments()->where('user_id', $user->id)->exists();
    }
}