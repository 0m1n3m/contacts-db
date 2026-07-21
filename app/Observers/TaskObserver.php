<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\TimeTrackingService;

class TaskObserver
{
    public function __construct(private TimeTrackingService $timeTrackingService)
    {
    }

    /**
     * Se dispara cuando el modelo es actualizado
     */
    public function updating(Task $task): void
    {
        // Solo procesar si el estado cambió
        if ($task->isDirty('status')) {
            $this->timeTrackingService->recordStateChange(
                $task,
                $task->status
            );
        }
    }
}