<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAttachmentVersion extends Model
{
    protected $fillable = [
        'task_attachment_id',
        'version',
        'uploaded_by',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
        'checksum',
    ];

    protected $casts = [
        'version' => 'integer',
        'size' => 'integer',
    ];

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(TaskAttachment::class, 'task_attachment_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}