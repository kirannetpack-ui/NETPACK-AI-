@extends('layouts.app')

@section('title', 'My Orders')
@section('page-title', 'My Deliveries')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">🛵 My Deliveries</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your active deliveries</p>
            </div>
            <a href="{{ route('rider.orders.available') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-plus mr-2"></i> Find More Orders
            </a>
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

            <!-- Active Orders -->
            <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Active Deliveries</h3>

            @if($orders->count() > 0)
                <div id="orderGpsStatus" class="mb-4 flex items-center gap-3 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-800">
                    <i class="fas fa-satellite-dish"></i>
                    <span>Starting secure GPS sharing for your active deliveries…</span>
                </div>
            @endif
            
            @if($orders->count() > 0)
                <div class="space-y-4 mb-8">
                    @foreach($orders as $order)
                        <div class="border rounded-lg p-4 {{ $order->status === 'out_for_delivery' ? 'bg-green-50 border-green-300' : '' }}">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <span class="font-mono text-sm font-bold">#{{ $order->order_number }}</span>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $order->status_badge }}">
                                            {{ $order->status_label }}
                                        </span>
                                        @if($order->status === 'out_for_delivery')
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium animate-pulse">
                                                🔴 LIVE
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
                                            <p class="text-xs text-gray-500">Delivery Fee</p>
                                            <p class="font-bold text-teal-600">Rs. {{ number_format($order->shipping_cost ?? 100, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @if($order->status === 'assigned')
                                        <form method="POST" action="{{ route('rider.orders.pickup', $order->id) }}" onsubmit="return confirm('Mark as picked up?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bg-purple-600 text-white px-3 py-2 rounded-lg hover:bg-purple-700 transition text-sm">
                                                <i class="fas fa-box mr-1"></i> Pickup
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($order->status === 'picked_up')
                                        <form method="POST" action="{{ route('rider.orders.in-transit', $order->id) }}" onsubmit="return confirm('Mark as in transit?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bg-indigo-600 text-white px-3 py-2 rounded-lg hover:bg-indigo-700 transition text-sm">
                                                <i class="fas fa-truck mr-1"></i> In Transit
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($order->status === 'in_transit')
                                        <form method="POST" action="{{ route('rider.orders.out-for-delivery', $order->id) }}" onsubmit="return confirm('Mark as out for delivery?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bg-orange-600 text-white px-3 py-2 rounded-lg hover:bg-orange-700 transition text-sm">
                                                <i class="fas fa-truck mr-1"></i> Out for Delivery
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($order->status === 'out_for_delivery')
                                        <button onclick="showDeliverModal({{ $order->id }}, '{{ $order->payment_method }}')" 
                                                class="bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 transition text-sm">
                                            <i class="fas fa-check-circle mr-1"></i> Deliver
                                        </button>
                                    @endif
                                    
                                    <a href="{{ route('rider.orders.track', $order->tracking_number) }}" 
                                       target="_blank"
                                       class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                                        <i class="fas fa-map-marker-alt mr-1"></i> Track
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="mt-3">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>📦 Assigned</span>
                                    <span>📍 Picked Up</span>
                                    <span>🚚 In Transit</span>
                                    <span>🎯 Delivered</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    @php
                                        $progress = 0;
                                        if ($order->status === 'assigned') $progress = 25;
                                        elseif ($order->status === 'picked_up') $progress = 50;
                                        elseif ($order->status === 'in_transit') $progress = 65;
                                        elseif ($order->status === 'out_for_delivery') $progress = 80;
                                        elseif ($order->status === 'delivered') $progress = 100;
                                    @endphp
                                    <div class="bg-teal-600 h-2 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-check-circle text-4xl block mb-2 text-green-400"></i>
                    <p>No active deliveries</p>
                    <a href="{{ route('rider.orders.available') }}" class="inline-block mt-2 text-teal-600 hover:underline">
                        Find available orders →
                    </a>
                </div>
            @endif

            <!-- Delivery History -->
            <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">📋 Delivery History</h3>
            
            @if($history->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Order #</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Customer</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Amount</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Delivered</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $order)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3 font-mono text-sm">#{{ $order->order_number }}</td>
                                    <td class="py-2 px-3">{{ $order->customer_name }}</td>
                                    <td class="py-2 px-3">Rs. {{ number_format($order->total_amount, 2) }}</td>
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $order->status_badge }}">
                                            {{ $order->status_label }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-sm">{{ $order->delivered_at ? $order->delivered_at->diffForHumans() : 'N/A' }}</td>
                                    <td class="py-2 px-3">
                                        <a href="{{ route('rider.orders.track', $order->tracking_number) }}" 
                                           target="_blank"
                                           class="text-blue-600 hover:text-blue-800" title="Track">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-gray-500 text-sm">
                    No delivery history yet
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Deliver Modal -->
<div id="deliverModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">🎯 Complete Delivery</h3>
                <button onclick="closeDeliverModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="deliverForm" method="POST">
                @csrf
                <div class="p-6">
                    <!-- COD Fields - Shown dynamically -->
                    <div id="codFields" class="hidden mb-4 bg-blue-50 p-3 rounded-lg border border-blue-200">
                        <p class="text-sm text-blue-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            COD collection is submitted for finance verification before any wallet payment is released.
                        </p>
                        <div class="mt-3">
                            <label class="block text-sm font-medium mb-1">COD Collected Amount <span class="text-red-500">*</span></label>
                            <input type="number" name="cod_collected_amount" step="0.01" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="Enter collected amount">
                            <p class="text-xs text-gray-500 mt-1">Amount collected from customer</p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Recipient name <span class="text-red-500">*</span></label>
                        <input type="text" name="signature" required
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                               placeholder="Recipient name">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Notes (Optional)</label>
                        <textarea name="notes" rows="2" 
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                  placeholder="Add delivery notes"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition flex-1">
                            <i class="fas fa-check mr-2"></i> Confirm Delivery & Settle
                        </button>
                        <button type="button" onclick="closeDeliverModal()" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showDeliverModal(orderId, paymentMethod) {
    const form = document.getElementById('deliverForm');
    form.action = paymentMethod === 'cod'
        ? '/rider/cod/settle/' + orderId
        : '/rider/orders/deliver/' + orderId;
    
    // Show COD fields if payment method is COD
    const codFields = document.getElementById('codFields');
    if (paymentMethod === 'cod') {
        codFields.classList.remove('hidden');
    } else {
        codFields.classList.add('hidden');
    }
    
    document.getElementById('deliverModal').classList.remove('hidden');
}

function closeDeliverModal() {
    document.getElementById('deliverModal').classList.add('hidden');
}

// Click outside to close modal
document.getElementById('deliverModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeliverModal();
    }
});

const activeOrderIds = @json($orders->pluck('id')->values());
const orderLocationUrl = @json(route('rider.orders.update-location'));
let orderLocationWatch = null;
let lastOrderLocationSentAt = 0;

function setOrderGpsStatus(message, tone = 'blue') {
    const element = document.getElementById('orderGpsStatus');
    if (!element) return;
    const tones = {
        blue: 'border-blue-200 bg-blue-50 text-blue-800',
        green: 'border-green-200 bg-green-50 text-green-800',
        amber: 'border-amber-200 bg-amber-50 text-amber-800',
        red: 'border-red-200 bg-red-50 text-red-800',
    };
    element.className = `mb-4 flex items-center gap-3 rounded-lg border p-3 text-sm ${tones[tone]}`;
    element.innerHTML = `<i class="fas fa-satellite-dish"></i><span>${message}</span>`;
}

async function shareOrderLocation(position) {
    const now = Date.now();
    if (now - lastOrderLocationSentAt < {{ max(5, config('tracking.live.refresh_seconds')) * 1000 }}) return;
    lastOrderLocationSentAt = now;

    const payload = {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude,
        accuracy: position.coords.accuracy,
        speed: position.coords.speed,
        bearing: position.coords.heading,
        altitude: position.coords.altitude,
    };

    try {
        await Promise.all(activeOrderIds.map(async orderId => {
            const response = await fetch(orderLocationUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify({...payload, order_id: orderId}),
            });
            if (!response.ok) throw new Error('Location update failed');
        }));
        setOrderGpsStatus(`Live GPS shared · accuracy ${Math.round(position.coords.accuracy)} m`, 'green');
    } catch (_) {
        setOrderGpsStatus('GPS was received, but the server update failed. Retrying automatically.', 'amber');
    }
}

if (activeOrderIds.length && navigator.geolocation) {
    orderLocationWatch = navigator.geolocation.watchPosition(
        shareOrderLocation,
        error => setOrderGpsStatus(error.code === 1 ? 'Location permission is required for customer live tracking.' : 'Waiting for a stable GPS signal…', error.code === 1 ? 'red' : 'amber'),
        {enableHighAccuracy: true, maximumAge: 10000, timeout: 15000},
    );
} else if (activeOrderIds.length) {
    setOrderGpsStatus('This device does not support browser GPS.', 'red');
}

window.addEventListener('beforeunload', () => {
    if (orderLocationWatch !== null) navigator.geolocation.clearWatch(orderLocationWatch);
});
</script>
@endpush
@endsection
