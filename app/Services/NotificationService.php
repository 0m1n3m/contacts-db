<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    /**
     * Create a mention notification
     */
    public static function notifyMention(
        User $mentionedUser,
        User $triggeredBy,
        Model $task,
        string $excerpt,
    ): Notification {
        return Notification::create([
            'user_id' => $mentionedUser->id,
            'type' => 'mention',
            'title' => "{$triggeredBy->name} mentioned you",
            'message' => $excerpt,
            'action_url' => route('tasks.show', $task),
            'action_label' => 'View Task',
            'notifiable_type' => get_class($task),
            'notifiable_id' => $task->id,
            'triggered_by' => $triggeredBy->id,
        ]);
    }

    /**
     * Create an assignment notification
     */
    public static function notifyAssignment(
        User $assignedUser,
        User $triggeredBy,
        Model $task,
    ): Notification {
        return Notification::create([
            'user_id' => $assignedUser->id,
            'type' => 'assignment',
            'title' => "{$triggeredBy->name} assigned you a task",
            'message' => "Task: {$task->title}",
            'action_url' => route('tasks.show', $task),
            'action_label' => 'View Task',
            'notifiable_type' => get_class($task),
            'notifiable_id' => $task->id,
            'triggered_by' => $triggeredBy->id,
        ]);
    }

    /**
     * Create an upload notification
     */
    public static function notifyFileUpload(
        User $recipientUser,
        User $triggeredBy,
        Model $task,
        string $fileName,
    ): Notification {
        return Notification::create([
            'user_id' => $recipientUser->id,
            'type' => 'upload',
            'title' => "{$triggeredBy->name} uploaded a file",
            'message' => "File: {$fileName}",
            'action_url' => route('tasks.show', $task),
            'action_label' => 'View Attachments',
            'notifiable_type' => get_class($task),
            'notifiable_id' => $task->id,
            'triggered_by' => $triggeredBy->id,
        ]);
    }

    /**
     * Create a status change notification
     */
    public static function notifyStatusChange(
        User $recipientUser,
        User $triggeredBy,
        Model $task,
        string $oldStatus,
        string $newStatus,
    ): Notification {
        return Notification::create([
            'user_id' => $recipientUser->id,
            'type' => 'status_change',
            'title' => "{$triggeredBy->name} changed task status",
            'message' => "{$task->title}: {$oldStatus} → {$newStatus}",
            'action_url' => route('tasks.show', $task),
            'action_label' => 'View Task',
            'notifiable_type' => get_class($task),
            'notifiable_id' => $task->id,
            'triggered_by' => $triggeredBy->id,
        ]);
    }

    /**
     * Create a task due soon notification
     */
    public static function notifyTaskDueSoon(
        User $recipientUser,
        Model $task,
    ): Notification {
        $daysLeft = ceil($task->due_at->diffInDays(now()));

        return Notification::create([
            'user_id' => $recipientUser->id,
            'type' => 'task_due_soon',
            'title' => 'Task due soon',
            'message' => "{$task->title} is due in {$daysLeft} days",
            'action_url' => route('tasks.show', $task),
            'action_label' => 'View Task',
            'notifiable_type' => get_class($task),
            'notifiable_id' => $task->id,
            'triggered_by' => null, // Sistema automático
        ]);
    }
}