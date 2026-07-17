<?php

namespace App\Actions\Tags;

use App\Models\Tag;
use Illuminate\Validation\ValidationException;

class UpdateTagAction
{
    public function execute(Tag $tag, string $name = null, string $color = null): Tag
    {
        // Validar que el nombre no exista en otro tag
        if ($name && $name !== $tag->name && Tag::where('name', $name)->exists()) {
            throw ValidationException::withMessages([
                'name' => 'El tag ya existe.',
            ]);
        }

        $tag->update([
            'name' => $name ?? $tag->name,
            'color' => $color ?? $tag->color,
        ]);

        return $tag->refresh();
    }
}