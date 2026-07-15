<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->name }}
            </h2>
            @if (auth()->user()->role !== 'viewer')
                <div class="space-x-2">
                    <a href="{{ route('projects.edit', $project) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Edit
                    </a>
                    @if ($project->tasks()->count() == 0)
                        <form method="POST" action="{{ route('projects.destroy', $project) }}" class="inline" onsubmit="return confirm('Delete this project?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                Delete
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Project Info -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Tasks</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_tasks'] }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Completed</h3>
                        <p class="text-3xl font-bold text-green-600">{{ $stats['completed_tasks'] }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">In Progress</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ $stats['in_progress_tasks'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Project Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Project Details</h3>
                    
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Client:</span>
                            <p class="font-medium">{{ $project->client?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Status:</span>
                            <p class="font-medium">
                                <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full
                                    @if ($project->status === 'active')
                                        bg-green-100 text-green-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif
                                ">
                                    {{ $project->status }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-500">Created by:</span>
                            <p class="font-medium">{{ $project->creator->name }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Created:</span>
                            <p class="font-medium">{{ $project->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>

                    @if ($project->description)
                        <div class="mt-4 pt-4 border-t">
                            <span class="text-gray-500 text-sm">Description:</span>
                            <p class="text-gray-700 mt-2">{{ $project->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tasks List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Tasks</h3>
                    @if (auth()->user()->role !== 'viewer')
                        <a href="{{ route('tasks.create') }}?project_id={{ $project->id }}" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                            + Add Task
                        </a>
                    @endif
                </div>

                @if ($project->tasks()->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($project->tasks as $task)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $task->title }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                                @if ($task->status?->value === 'done')
                                                    bg-green-100 text-green-800
                                                @elseif ($task->status?->value === 'in_review')
                                                    bg-yellow-100 text-yellow-800
                                                @elseif ($task->status?->value === 'in_progress')
                                                    bg-blue-100 text-blue-800
                                                @else
                                                    bg-gray-100 text-gray-800
                                                @endif
                                            ">
                                                {{ $task->status?->value ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">{{ $task->priority?->value ?? '—' }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            @if ($task->due_at)
                                                <span class="{{ $task->due_at->isPast() ? 'text-red-600 font-semibold' : '' }}">
                                                    {{ $task->due_at->format('M d, Y') }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 hover:underline">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500">
                        No tasks yet. 
                        @if (auth()->user()->role !== 'viewer')
                            <a href="{{ route('tasks.create') }}?project_id={{ $project->id }}" class="text-blue-600 hover:underline">
                                Create one now
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>