<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        // Verificar permisos
        if (!$actor->can('uploadAttachment', $task)) {
            throw new AuthorizationException('You cannot upload attachments to this task.');
        }

        return DB::transaction(function () use ($actor, $task, $file, $label) {
            // Crear o obtener attachment
            $attachment = TaskAttachment::firstOrCreate(
                [
                    'task_id' => $task->id,
                    'label' => $label ?? $file->getClientOriginalName(),
                ],
                [
                    'created_by' => $actor->id,
                ]
            );

            // Obtener versión siguiente
            $nextVersion = $attachment->versions()->max('version') + 1;

            // Guardar archivo
            $path = $file->store("task-attachments/{$task->id}", 'public');
            $checksum = hash_file('sha256', $file->getRealPath());

            // Crear versión
            $attachment->versions()->create([
                'version' => $nextVersion,
                'uploaded_by' => $actor->id,
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'checksum' => $checksum,
            ]);

            // Actualizar last_activity_at
            $task->forceFill(['last_activity_at' => now()])->save();

            return $attachment->refresh();
        });
    }
}