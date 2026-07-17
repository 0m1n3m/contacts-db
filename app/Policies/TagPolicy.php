<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    /**
     * Solo admin/editor pueden crear tags
     */
    public function create(User $user): bool
    {
        return in_array($user->role ?? 'viewer', ['admin', 'editor']);
    }

    /**
     * Solo admin/editor pueden editar tags
     */
    public function update(User $user, Tag $tag): bool
    {
        return in_array($user->role ?? 'viewer', ['admin', 'editor']);
    }

    /**
     * Solo admin puede eliminar tags
     */
    public function delete(User $user, Tag $tag): bool
    {
        return ($user->role ?? 'viewer') === 'admin';
    }
}