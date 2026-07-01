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
        return $this->view($user, $task);
    }

    public function uploadAttachment(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    /**
     * Coarse authorization:
     * - admin/editor: allowed (fine-grained transitions enforced in ChangeTaskStatusAction)
     * - viewer: only if assigned AND target is in_progress or in_review
     */
    public function changeStatus(User $user, Task $task, TaskStatus|string $toStatus): bool
    {
        $role = $user->role ?? 'viewer';

        if ($role === 'admin' || $role === 'editor') {
            return true;
        }

        // viewer must be assigned
        if (! $task->assignments()->where('user_id', $user->id)->exists()) {
            return false;
        }

        $to = $toStatus instanceof TaskStatus ? $toStatus->value : (string) $toStatus;

        return in_array($to, [TaskStatus::InProgress->value, TaskStatus::InReview->value], true);
    }
}