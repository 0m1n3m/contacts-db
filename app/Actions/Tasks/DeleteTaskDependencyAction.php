<?php

namespace App\Actions\Tasks;

use App\Models\TaskDependency;

class DeleteTaskDependencyAction
{
    public function execute(TaskDependency $dependency): void
    {
        $dependency->delete();
    }
}