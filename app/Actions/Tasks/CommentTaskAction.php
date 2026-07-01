<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskCommentMention;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class CommentTaskAction
{
    /**
     * Add a comment to a task and process mentions.
     * 
     * Mentions format: @username
     */
    public function execute(
        User $actor,
        Task $task,
        string $body,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): TaskComment {
        // Solo usuarios autenticados pueden comentar
        if (!$actor) {
            throw new AuthorizationException('You must be logged in to comment.');
        }

        // Verificar que el usuario pueda comentar en esta tarea
        if (!$actor->can('comment', $task)) {
            throw new AuthorizationException('You cannot comment on this task.');
        }

        return DB::transaction(function () use ($actor, $task, $body) {
            // Crear comentario
            $comment = TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $actor->id,
                'type' => TaskComment::TYPE_USER,
                'body' => $body,
            ]);

            // Procesar menciones (@username)
            $mentions = $this->extractMentions($body);
            foreach ($mentions as $username) {
                $mentionedUser = User::where('name', $username)->first();
                if ($mentionedUser && $mentionedUser->id !== $actor->id) {
                    TaskCommentMention::create([
                        'task_comment_id' => $comment->id,
                        'task_id' => $task->id,
                        'mentioned_user_id' => $mentionedUser->id,
                    ]);
                }
            }

            // Actualizar last_activity_at de la tarea
            $task->forceFill(['last_activity_at' => now()])->save();

            return $comment->refresh();
        });
    }

    private function extractMentions(string $body): array
    {
        preg_match_all('/@(\w+)/', $body, $matches);
        return $matches[1] ?? [];
    }
}