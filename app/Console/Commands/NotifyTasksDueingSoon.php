<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class NotifyTasksDueingSoon extends Command
{
    protected $signature = 'tasks:notify-due-soon';
    protected $description = 'Notify users about tasks due in less than 1 week';

    public function handle(): int
    {
        // Tareas que vencen en menos de 7 días y aún no están done
        $tasks = Task::where('status', '!=', 'done')
            ->whereBetween('due_at', [now(), now()->addDays(7)])
            ->whereNull('last_due_soon_reminded_at') // Solo notificar una vez
            ->get();

        foreach ($tasks as $task) {
            $notifiedUsers = [];

            // Notificar a asignados
            foreach ($task->assignments as $assignment) {
                if (!in_array($assignment->user_id, $notifiedUsers)) {
                    NotificationService::notifyTaskDueSoon(
                        recipientUser: $assignment->user,
                        task: $task,
                    );
                    $notifiedUsers[] = $assignment->user_id;
                }
            }

            // Notificar al creador (si no ya fue notificado)
            if (!in_array($task->created_by, $notifiedUsers)) {
                NotificationService::notifyTaskDueSoon(
                    recipientUser: $task->creator,
                    task: $task,
                );
                $notifiedUsers[] = $task->created_by;
            }

            // Marcar como notificado
            $task->update(['last_due_soon_reminded_at' => now()]);
        }

        $this->info("Notified about {$tasks->count()} tasks due soon.");

        return 0;
    }
}