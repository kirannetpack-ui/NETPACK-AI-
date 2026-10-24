{{-- resources/views/notifications/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - COURIER with NETPACK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="np-system np-legacy-screen bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-box-open text-teal-600 text-2xl mr-2"></i>
                    <span class="font-bold text-xl text-gray-800">COURIER with NETPACK</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ url('/') }}" class="text-gray-600 hover:text-teal-600">Home</a>
                    @if(auth()->check())
                        <a href="{{ route('seller.dashboard') }}" class="text-gray-600 hover:text-teal-600">Dashboard</a>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-red-600">
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-bell text-teal-600"></i>
                    <span>Notifications</span>
                </h1>
                <p class="text-gray-500 mt-1">Stay updated with your orders and payments</p>
            </div>
            <div class="flex gap-3">
                @if($notifications->where('read_at', null)->count() > 0)
                    <button onclick="markAllAsRead()" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-check-double mr-2"></i>Mark All as Read
                    </button>
                @endif
                <a href="{{ url('/') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            @forelse($notifications as $notification)
                <div class="border-b border-gray-100 hover:bg-gray-50 transition {{ $notification->read_at ? '' : 'bg-teal-50' }}">
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <!-- Icon -->
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
                                <div class="w-12 h-12 rounded-full {{ $bgColor }} flex items-center justify-center">
                                    <i class="fas {{ $icon }} text-xl"></i>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-gray-800">
                                            {{ $notification->data['title'] ?? 'Notification' }}
                                        </h3>
                                        <p class="text-gray-600 mt-1">
                                            {{ $notification->data['message'] ?? '' }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-400">
                                            {{ $notification->created_at->format('M d, Y H:i') }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons based on notification type -->
                                <div class="mt-3 flex gap-3">
                                    @if(isset($notification->data['tracking_number']))
                                        <a href="{{ route('tracking.show', $notification->data['tracking_number']) }}" 
                                           class="text-sm text-teal-600 hover:text-teal-700">
                                            <i class="fas fa-eye mr-1"></i> Track Shipment
                                        </a>
                                    @endif
                                    
                                    @if(isset($notification->data['shipment_id']))
                                        <a href="{{ route('shipments.show', $notification->data['shipment_id']) }}" 
                                           class="text-sm text-blue-600 hover:text-blue-700">
                                            <i class="fas fa-info-circle mr-1"></i> View Details
                                        </a>
                                    @endif
                                    
                                    @if(!$notification->read_at)
                                        <button onclick="markAsRead('{{ $notification->id }}')" 
                                                class="text-sm text-gray-500 hover:text-gray-700">
                                            <i class="fas fa-check mr-1"></i> Mark as Read
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <i class="fas fa-bell-slash text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Notifications</h3>
                    <p class="text-gray-500">You're all caught up! Check back later for updates.</p>
                </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        <div class="mt-6">
            {{ $notifications->links() }}
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
</body>
</html>
