<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\UploadTaskAttachmentAction;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskAttachmentController extends Controller
{
    /**
     * Store a new attachment
     */
    public function store(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|max:102400', // 100MB
            'label' => 'nullable|string|max:255',
        ]);

        try {
            (new UploadTaskAttachmentAction())->execute(
                actor: auth()->user(),
                task: $task,
                file: $validated['file'],
                label: $validated['label'] ?? null,
            );

            return redirect()->route('tasks.show', $task)
                ->with('success', 'File uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()->route('tasks.show', $task)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Download a file
     */
    public function download(Task $task, TaskAttachment $attachment): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        if (!auth()->user()->can('view', $task)) {
            abort(403, 'You cannot access this file.');
        }

        $version = $attachment->latestVersion;

        return response()->download(
            storage_path("app/public/{$version->path}"),
            $version->original_name
        );
    }

    /**
     * Delete an attachment
     */
    public function destroy(Task $task, TaskAttachment $attachment): RedirectResponse
    {
        // Solo el creador o admin pueden eliminar
        if (auth()->id() !== $attachment->created_by && auth()->user()->role !== 'admin') {
            abort(403, 'You can only delete your own attachments.');
        }

        // Eliminar archivos del almacenamiento
        foreach ($attachment->versions as $version) {
            \Storage::disk($version->disk)->delete($version->path);
        }

        $attachment->delete();

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Attachment deleted.');
    }
}