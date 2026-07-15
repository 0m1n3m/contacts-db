<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $task->title }}
            </h2>
            @if (auth()->user()->role !== 'viewer' && (auth()->id() === $task->created_by || auth()->user()->role === 'admin'))
                <div class="space-x-2">
                    <a href="{{ route('tasks.edit', $task) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Edit
                    </a>
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="inline" onsubmit="return confirm('Delete this task?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="col-span-2 space-y-6">
                    <!-- Task Details -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b">
                            <h3 class="text-lg font-semibold mb-4">Details</h3>

                            <div class="space-y-4">
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <span class="text-sm text-gray-500">Status</span>
                                        <p class="font-medium mt-1">
                                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full
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
                                                {{ $task->status?->value ?? 'unknown' }}
                                            </span>
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Priority</span>
                                        <p class="font-medium mt-1">{{ $task->priority?->value ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Due Date</span>
                                        <p class="font-medium mt-1">
                                            @if ($task->due_at)
                                                <span class="{{ $task->due_at->isPast() ? 'text-red-600' : '' }}">
                                                    {{ $task->due_at->format('Y-m-d') }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <span class="text-sm text-gray-500">Project</span>
                                    <p class="font-medium mt-1">
                                        @if ($task->project)
                                            <a href="{{ route('projects.show', $task->project) }}" class="text-blue-600 hover:underline">
                                                {{ $task->project->name }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </p>
                                </div>

                                @if ($task->description)
                                    <div class="border-b">
                                        <h3 class="text-lg font-semibold mb-2">Description</h3>
                                        <p class="text-gray-700 whitespace-pre-line">{{ $task->description }}</p>
                                    </div>
                                @endif

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm text-gray-500">Created By</span>
                                        <p class="font-medium mt-1">{{ $task->creator->name ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Last Activity</span>
                                        <p class="font-medium mt-1">{{ $task->last_activity_at?->diffForHumans() ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b">
                            <h3 class="text-lg font-semibold">Comments</h3>
                        </div>
                        <div class="p-6">
                            <!-- Comment Form -->
                            <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="mb-6" id="commentForm">
                                @csrf

                                <div class="mb-4">
                                    <label for="body" class="block text-sm font-medium text-gray-700 mb-2">
                                        Add a comment
                                    </label>
                                    <textarea
                                        id="body"
                                        name="body"
                                        rows="4"
                                        placeholder="Write a comment..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    >{{ old('body') }}</textarea>
                                    @error('body')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-2 text-xs text-gray-500">💡 Tip: Use @username to mention someone</p>
                                    <p class="mt-2 text-xs text-gray-500">💡 Tip: Press Enter to send</p>
                                    <p class="mt-2 text-xs text-gray-500">💡 Tip: Press Shift+Enter for new line</p>
                                </div>

                                <button type="submit" id="submitBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                    Post Comment
                                </button>
                            </form>

                            <script>
                                document.getElementById('body').addEventListener('keydown', function(e) {
                                    // Enter sin modificadores = enviar
                                    if (e.key === 'Enter' && !e.shiftKey && !e.ctrlKey && !e.metaKey) {
                                        e.preventDefault();
                                        document.getElementById('commentForm').submit();
                                    }
                                    // Shift+Enter o Ctrl+Enter = nueva línea
                                    else if (e.key === 'Enter' && (e.shiftKey || e.ctrlKey)) {
                                        // Permitir el comportamiento por defecto (salto de línea)
                                    }
                                });
                            </script>

                            <!-- Comments List -->
                            <div class="space-y-4 border-t pt-4">
                                @forelse ($task->comments()->where('type', \App\Models\TaskComment::TYPE_USER)->orderByDesc('created_at')->get() as $comment)
                                    <div class="border rounded-lg p-4 bg-gray-50">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <p class="font-semibold text-gray-900">{{ $comment->user->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $comment->user->email }}</p>
                                            </div>
                                            <div class="flex gap-2">
                                                @if (auth()->id() === $comment->user_id || auth()->user()->role === 'admin')
                                                    <form method="POST" action="{{ route('tasks.comments.destroy', [$task, $comment]) }}" class="inline" onsubmit="return confirm('Delete this comment?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-xs text-red-600 hover:text-red-800">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                                <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>

                                        <!-- Comment body with mention highlighting -->
                                        <div class="text-gray-700 mt-3 break-words text-left">
                                            {!! \App\Helpers\TextHelper::highlightMentions($comment->body) !!}
                                        </div>

                                        <!-- Mentions -->
                                        @if ($comment->mentions()->count() > 0)
                                            <div class="mt-3 pt-3 border-t border-gray-200">
                                                <p class="text-xs text-gray-500 mb-1">Mentioned:</p>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($comment->mentions as $mention)
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">
                                                            {{ $mention->mentionedUser->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-sm">No comments yet. Be the first to comment!</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">

                    <!-- Assignments -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b">
                            <h3 class="text-lg font-semibold">Assignments</h3>
                        </div>
                        <div class="p-6">
                            @if ($task->assignments->count() > 0)
                                <div class="space-y-3">
                                    @foreach ($task->assignments as $assignment)
                                        <div class="flex flex-col gap-3 p-3 border rounded-lg bg-gray-50">
                                            <div>
                                                <p class="font-semibold text-gray-900 text-sm">{{ $assignment->user->name }}</p>
                                                @if ($assignment->accepted_at)
                                                    <p class="text-xs text-green-600 mt-1">✓ Accepted on {{ $assignment->accepted_at->format('M d, Y') }}</p>
                                                @else
                                                    <p class="text-xs text-amber-600 mt-1">⏳ Pending acceptance</p>
                                                @endif
                                            </div>

                                            <!-- Actions for current user -->
                                            @if (auth()->id() === $assignment->user_id)
                                                <!-- User can accept/reject their own assignment -->
                                                @if (!$assignment->accepted_at)
                                                    <div class="flex gap-2">
                                                        <form method="POST" action="{{ route('tasks.assignments.accept', $assignment) }}" class="inline flex-1">
                                                            @csrf
                                                            <button type="submit" class="w-full px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition">
                                                                Accept
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('tasks.assignments.reject', $assignment) }}" class="inline flex-1">
                                                            @csrf
                                                            <button type="submit" class="w-full px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition">
                                                                Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @elseif (auth()->user()->role === 'admin' || (auth()->user()->role === 'editor' && $task->created_by === auth()->id()))
                                                <!-- Admin or creator can unassign -->
                                                <form method="POST" action="{{ route('tasks.assignments.destroy', $assignment) }}" class="inline w-full" onsubmit="return confirm('Remove this assignment?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full px-3 py-1 bg-gray-400 text-white text-xs rounded hover:bg-gray-500 transition">
                                                        Remove
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-sm">No assignments yet.</p>
                            @endif
                        </div>
                    </div>



                    <!-- Attachments Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                        <div class="p-6 border-b">
                            <h3 class="text-lg font-semibold">Attachments</h3>
                        </div>
                        <div class="p-6">
                            <!-- Upload Form -->
                            <form method="POST" action="{{ route('tasks.attachments.store', $task) }}" enctype="multipart/form-data" class="mb-6">
                                @csrf

                                <div class="mb-4">
                                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                                        Upload File
                                    </label>
                                    <input
                                        type="file"
                                        id="file"
                                        name="file"
                                        required
                                        class="block w-full text-sm text-gray-500
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-lg file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-indigo-50 file:text-indigo-700
                                            hover:file:bg-indigo-100"
                                    />
                                    @error('file')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="label" class="block text-sm font-medium text-gray-700 mb-2">
                                        Label (optional)
                                    </label>
                                    <input
                                        type="text"
                                        id="label"
                                        name="label"
                                        placeholder="e.g. Design, Specs, etc."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        value="{{ old('label') }}"
                                    />
                                </div>

                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                    Upload
                                </button>
                            </form>

                            <!-- Files List -->
                            @if ($task->attachments()->count() > 0)
                                <div class="space-y-3">
                                    @foreach ($task->attachments as $attachment)
                                        <div class="flex flex-col gap-y-4 justify-between p-3 border rounded-lg bg-gray-50">
                                            <div class="flex-1">
                                                <p class="font-semibold text-gray-900">{{ $attachment->label }}</p>
                                                @if ($attachment->latestVersion)
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        {{ $attachment->latestVersion->original_name }}
                                                        • {{ number_format($attachment->latestVersion->size / 1024, 2) }} KB
                                                    </p>
                                                    <p class="text-xs text-gray-400 mt-1">
                                                        Uploaded by {{ $attachment->latestVersion->uploader->name }}
                                                        on {{ $attachment->latestVersion->created_at->format('M d, Y H:i') }}
                                                    </p>
                                                    @if ($attachment->versions()->count() > 1)
                                                        <p class="text-xs text-blue-600 mt-1">
                                                            Version {{ $attachment->latestVersion->version }} of {{ $attachment->versions()->count() }}
                                                        </p>
                                                    @endif
                                                @endif
                                            </div>

                                            <div class="flex gap-2">
                                                <a href="{{ route('tasks.attachments.download', [$task, $attachment]) }}" class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                                    Download
                                                </a>
                                                @if (auth()->id() === $attachment->created_by || auth()->user()->role === 'admin')
                                                    <form method="POST" action="{{ route('tasks.attachments.destroy', [$task, $attachment]) }}" class="inline" onsubmit="return confirm('Delete this attachment?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-sm">No attachments yet.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Time Tracking Metrics -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b">
                            <h3 class="text-lg font-semibold">Time Metrics</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Lead Time -->
                            <div class="flex justify-between items-center pb-3 border-b">
                                <span class="text-sm font-medium text-gray-700">Lead Time</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    @if ($task->completed_at)
                                        {{ number_format($task->getLeadTimeInDays(), 2) }} working days
                                    @else
                                        <span class="text-amber-600">In Progress</span>
                                    @endif
                                </span>
                            </div>

                            <!-- Dev Time -->
                            <div class="flex justify-between items-center pb-3 border-b">
                                <span class="text-sm font-medium text-gray-700">Dev Time</span>
                                <span class="text-sm font-semibold text-blue-600">
                                    {{ number_format($task->getDevTimeInDays(), 2) }} working days
                                </span>
                            </div>

                            <!-- Review Time -->
                            <div class="flex justify-between items-center pb-3 border-b">
                                <span class="text-sm font-medium text-gray-700">Review Time</span>
                                <span class="text-sm font-semibold text-yellow-600">
                                    {{ number_format($task->getReviewTimeInDays(), 2) }} working days
                                </span>
                            </div>

                            <!-- Backward Transitions -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700">Re-openings</span>
                                <span class="text-sm font-semibold 
                                    @if ($task->backward_transitions > 0)
                                        text-red-600
                                    @else
                                        text-green-600
                                    @endif
                                ">
                                    {{ $task->backward_transitions }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Dates Info -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b">
                            <h3 class="text-lg font-semibold">Dates</h3>
                        </div>
                        <div class="p-6 text-xs text-gray-500 space-y-1">
                            <p>Created: {{ $task->created_at->format('Y-m-d H:i') }}</p>
                            <p>Updated: {{ $task->updated_at->format('Y-m-d H:i') }}</p>
                            <p>Due date: {{ $task->due_at ? $task->due_at->format('Y-m-d H:i') : '—' }}</p>
                            @if ($task->completed_at)
                                <p class="text-green-600 font-semibold">Completed: {{ $task->completed_at->format('Y-m-d H:i') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>