<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Notifications
            </h2>
            @if (auth()->user()->notifications()->whereNull('read_at')->count() > 0)
                <form method="POST" action="{{ route('notifications.markAllAsRead') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if ($notifications->count() > 0)
                <div class="space-y-3">
                    @foreach ($notifications as $notification)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 {{ $notification->isRead() ? 'border-gray-300' : 'border-blue-600 bg-blue-50' }}">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="font-semibold text-gray-900">{{ $notification->title }}</h3>
                                        <span class="inline-block px-2 py-1 text-xs rounded {{ \App\Helpers\NotificationHelper::getBadgeClass($notification->type) }}">
                                            {{ ucfirst($notification->type) }}
                                        </span>
                                    </div>

                                    @if ($notification->message)
                                        <p class="text-sm text-gray-600">{{ $notification->message }}</p>
                                    @endif

                                    <p class="text-xs text-gray-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>

                                <div class="flex gap-2 ml-4">
                                    @if (!$notification->isRead())
                                        <form method="POST" action="{{ route('notifications.markAsRead', $notification) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                                {{ $notification->action_label }}
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ $notification->action_url }}" class="px-3 py-1 bg-gray-200 text-gray-700 text-xs rounded hover:bg-gray-300">
                                            {{ $notification->action_label }}
                                        </a>
                                    @endif

                                    <form method="POST" action="{{ route('notifications.destroy', $notification) }}" class="inline" onsubmit="return confirm('Delete?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-500">No notifications yet.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>