<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TaskAttachment extends Model
{
    protected $fillable = [
        'task_id',
        'created_by',
        'label',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(TaskAttachmentVersion::class, 'task_attachment_id')
            ->orderByDesc('version');
    }

    /**
     * Latest version by version number.
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(TaskAttachmentVersion::class, 'task_attachment_id')
            ->latestOfMany('version');
    }
}