<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Projects
            </h2>
            @if (auth()->user()->role !== 'viewer')
                <a href="{{ route('projects.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    + New Project
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

            @if ($projects->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($projects as $project)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition">
                            <div class="p-6">
                                <a href="{{ route('projects.show', $project) }}" class="block mb-3">
                                    <h3 class="text-lg font-semibold text-gray-900 hover:text-blue-600">
                                        {{ $project->name }}
                                    </h3>
                                </a>

                                @if ($project->client)
                                    <p class="text-sm text-gray-600 mb-2">
                                        <span class="text-gray-500">Client:</span> {{ $project->client->name }}
                                    </p>
                                @endif

                                <p class="text-sm text-gray-600 mb-4">
                                    <span class="text-gray-500">Created by:</span> {{ $project->creator->name }}
                                </p>

                                @if ($project->description)
                                    <p class="text-sm text-gray-700 mb-4 line-clamp-2">
                                        {{ $project->description }}
                                    </p>
                                @endif

                                <div class="flex items-center justify-between mb-4 pt-4 border-t">
                                    <span class="text-sm font-medium text-gray-700">
                                        {{ $project->tasks()->count() }} Tasks
                                    </span>
                                    <span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-800">
                                        {{ $project->status }}
                                    </span>
                                </div>

                                <div class="flex gap-2">
                                    <a href="{{ route('projects.show', $project) }}" class="flex-1 text-center px-3 py-2 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                        View
                                    </a>
                                    @if (auth()->user()->role !== 'viewer')
                                        <a href="{{ route('projects.edit', $project) }}" class="flex-1 text-center px-3 py-2 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $projects->links() }}
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        <p class="mb-4">No projects yet.</p>
                        @if (auth()->user()->role !== 'viewer')
                            <a href="{{ route('projects.create') }}" class="text-blue-600 hover:underline">
                                Create the first project
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>