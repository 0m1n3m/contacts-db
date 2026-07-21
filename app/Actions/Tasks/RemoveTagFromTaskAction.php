<?php

namespace App\Actions\Tasks;

use App\Models\Tag;
use App\Models\Task;

class RemoveTagFromTaskAction
{
    public function execute(Task $task, Tag $tag): void
    {
        $task->tags()->detach($tag->id);
    }
}