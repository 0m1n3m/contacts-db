<?php

namespace App\Actions\Tags;

use App\Models\Tag;
use Illuminate\Validation\ValidationException;

class CreateTagAction
{
    public function execute(string $name, string $color = '#3B82F6'): Tag
    {
        // Validar que el nombre no exista
        if (Tag::where('name', $name)->exists()) {
            throw ValidationException::withMessages([
                'name' => 'El tag ya existe.',
            ]);
        }

        return Tag::create([
            'name' => $name,
            'color' => $color,
        ]);
    }
}