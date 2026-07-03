<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UploadTaskAttachmentAction
{
    /**
     * Upload a file attachment to a task
     */
    public function execute(
        User $actor,
        Task $task,
        UploadedFile $file,
        ?string $label = null,
    ): TaskAttachment {
        if (!$actor->can('uploadAttachment', $task)) {
            throw new AuthorizationException('You cannot upload attachments to this task.');
        }

        return DB::transaction(function () use ($actor, $task, $file, $label) {
            $attachment = TaskAttachment::firstOrCreate(
                [
                    'task_id' => $task->id,
                    'label' => $label ?? $file->getClientOriginalName(),
                ],
                [
                    'created_by' => $actor->id,
                ]
            );

            $nextVersion = $attachment->versions()->max('version') + 1;

            $path = $file->store("task-attachments/{$task->id}", 'public');
            $checksum = hash_file('sha256', $file->getRealPath());

            $attachment->versions()->create([
                'version' => $nextVersion,
                'uploaded_by' => $actor->id,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'checksum' => $checksum,
            ]);

            $task->forceFill(['last_activity_at' => now()])->save();

            // 🔔 Notificar a usuarios asignados
            foreach ($task->assignments as $assignment) {
                if ($assignment->user_id !== $actor->id) {
                    NotificationService::notifyFileUpload(
                        recipientUser: $assignment->user,
                        triggeredBy: $actor,
                        task: $task,
                        fileName: $file->getClientOriginalName(),
                    );
                }
            }

            return $attachment->refresh();
        });
    }
}