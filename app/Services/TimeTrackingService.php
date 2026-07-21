<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Helpers\WorkingTimeHelper;
use App\Models\AuditLog;
use App\Models\Task;
use Carbon\Carbon;

class TimeTrackingService
{
    private const DEV_STATES = ['in_progress'];
    private const REVIEW_STATES = ['in_review'];
    private const STATUS_ORDER = [
        'created' => 0,
        'accepted' => 1,
        'in_progress' => 2,
        'in_review' => 3,
        'done' => 4,
    ];

    /**
     * Registrar cambio de estado y actualizar tiempos
     */
    public function recordStateChange(Task $task, TaskStatus|string $newStatus, ?int $actorId = null): void
    {
        $newStatusValue = $newStatus instanceof TaskStatus ? $newStatus->value : $newStatus;
        $oldStatus = $task->getOriginal('status');
        $oldStatusValue = $oldStatus instanceof TaskStatus ? $oldStatus->value : $oldStatus;

        // Registrar en AuditLog (Esto está bien porque es otro modelo/tabla)
        $this->logStateChange($task, $oldStatusValue, $newStatusValue, $actorId);

        // Contar transiciones hacia atrás (Cambiamos increment() por asignación en memoria)
        if ($this->isBackwardTransition($oldStatusValue, $newStatusValue)) {
            $task->backward_transitions = $task->backward_transitions + 1;
        }

        // Actualizar tiempos acumulados del estado anterior
        $this->updateAccumulatedTime($task, $oldStatusValue, now());

        // Si llegó a 'done', calcular lead_time (Cambiamos update() por asignación en memoria)
        if ($newStatusValue === 'done') {
            $task->completed_at = now();
            $task->lead_time = WorkingTimeHelper::getWorkingSecondsPrecise($task->created_at, now());
        }

        // Actualizar timestamp del cambio de estado (Cambiamos update() por asignación en memoria)
        $task->entered_current_status_at = now();

        // ¡OJO! NO LLAMAR A $task->save() NI $task->update() AQUÍ.
        // Como estamos en el evento 'updating', Laravel guardará todo esto automáticamente.
    }

    /**
     * Registrar en AuditLog
     */
    private function logStateChange(Task $task, string $oldStatus, string $newStatus, ?int $actorId = null): void
    {
        AuditLog::create([
            'actor_id' => $actorId ?? auth()->id(),
            'project_id' => $task->project_id,
            'entity_type' => Task::class,
            'entity_id' => $task->id,
            'action' => 'status_changed',
            'before' => ['status' => $oldStatus],
            'after' => ['status' => $newStatus],
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }

    /**
     * Detectar si es una transición hacia atrás
     */
    private function isBackwardTransition(string $oldStatus, string $newStatus): bool
    {
        $oldOrder = self::STATUS_ORDER[$oldStatus] ?? -1;
        $newOrder = self::STATUS_ORDER[$newStatus] ?? -1;
        return $newOrder < $oldOrder;
    }

    /**
     * Actualizar tiempo acumulado en el estado anterior
     */
    private function updateAccumulatedTime(Task $task, string $previousStatus, Carbon $transitionTime): void
    {
        if (!$task->entered_current_status_at) {
            return;
        }

        $elapsedSeconds = WorkingTimeHelper::getWorkingSecondsPrecise(
            $task->entered_current_status_at,
            $transitionTime
        );

        if (in_array($previousStatus, self::DEV_STATES)) {
            $task->dev_time += $elapsedSeconds;  // Asignación en memoria
        }

        if (in_array($previousStatus, self::REVIEW_STATES)) {
            $task->review_time += $elapsedSeconds;  // Asignación en memoria
        }
    }

    /**
     * Recalcular todos los tiempos desde cero basándose en el historial
     */
    public function recalculateAllTimesForTask(Task $task): void
    {
        // Usar withoutEvents para evitar que cualquier acción aquí despierte al observer
        Task::withoutEvents(function () use ($task) {
            // 1. En lugar de hacer un ->update() en la BD, reseteamos las propiedades en memoria
            $task->lead_time = 0;
            $task->dev_time = 0;
            $task->review_time = 0;
            $task->backward_transitions = 0;
            $task->completed_at = null;

            // Obtener todos los cambios de estado ordenados por fecha
            $changes = $task->statusChanges()->get();

            if ($changes->isEmpty()) {
                return;
            }

            foreach ($changes as $index => $change) {
                $nextChange = $changes->get($index + 1);
                
                if (!$nextChange) {
                    continue;
                }

                // El estado en el que se quedó la tarea en este punto del historial
                $currentStatus = $change->after['status']; 
                
                // El estado al que pasó en el siguiente movimiento
                $nextStatus = $nextChange->after['status']; 

                // Usar WorkingTimeHelper
                $elapsedSeconds = WorkingTimeHelper::getWorkingSecondsPrecise(
                    $change->created_at,
                    $nextChange->created_at
                );

                // Acumular tiempo según el estado
                if (in_array($currentStatus, self::DEV_STATES)) {
                    $task->dev_time += $elapsedSeconds;
                }

                if (in_array($currentStatus, self::REVIEW_STATES)) {
                    $task->review_time += $elapsedSeconds;
                }

                // CORRECCIÓN: Evaluamos si el salto al SIGUIENTE estado fue un retroceso
                if ($this->isBackwardTransition($currentStatus, $nextStatus)) {
                    $task->backward_transitions += 1;
                }
            }

            // 2. Calcular lead_time si el último estado registrado fue 'done'
            $lastChange = $changes->last();
            if ($lastChange && $lastChange->after['status'] === 'done') {
                $firstChange = $changes->first();

                $task->lead_time = WorkingTimeHelper::getWorkingSecondsPrecise(
                    $firstChange->created_at,
                    $lastChange->created_at
                );
                $task->completed_at = $lastChange->created_at;
            }

            // 3. UNA SOLA QUERY: Guardamos todo el estado recalculado de golpe
            $task->save();
        });
    }
}