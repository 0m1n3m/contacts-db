<?php

namespace App\Actions\Tasks;

use App\Models\Tag;
use App\Models\Task;

class AddTagToTaskAction
{
    public function execute(Task $task, Tag $tag): void
    {
        // Si ya tiene el tag, no hacer nada
        if ($task->tags()->where('tag_id', $tag->id)->exists()) {
            return;
        }

        $task->tags()->attach($tag->id);
    }
}