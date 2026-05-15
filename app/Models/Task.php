<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;    
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'project_id',
        'created_by',
        'title',
        'description',
        'priority',
        'status',
        'sort_order',
        'due_at',
        'last_activity_at',
        'last_due_soon_reminded_at',
    ];

    protected $casts = [
        'priority' => TaskPriority::class,
        'status' => TaskStatus::class,
        'due_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'last_due_soon_reminded_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function assignees(): BelongsToMany
    {
        // Pivot table has extra fields (accepted_at, assigned_by, timestamps)
        return $this->belongsToMany(User::class, 'task_assignments')
            ->withPivot(['accepted_at', 'assigned_by'])
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(TaskCommentMention::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function attachmentVersions()
    {
        return $this->hasManyThrough(
            TaskAttachmentVersion::class,
            TaskAttachment::class,
            'task_id',              // FK en task_attachments
            'task_attachment_id',   // FK en task_attachment_versions
            'id',                   // local key en tasks
            'id'                    // local key en task_attachments
        );
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * Visibility rules:
     * - admin: all
     * - editor: created_by OR assigned OR mentioned
     * - viewer: assigned only
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $role = $user->role ?? 'viewer';

        if ($role === 'admin') {
            return $query;
        }

        if ($role === 'viewer') {
            return $query->whereHas('assignments', function (Builder $q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // editor
        return $query->where(function (Builder $q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhereHas('assignments', function (Builder $q2) use ($user) {
                  $q2->where('user_id', $user->id);
              })
              ->orWhereHas('mentions', function (Builder $q3) use ($user) {
                  $q3->where('mentioned_user_id', $user->id);
              });
        });
    }

    /**
     * Ordering helper for Kanban:
     * Sort within a "column" (project_id + status) by sort_order.
     */
    public function scopeKanbanColumn(Builder $query, ?int $projectId, TaskStatus|string $status): Builder
    {
        $statusValue = $status instanceof TaskStatus ? $status->value : $status;

        return $query
            ->where('project_id', $projectId)
            ->where('status', $statusValue)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}