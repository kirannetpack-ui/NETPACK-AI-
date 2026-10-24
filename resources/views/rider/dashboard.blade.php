@extends('layouts.app')

@section('title', 'Rider workspace - COURIER with NETPACK')
@section('page-title', 'Delivery workspace')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header with Online / Offline Cockpit Toggle -->
    <div class="np-dashboard-hero relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-950 via-slate-900 to-yellow-950 p-6 sm:p-8 text-white border border-amber-500/20 shadow-xl">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase tracking-widest">
                        <i class="fas fa-motorcycle text-[9px] mr-1"></i> Fleet Navigation
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">
                        <i class="fas fa-star text-[9px] mr-1 text-yellow-400"></i> {{ number_format($rating ?? 5.0, 1) }} Rating
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    Delivery workspace • {{ $rider->name }}
                </h1>
                <p class="text-sm text-slate-300 max-w-xl">
                    Review your assigned work, update delivery progress and keep cash collections in order.
                </p>
            </div>

            <!-- Online / Offline Toggle Form & Actions -->
            <div class="flex flex-wrap items-center gap-3">
                <form method="POST" action="{{ route('rider.toggle-status') }}">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl font-bold text-sm shadow-lg transition transform hover:-translate-y-0.5 {{ $rider->is_online ? 'bg-red-500 hover:bg-red-600 text-white shadow-red-900/30' : 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-emerald-900/30' }}">
                        <span class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></span>
                        <span>{{ $rider->is_online ? 'Go Offline' : 'Go Online (Take Jobs)' }}</span>
                    </button>
                </form>

                <button onclick="updateLocation()" 
                        class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-slate-800/90 hover:bg-slate-700 text-slate-200 font-semibold text-sm border border-slate-700 transition">
                    <i class="fas fa-location-crosshairs text-amber-400"></i>
                    <span>Ping GPS</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Core Fleet Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Available Orders in Pool -->
        <a href="{{ route('rider.orders.available') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-amber-500/40 hover:shadow-md transition block group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Available Orders</p>
                    <p class="text-2xl sm:text-3xl font-black text-amber-600 mt-1">{{ $availableOrders ?? 0 }}</p>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium group-hover:text-amber-600 transition">
                        Tap to accept jobs &rarr;
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-110 transition">
                    <i class="fas fa-radar"></i>
                </div>
            </div>
        </a>

        <!-- Active Deliveries -->
        <a href="{{ route('rider.orders.my') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-blue-500/40 hover:shadow-md transition block group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active Deliveries</p>
                    <p class="text-2xl sm:text-3xl font-black text-blue-600 mt-1">{{ $activeDeliveries ?? 0 }}</p>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium group-hover:text-blue-600 transition">
                        On route for delivery &rarr;
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-110 transition">
                    <i class="fas fa-motorcycle"></i>
                </div>
            </div>
        </a>

        <!-- Today's Earnings -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Today's Earnings</p>
                    <p class="text-2xl sm:text-3xl font-black text-emerald-600 mt-1">Rs. {{ number_format($todayEarnings ?? 0, 2) }}</p>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium">
                        <i class="fas fa-wallet text-emerald-500 mr-1"></i> Balance: Rs. {{ number_format($balance ?? 0, 0) }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        <!-- Deposit Buffer / COD Clearance -->
        <a href="{{ route('rider.deposit') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-teal-500/40 hover:shadow-md transition block group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Deposit Guarantee</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Rs. {{ number_format($rider->rider_deposit_balance ?? 0, 0) }}</p>
                    <p class="text-[11px] text-teal-600 font-bold mt-2 group-hover:underline">
                        Top Up Deposit &rarr;
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-110 transition">
                    <i class="fas fa-shield-halved"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Quick Operations Fleet Nav -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('rider.orders.available') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-amber-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-box"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Find Orders</p>
                <p class="text-xs text-slate-400">{{ $availableOrders ?? 0 }} ready for pickup</p>
            </div>
        </a>

        <a href="{{ route('rider.deliveries.index') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-blue-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-truck-ramp-box"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Courier Parcels</p>
                <p class="text-xs text-slate-400">Domestic pickups & drops</p>
            </div>
        </a>

        <a href="{{ route('rider.cod.settlement-summary') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-emerald-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-hand-holding-dollar"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">COD Collections</p>
                <p class="text-xs text-slate-400">Settle cash in hand</p>
            </div>
        </a>

        <a href="{{ route('tracking.page') }}" target="_blank" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-teal-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-satellite-dish"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Live Shipment tracking</p>
                <p class="text-xs text-slate-400">Full telemetry tracking</p>
            </div>
        </a>
    </div>

    <!-- Active Tasks & Deliveries Queue -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Deliveries Queue -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Active Deliveries Queue</h3>
                    <p class="text-xs text-slate-500">Recipient contact, delivery destination, and POD verification</p>
                </div>
                <a href="{{ route('rider.orders.my') }}" class="text-xs font-bold text-amber-600 hover:text-amber-700 hover:underline">
                    View All Active &rarr;
                </a>
            </div>

            <div class="p-4">
                @if(isset($recentDeliveries) && $recentDeliveries->count() > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach($recentDeliveries as $delivery)
                            <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/80 rounded-xl px-3 transition">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm flex-shrink-0 mt-0.5">
                                        <i class="fas fa-map-pin"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-xs text-slate-900">
                                                #{{ $delivery->order->order_number ?? 'Order' }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                                {{ $delivery->status === 'delivered' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                                   ($delivery->status === 'out_for_delivery' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                                {{ str_replace('_', ' ', $delivery->status) }}
                                            </span>
                                        </div>
                                        <p class="text-xs font-semibold text-slate-800 mt-1">
                                            {{ $delivery->recipient_name }}
                                            @if($delivery->recipient_phone)
                                                • <a href="tel:{{ $delivery->recipient_phone }}" class="text-blue-600 hover:underline"><i class="fas fa-phone text-[10px]"></i> {{ $delivery->recipient_phone }}</a>
                                            @endif
                                        </p>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            <i class="fas fa-location-dot text-slate-400 text-[10px] mr-1"></i> {{ $delivery->address }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 sm:text-right">
                                    <div>
                                        <p class="font-bold font-mono text-sm text-emerald-600">Rs. {{ number_format($delivery->delivery_fee ?? 0, 2) }}</p>
                                        <p class="text-[10px] text-slate-400">Payout Fee</p>
                                    </div>
                                    <a href="{{ route('rider.orders.my') }}" 
                                       class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs transition">
                                        Update POD
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10">
                        <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400 mb-3 text-xl">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm">No Active Deliveries Right Now</h4>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-4">Open the available orders pool to accept new customer deliveries in your zone.</p>
                        <a href="{{ route('rider.orders.available') }}" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-sm transition">
                            Find Available Orders
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: GPS Beacon & Live Telemetry -->
        <div class="space-y-6">
            <!-- GPS Telemetry Card -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-950 rounded-2xl p-6 text-white border border-slate-800 shadow-md space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-amber-400 uppercase tracking-widest">GPS Telemetry</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Live Device Tracking</p>
                    <p class="text-sm font-semibold text-slate-200 mt-1">
                        @if($rider->current_latitude && $rider->current_longitude)
                            {{ number_format($rider->current_latitude, 4) }}° N, {{ number_format($rider->current_longitude, 4) }}° E
                        @else
                            Waiting for GPS Signal...
                        @endif
                    </p>
                </div>
                <div id="location-status" class="text-xs text-slate-400 pt-2 border-t border-slate-800">
                    <i class="fas fa-clock text-amber-400 mr-1"></i>
                    Last Ping: {{ $rider->last_location_update ? $rider->last_location_update->diffForHumans() : 'Standby' }}
                </div>
                <button onclick="updateLocation()" class="w-full py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-center font-bold text-xs transition shadow-md">
                    <i class="fas fa-location-crosshairs mr-1"></i> Broadcast GPS Location
                </button>
            </div>

            <!-- Fast COD Collection Alert Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-slate-900 text-sm">COD Cash In-Hand</h4>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Active Guarantee
                    </span>
                </div>
                <p class="text-xs text-slate-500">
                    Always deposit collected Cash on Delivery amounts to maintain your active balance limit.
                </p>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('rider.cod.settlement-summary') }}" class="text-xs font-bold text-amber-600 hover:underline">
                        View Settlement Summary &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const data = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };
                
                fetch('{{ route("rider.update-location") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const statusEl = document.getElementById('location-status');
                        if (statusEl) {
                            statusEl.innerHTML = '<i class="fas fa-circle-check text-emerald-400 mr-1"></i> GPS telemetry synchronized!';
                        }
                    }
                });
            },
            function(error) {
                console.warn('GPS location request error or denied:', error);
            }
        );
    }
}

// Auto ping every 45 seconds if online
@if($rider->is_online)
setInterval(updateLocation, 45000);
@endif
</script>
@endpush
@endsection