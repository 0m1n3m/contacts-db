<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\TaskDependency;
use RuntimeException;

class CreateTaskDependencyAction
{
    public function execute(
        Task $task,
        Task $dependentTask,
        string $type = 'depends_on'
    ): TaskDependency {
        // Validar que no sea la misma tarea
        if ($task->id === $dependentTask->id) {
            throw new RuntimeException('Una tarea no puede depender de sí misma.');
        }

        // Validar que no exista ya la dependencia
        if (TaskDependency::where('task_id', $task->id)
            ->where('dependent_task_id', $dependentTask->id)
            ->where('type', $type)
            ->exists()) {
            throw new RuntimeException('Esta dependencia ya existe.');
        }

        // Validar que no haya ciclo (dependencia circular)
        $this->validateNoCycle($task, $dependentTask);

        return TaskDependency::create([
            'task_id' => $task->id,
            'dependent_task_id' => $dependentTask->id,
            'type' => $type,
        ]);
    }

    private function validateNoCycle(Task $task, Task $dependentTask): void
    {
        // Si dependentTask depende de task, habría ciclo
        $hasCycle = TaskDependency::where('task_id', $dependentTask->id)
            ->where('dependent_task_id', $task->id)
            ->exists();

        if ($hasCycle) {
            throw new RuntimeException('Esto crearía una dependencia circular.');
        }

        // Verificar dependencias indirectas
        foreach ($dependentTask->dependencies as $dep) {
            if ($dep->dependent_task_id === $task->id) {
                throw new RuntimeException('Esto crearía una dependencia circular.');
            }
        }
    }
}