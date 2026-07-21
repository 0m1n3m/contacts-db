<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tasks
            </h2>
            @if (auth()->user()->role !== 'viewer')
                <a href="{{ route('tasks.create') }}" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">
                    + Create Task
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($tasks->count() > 0)
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="px-4 py-3 text-left font-medium">Title</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-left font-medium">Priority</th>
                                <th class="px-4 py-3 text-left font-medium">Assigned To</th>
                                <th class="px-4 py-3 text-left font-medium">Project</th>
                                <th class="px-4 py-3 text-left font-medium">Due At</th>
                                <th class="px-4 py-3 text-left font-medium">Creator</th>
                                <th class="px-4 py-3 text-left font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tasks as $task)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 hover:underline">
                                            {{ $task->title }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full
                                            @if ($task->status && $task->status->value === 'done')
                                                bg-green-100 text-green-800
                                            @elseif ($task->status && $task->status->value === 'in_review')
                                                bg-yellow-100 text-yellow-800
                                            @elseif ($task->status && $task->status->value === 'in_progress')
                                                bg-blue-100 text-blue-800
                                            @else
                                                bg-gray-100 text-gray-800
                                            @endif
                                        ">
                                            {{ $task->status?->value ?? 'unknown' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs">{{ $task->priority?->value ?? '—' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if ($task->assignments->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($task->assignments as $assignment)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $assignment->user->name }}
                                                        @if ($assignment->accepted_at)
                                                            <svg class="ml-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                            </svg>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400 text-xs">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($task->project)
                                            <a href="{{ route('projects.show', $task->project) }}" class="text-blue-600 hover:underline">
                                                {{ $task->project->name }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($task->due_at)
                                            <span class="text-xs {{ $task->due_at->isPast() ? 'text-red-600' : '' }}">
                                                {{ $task->due_at->format('Y-m-d') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs">{{ $task->creator->name ?? '—' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('tasks.show', $task) }}" class="text-sm text-blue-600 hover:underline">
                                            View
                                        </a>
                                        @if (auth()->user()->role !== 'viewer' && (auth()->id() === $task->created_by || auth()->user()->role === 'admin'))
                                            · <a href="{{ route('tasks.edit', $task) }}" class="text-sm text-blue-600 hover:underline">
                                                Edit
                                            </a>
                                            · 
                                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm text-red-600 hover:underline">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="px-4 py-3 border-t">
                        {{ $tasks->links() }}
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500">
                        No tasks yet.
                        @if (auth()->user()->role !== 'viewer')
                            <a href="{{ route('tasks.create') }}" class="text-blue-600 hover:underline">
                                Create one now.
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>