<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\Tag;
use App\Models\User;

class TaskTagPolicy
{
    /**
     * Solo quien puede editar la tarea puede agregar tags
     */
    public function attachTag(User $user, Task $task): bool
    {
        // Usar la policy de Task existente
        return $user->can('update', $task);
    }

    /**
     * Solo quien puede editar la tarea puede remover tags
     */
    public function detachTag(User $user, Task $task): bool
    {
        return $user->can('update', $task);
    }
}