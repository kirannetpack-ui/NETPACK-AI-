{{-- resources/views/components/notifications-dropdown.blade.php --}}
<div class="relative" x-data="{ open: false }">
    <!-- Notification Bell Icon -->
    <button @click="open = !open" class="relative focus:outline-none">
        <i class="fas fa-bell text-gray-600 text-xl hover:text-teal-600 transition"></i>
        
        <!-- Notification Badge -->
        @php
            $unreadCount = auth()->user()->unreadNotifications->count();
        @endphp
        @if($unreadCount > 0)
            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>
    
    <!-- Dropdown Menu -->
    <div x-show="open" @click.away="open = false" 
         class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl z-50 max-h-96 overflow-y-auto"
         x-cloak>
        <div class="p-4 border-b">
            <div class="flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Notifications</h3>
                @if($unreadCount > 0)
                    <button onclick="markAllAsRead()" class="text-xs text-teal-600 hover:text-teal-700">
                        Mark all as read
                    </button>
                @endif
            </div>
        </div>
        
        <div class="divide-y divide-gray-100">
            @forelse(auth()->user()->notifications()->latest()->take(20)->get() as $notification)
                <div class="p-4 hover:bg-gray-50 transition {{ $notification->read_at ? 'opacity-75' : 'bg-teal-50' }}">
                    <div class="flex items-start gap-3">
                        <!-- Icon based on notification type -->
                        <div class="flex-shrink-0">
                            @php
                                $icon = match($notification->data['type'] ?? '') {
                                    'order_confirmation' => 'fa-shopping-cart',
                                    'status_update' => 'fa-truck',
                                    'payment_received' => 'fa-wallet',
                                    default => 'fa-bell'
                                };
                                $bgColor = match($notification->data['type'] ?? '') {
                                    'order_confirmation' => 'bg-blue-100 text-blue-600',
                                    'status_update' => 'bg-green-100 text-green-600',
                                    'payment_received' => 'bg-purple-100 text-purple-600',
                                    default => 'bg-gray-100 text-gray-600'
                                };
                            @endphp
                            <div class="w-10 h-10 rounded-full {{ $bgColor }} flex items-center justify-center">
                                <i class="fas {{ $icon }}"></i>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </p>
                            <p class="text-xs text-gray-600 mt-1">
                                {{ $notification->data['message'] ?? '' }}
                            </p>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-xs text-gray-400">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                                @if(!$notification->read_at)
                                    <button onclick="markAsRead('{{ $notification->id }}')" 
                                            class="text-xs text-teal-600 hover:text-teal-700">
                                        Mark as read
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <i class="fas fa-bell-slash text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No notifications yet</p>
                    <p class="text-xs text-gray-400 mt-1">We'll notify you when something arrives</p>
                </div>
            @endforelse
        </div>
        
        @if(auth()->user()->notifications()->count() > 0)
            <div class="p-3 border-t bg-gray-50">
                <a href="{{ route('notifications.index') }}" class="block text-center text-sm text-teal-600 hover:text-teal-700">
                    View all notifications →
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(() => {
        location.reload();
    });
}

function markAllAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(() => {
        location.reload();
    });
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>