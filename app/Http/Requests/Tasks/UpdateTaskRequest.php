<?php

namespace App\Http\Requests\Tasks;

use App\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = auth()->user()?->role ?? 'viewer';
        $task = $this->route('task');

        if ($role === 'viewer') {
            return false;
        }

        if ($role === 'editor' && $task->created_by !== auth()->id()) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        $priorities = array_map(fn ($p) => $p->value, TaskPriority::cases());

        return [
            'title' => ['nullable', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['nullable', Rule::in($priorities)],
            'due_at' => ['nullable', 'date'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ];
    }
}