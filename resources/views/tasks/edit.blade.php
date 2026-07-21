<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Task
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" class="mt-1 block w-full border rounded shadow-sm p-2 @error('title') border-red-500 @else border-gray-300 @enderror" value="{{ old('title', $task->title) }}">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" rows="4" class="mt-1 block w-full border rounded shadow-sm p-2 border-gray-300">{{ old('description', $task->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- <div>
                                <label class="block text-sm font-medium text-gray-700">Project</label>
                                <select name="project_id" class="mt-1 block w-full border rounded shadow-sm p-2 border-gray-300">
                                    <option value="">— None —</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id', $task->project_id) == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div> -->

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Project</label>
                                <select name="project_id" class="mt-1 block w-full border rounded shadow-sm p-2 border-gray-300">
                                    <option value="">— None —</option>
                                    @forelse ($projects as $project)
                                        <option value="{{ $project->id }}" 
                                            @if(old('project_id', $task->project_id) == $project->id) selected @endif>
                                            {{ $project->name }}
                                        </option>
                                    @empty
                                        <option disabled>No active projects</option>
                                    @endforelse
                                </select>
                                @error('project_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Priority</label>
                                <select name="priority" class="mt-1 block w-full border rounded shadow-sm p-2 border-gray-300">
                                    <option value="normal" {{ old('priority', $task->priority?->value) == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="low" {{ old('priority', $task->priority?->value) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="high" {{ old('priority', $task->priority?->value) == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="critical" {{ old('priority', $task->priority?->value) == 'critical' ? 'selected' : '' }}>Critical</option>
                                </select>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Due Date</label>
                            <input type="date" name="due_at" class="mt-1 block w-full border rounded shadow-sm p-2 border-gray-300" value="{{ old('due_at', $task->due_at ? $task->due_at->format('Y-m-d') : '') }}">
                            @error('due_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Assign to Users</label>
                            <div class="mt-2 space-y-2 border rounded p-3 bg-gray-50 max-h-48 overflow-y-auto">
                                @php
                                    $users = \App\Models\User::all();
                                    $assignedIds = $task->assignments->pluck('user_id')->toArray();
                                @endphp
                                @forelse ($users as $user)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="assignee_ids[]" value="{{ $user->id }}" 
                                            {{ in_array($user->id, $assignedIds) ? 'checked' : '' }}
                                            class="rounded border-gray-300">
                                        <span class="ml-2 text-sm text-gray-700">{{ $user->name }} ({{ $user->email }})</span>
                                    </label>
                                @empty
                                    <p class="text-sm text-gray-500">No other users available</p>
                                @endforelse
                            </div>
                            @error('assignee_ids')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('tasks.show', $task) }}" class="px-4 py-2 text-gray-700 border rounded hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">
                                Update Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>