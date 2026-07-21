<div class="relative" 
     x-data="{ 
        open: false, 
        count: 0,
        notifications: [],
        loading: false,
        async fetchCount() {
            try {
                let response = await fetch('{{ route("notifications.unreadCount") }}');
                let data = await response.json();
                this.count = data.count;
            } catch (error) {
                console.error('Error cargando notificaciones:', error);
            }
        },
        async loadNotifications() {
            this.loading = true;
            try {
                let response = await fetch('{{ route("api.notifications.recent") }}');
                this.notifications = await response.json();
            } catch (error) {
                console.error('Error cargando lista:', error);
                this.notifications = [];
            }
            this.loading = false;
        },
        async markAllAsRead() {
            try {
                const response = await fetch('{{ route("notifications.markAllAsReadAjax") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });
                
                if (response.ok) {
                    this.count = 0;
                    this.notifications = this.notifications.map(n => ({
                        ...n,
                        read_at: new Date().toISOString()
                    }));
                    await this.fetchCount();
                }
            } catch (error) {
                console.error('Error marcando como leído:', error);
            }
        },
        formatTime(date) {
            const now = new Date();
            const created = new Date(date);
            const diff = Math.floor((now - created) / 1000);
            
            if (diff < 60) return 'just now';
            if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
            return Math.floor(diff / 86400) + 'd ago';
        },
        getBadgeClass(type) {
            const classes = {
                'mention': 'bg-purple-100 text-purple-800',
                'assignment': 'bg-blue-100 text-blue-800',
                'upload': 'bg-green-100 text-green-800',
                'status_change': 'bg-orange-100 text-orange-800'
            };
            return classes[type] || 'bg-gray-100 text-gray-800';
        }
     }" 
     x-init="
        fetchCount();
        setInterval(() => fetchCount(), 60000);
     "
     @click.outside="open = false">
    
    <!-- Bell Button -->
    <button @click="open = !open; open && loadNotifications()" class="relative p-2 text-gray-600 hover:text-gray-900 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <!-- Badge con count -->
        <span x-show="count > 0" 
              x-transition
              class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full" 
              x-text="count">
        </span>
    </button>

    <!-- Dropdown Panel -->
    <div x-show="open" 
         x-transition
         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50">
        
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="font-semibold text-gray-900">Notifications</h3>
            <div class="flex gap-2">
                @if (auth()->user()->notifications()->whereNull('read_at')->count() > 0)
                    <button @click="markAllAsRead()" 
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Mark all as read
                    </button>
                @endif
                <a href="{{ route('notifications.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    View All
                </a>
            </div>
        </div>

        <!-- Notification List -->
        <div class="max-h-96 overflow-y-auto">
            <!-- Loading State -->
            <div x-show="loading" class="p-4 text-center text-gray-500">
                <span>Loading...</span>
            </div>

            <!-- Empty State -->
            <div x-show="!loading && notifications.length === 0" class="p-4 text-center text-gray-500">
                <span>No notifications</span>
            </div>

            <!-- Notifications with x-for -->
            <template x-for="notification in notifications" :key="notification.id">
                <button type="button"
                        @click="
                            fetch(`/notifications/${notification.id}/read`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                }
                            }).then(() => {
                                window.location.href = notification.action_url;
                            });
                        "
                        class="w-full block p-3 border-b hover:bg-gray-50 transition text-left">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="font-medium text-sm text-gray-900" x-text="notification.title"></p>
                            <p class="text-xs text-gray-600 mt-1" x-text="notification.message || ''"></p>
                            <p class="text-xs text-gray-400 mt-2" x-text="formatTime(notification.created_at)"></p>
                        </div>
                        <span class="inline-block px-2 py-1 text-xs rounded whitespace-nowrap ml-2"
                            :class="getBadgeClass(notification.type)"
                            x-text="notification.type">
                        </span>
                    </div>
                </button>
            </template>
        </div>

        <div class="p-4 border-t text-center">
            <a href="{{ route('notifications.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                Go to All Notifications
            </a>
        </div>
    </div>
</div>