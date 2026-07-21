<!-- Comments Section -->
<div class="mt-8 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Comments</h3>

    <!-- Comment Form -->
    <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="mb-6">
        @csrf
        
        <div class="mb-4">
            <label for="body" class="block text-sm font-medium text-gray-700 mb-2">
                Add a comment
            </label>
            <textarea
                id="body"
                name="body"
                rows="4"
                placeholder="Write a comment... Use @username to mention someone"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >{{ old('body') }}</textarea>
            @error('body')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-gray-500">💡 Tip: Use @username to mention someone</p>
        </div>

        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            Post Comment
        </button>
    </form>

    <!-- Comments List -->
    <div class="space-y-4">
        @forelse ($task->comments()->orderByDesc('created_at')->get() as $comment)
            <div class="border rounded-lg p-4 bg-gray-50">
                @if ($comment->type === 'system')
                    <!-- System Comment -->
                    <div class="text-sm text-gray-600 italic">
                        {{ $comment->body }}
                    </div>
                    <p class="text-xs text-gray-400 mt-2">{{ $comment->created_at->diffForHumans() }}</p>
                @else
                    <!-- User Comment -->
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
                    <div class="text-gray-700 mt-3 whitespace-pre-wrap break-words">
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
                @endif
            </div>
        @empty
            <p class="text-gray-500 text-sm">No comments yet. Be the first to comment!</p>
        @endforelse
    </div>
</div>