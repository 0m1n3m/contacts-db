<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskCommentMention;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class CommentTaskAction
{
    /**
     * Add a comment to a task and process mentions.
     */
    public function execute(
        User $actor,
        Task $task,
        string $body,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): TaskComment {
        if (!$actor) {
            throw new AuthorizationException('You must be logged in to comment.');
        }

        if (!$actor->can('comment', $task)) {
            throw new AuthorizationException('You cannot comment on this task.');
        }

        return DB::transaction(function () use ($actor, $task, $body) {
            // Crear comentario
            $comment = TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $actor->id,
                'type' => 'message',
                'body' => $body,
            ]);

            // Procesar menciones (@username)
            $mentions = $this->extractMentions($body);

            foreach ($mentions as $username) {
                $mentionedUser = User::whereRaw('LOWER(name) = ?', [strtolower($username)])->first();

                if ($mentionedUser) {  // Cambio aquí: sin la condición de actor
                    TaskCommentMention::create([
                        'task_comment_id' => $comment->id,
                        'task_id' => $task->id,
                        'mentioned_user_id' => $mentionedUser->id,
                    ]);

                    NotificationService::notifyMention(
                        mentionedUser: $mentionedUser,
                        triggeredBy: $actor,
                        task: $task,
                        excerpt: substr($body, 0, 100) . '...',
                    );
                }
            }

            // Actualizar last_activity_at de la tarea
            $task->forceFill(['last_activity_at' => now()])->save();

            return $comment->refresh();
        });
    }

    private function extractMentions(string $body): array
    {
        preg_match_all('/@([\w]+)/u', $body, $matches);
        return $matches[1] ?? [];
    }
}