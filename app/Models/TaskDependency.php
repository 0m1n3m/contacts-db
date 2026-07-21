<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDependency extends Model
{
    protected $table = 'task_dependencies';
    
    protected $fillable = ['task_id', 'dependent_task_id', 'type'];

    protected $casts = [
        'type' => 'string',
    ];

    /**
     * Tarea que tiene la dependencia
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Tarea de la que depende
     */
    public function dependentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'dependent_task_id');
    }
}