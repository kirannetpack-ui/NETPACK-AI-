@extends('layouts.app')

@section('title', 'Available Orders')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">📦 Available Orders</h1>
            <p class="text-sm text-gray-500 mt-1">Accept orders to start delivering</p>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('info'))
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('info') }}
                </div>
            @endif

            <!-- Rider Status -->
            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-4 mb-6">
                <div>
                    <span class="text-sm text-gray-500">Your Status</span>
                    <p class="font-semibold {{ $rider->is_online ? 'text-green-600' : 'text-red-600' }}">
                        {{ $rider->is_online ? '🟢 Online' : '🔴 Offline' }}
                    </p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Active Deliveries</span>
                    <p class="font-semibold text-blue-600">
                        {{ \App\Models\Order::where('rider_id', auth()->id())->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])->count() }} / 3
                    </p>
                </div>
                @if(!$rider->is_online)
                    <a href="{{ route('rider.dashboard') }}" class="text-sm text-teal-600 hover:underline">Go Online</a>
                @endif
            </div>

            <!-- Orders List -->
            @if($orders->count() > 0)
                <div class="space-y-4">
                    @foreach($orders as $order)
                        <div class="border rounded-lg p-4 hover:shadow-md transition {{ $order->distance <= 5 ? 'border-green-300 bg-green-50' : '' }}">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <span class="font-mono text-sm font-bold">#{{ $order->order_number }}</span>
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">Pending</span>
                                        @if(isset($order->distance) && $order->distance <= 5)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                                <i class="fas fa-location-arrow mr-1"></i> Nearby
                                            </span>
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-2">
                                        <div>
                                            <p class="text-xs text-gray-500">Customer</p>
                                            <p class="font-medium">{{ $order->customer_name }}</p>
                                            <p class="text-xs text-gray-400">{{ $order->customer_phone }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Address</p>
                                            <p class="text-sm">{{ Str::limit($order->shipping_address, 30) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Amount</p>
                                            <p class="font-bold text-teal-600">Rs. {{ number_format($order->total_amount, 2) }}</p>
                                            @if(isset($order->distance))
                                                <p class="text-xs text-gray-400">
                                                    <i class="fas fa-map-marker-alt mr-1"></i> {{ $order->distance }} km away
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('rider.orders.accept', $order->id) }}" onsubmit="return confirm('Accept this order?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                                            <i class="fas fa-check mr-2"></i> Accept
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('rider.orders.reject', $order->id) }}" onsubmit="return confirm('Reject this order?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Auto-refresh -->
                <div class="mt-6 text-center text-sm text-gray-500">
                    <i class="fas fa-sync-alt animate-spin mr-2"></i>
                    Auto-refreshing every 30 seconds
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-check-circle text-5xl text-green-300 mb-4 block"></i>
                    <h3 class="text-lg font-semibold text-gray-700">No Orders Available</h3>
                    <p class="text-gray-500 mt-2">All orders have been assigned. Check back later!</p>
                    <a href="{{ route('rider.dashboard') }}" class="inline-block mt-4 text-teal-600 hover:underline">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-refresh every 30 seconds
setTimeout(function() {
    location.reload();
}, 30000);
</script>
@endpush
@endsection
