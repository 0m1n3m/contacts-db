<?php

namespace App\Http\Requests\Tasks;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        $statuses = array_map(fn ($s) => $s->value, TaskStatus::cases());

        return [
            'to_status' => ['required', Rule::in($statuses)],

            // Optional because drag events can be racy
            'from_ordered_task_ids' => ['sometimes', 'array'],
            'from_ordered_task_ids.*' => ['integer', 'distinct'],

            'to_ordered_task_ids' => ['required', 'array'],
            'to_ordered_task_ids.*' => ['integer', 'distinct'],
        ];
    }
}