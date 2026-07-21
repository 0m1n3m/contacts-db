<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\User;

class TaskDependencyPolicy
{
    /**
     * Solo quien puede editar la tarea puede crear dependencias
     */
    public function create(User $user, Task $task): bool
    {
        return $user->can('update', $task);
    }

    /**
     * Solo quien puede editar la tarea puede eliminar dependencias
     */
    public function delete(User $user, TaskDependency $dependency): bool
    {
        return $user->can('update', $dependency->task);
    }

    /**
     * Cualquiera puede ver las dependencias de una tarea que puede ver
     */
    public function view(User $user, Task $task): bool
    {
        return $user->can('view', $task);
    }
}