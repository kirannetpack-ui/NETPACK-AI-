@extends('layouts.public')

@section('title', 'Live E-Commerce & Flash Delivery - ' . $order->tracking_number)

@push('styles')
<style>
    @keyframes rider-radar {
        0% { box-shadow: 0 0 0 0 rgba(13, 148, 136, 0.6); }
        70% { box-shadow: 0 0 0 16px rgba(13, 148, 136, 0); }
        100% { box-shadow: 0 0 0 0 rgba(13, 148, 136, 0); }
    }
    .rider-live-pulse { animation: rider-radar 2s infinite ease-in-out; }

    @keyframes live-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.85); }
    }
    .live-dot { animation: live-dot 1.5s infinite; }
    .leaflet-container img.leaflet-tile, .leaflet-container img.leaflet-marker-icon { max-width: none !important; }
</style>
@endpush

@section('content')
@php
    $service = config('tracking.services.ecommerce');
    $statusInfo = config('tracking.statuses.' . $order->status, config('tracking.statuses.pending'));
    $isFlash = str_contains(strtolower($order->special_instructions ?? ''), 'flash') || str_contains(strtolower($order->service_tier ?? ''), 'flash') || $order->is_flash ?? false;

    $milestones = [
        ['label' => 'Order Placed', 'icon' => 'fa-receipt', 'time' => $order->created_at],
        ['label' => 'Rider Assigned', 'icon' => 'fa-user-check', 'time' => $order->rider_assigned_at],
        ['label' => 'Picked Up', 'icon' => 'fa-box', 'time' => $order->picked_up_at],
        ['label' => 'Out for Delivery', 'icon' => 'fa-motorcycle', 'time' => $order->out_for_delivery_at],
        ['label' => 'Delivered', 'icon' => 'fa-circle-check', 'time' => $order->delivered_at],
    ];

    $milestoneStep = match($order->status) {
        'delivered' => 4,
        'out_for_delivery' => 3,
        'picked_up' => 2,
        'assigned' => 1,
        default => 0,
    };
@endphp

<div class="max-w-6xl mx-auto space-y-6">

    <!-- Top Action Breadcrumb Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('tracking.page') }}" class="h-9 w-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    @if($isFlash)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-500 text-white uppercase tracking-wider animate-pulse">
                            <i class="fas fa-bolt"></i> Flash Priority Live
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 uppercase tracking-wider border border-teal-200">
                            <i class="fas fa-cart-shopping"></i> E-Commerce Delivery
                        </span>
                    @endif
                    <span class="text-xs font-mono font-bold text-slate-600">ORD: {{ $order->order_number }}</span>
                </div>
                <p class="text-xs text-slate-500">Live GPS Telemetry & Last-Mile Rider Tracking</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-emerald-500 live-dot"></span>
                <span>Telemetry Connected</span>
            </span>
            <button type="button" onclick="window.print()" class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:border-slate-800 text-slate-700 hover:text-slate-900 text-xs font-semibold flex items-center gap-1.5 transition">
                <i class="fas fa-print text-slate-400"></i> <span>Print Receipt</span>
            </button>
        </div>
    </div>

    <!-- HERO MASTER CARD -->
    <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-teal-950 text-white shadow-xl border border-slate-800 p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Left Info -->
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        <span class="h-2 w-2 rounded-full bg-teal-400 animate-ping"></span>
                        LIVE LAST-MILE FULFILLMENT
                    </span>
                    <span class="rounded-xl bg-white/10 px-3.5 py-1 text-xs font-mono font-bold tracking-widest text-teal-200 border border-white/10">
                        ORDER #{{ $order->order_number }}
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-black font-mono tracking-tight text-white">
                    {{ $order->tracking_number }}
                </h1>

                <p class="text-xs text-slate-400 flex items-center gap-2">
                    <span><i class="fas fa-motorcycle text-teal-400"></i> Active On-Demand Courier</span>
                    <span>&middot;</span>
                    <span>Updated {{ $order->updated_at->diffForHumans() }}</span>
                </p>
            </div>

            <!-- Right Status Card -->
            <div class="flex items-center gap-4 bg-white/10 backdrop-blur-xl p-4 sm:p-5 rounded-2xl border border-white/15">
                <div class="h-14 w-14 rounded-2xl bg-teal-500/20 border border-teal-500/30 flex items-center justify-center text-teal-300 text-2xl shrink-0 rider-live-pulse">
                    <i class="fas {{ $statusInfo['icon'] }}"></i>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-widest text-teal-300/80">Fulfillment Status</span>
                    <h3 class="text-xl font-extrabold text-white">
                        {{ $statusInfo['label'] }}
                    </h3>
                    <p class="text-xs text-slate-300 mt-0.5 max-w-xs leading-tight">
                        {{ $statusInfo['description'] }}
                    </p>
                </div>
            </div>
        </div>

        <!-- 5-STAGE MILESTONE STEPPER -->
        <div class="mt-8 pt-6 border-t border-white/10">
            <div class="grid grid-cols-5 gap-2 text-center">
                @foreach($milestones as $idx => $m)
                    @php 
                        $isDone = $idx <= $milestoneStep; 
                        $isCurrent = $idx === $milestoneStep; 
                    @endphp
                    <div class="flex flex-col items-center group">
                        <span class="h-10 w-10 sm:h-12 sm:w-12 rounded-2xl flex items-center justify-center text-sm font-bold transition transform group-hover:scale-105 {{ $isDone ? 'bg-gradient-to-tr from-teal-500 to-teal-400 text-slate-950 shadow-lg shadow-teal-500/30' : 'bg-white/10 text-white/40 border border-white/10' }} {{ $isCurrent ? 'ring-4 ring-teal-400/40' : '' }}">
                            <i class="fas {{ $m['icon'] }}"></i>
                        </span>
                        <span class="mt-2 text-xs font-bold {{ $isDone ? 'text-white' : 'text-slate-500' }}">
                            {{ $m['label'] }}
                        </span>
                        <span class="text-[10px] text-slate-400 hidden sm:block mt-0.5 font-mono">
                            {{ $m['time'] ? $m['time']->format('H:i') : 'Pending' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- LIVE MAP & DISPATCH TELEMETRY SECTION -->
    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <!-- Live Map Card -->
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-5 border-b border-slate-100 bg-slate-50/50">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-teal-700">Real-Time GPS Telemetry</span>
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-satellite-dish text-teal-600"></i> Live Rider Navigation
                    </h2>
                </div>

                <div class="flex items-center gap-2">
                    <span id="liveSignalBadge" class="rounded-full bg-emerald-100 border border-emerald-300 px-3 py-1 text-xs font-bold text-emerald-800 flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Signal Active</span>
                    </span>
                </div>
            </div>

            <!-- Interactive Map Container -->
            <div class="relative">
                <div id="liveOrderMap" class="h-[440px] w-full bg-slate-100"></div>

                <!-- Floating Live ETA Overlay -->
                <div class="absolute top-4 left-4 z-[400] bg-white/95 backdrop-blur-md px-4 py-2.5 rounded-2xl shadow-lg border border-slate-200/80 flex items-center gap-3">
                    <span class="h-10 w-10 rounded-xl bg-teal-600 text-white flex items-center justify-center text-lg shadow-sm">
                        <i class="fas fa-motorcycle"></i>
                    </span>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Estimated Arrival</span>
                        <span id="liveEtaText" class="text-sm font-black text-slate-900">
                            {{ $order->status === 'delivered' ? 'Completed & Handed Over' : ($order->status === 'out_for_delivery' ? 'Arriving in ~12–18 mins' : 'Preparing for rider dispatch') }}
                        </span>
                    </div>
                </div>

                @if(!$canViewLive)
                    <!-- Privacy-Protected Safe Notice Banner -->
                    <div class="absolute bottom-4 left-4 right-4 z-[400] bg-slate-900/90 backdrop-blur-md p-4 rounded-2xl border border-white/20 text-white shadow-xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-3">
                            <span class="h-9 w-9 shrink-0 rounded-xl bg-teal-500/20 border border-teal-500/30 text-teal-300 flex items-center justify-center text-base">
                                <i class="fas fa-shield-halved"></i>
                            </span>
                            <div>
                                <p class="font-bold text-white">Live location is privacy protected</p>
                                <p class="text-[11px] text-slate-300">Milestones and delivery progress are shown. Sign in to view exact live rider GPS coordinates.</p>
                            </div>
                        </div>
                        <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs shrink-0 transition">
                            Sign In to View GPS &rarr;
                        </a>
                    </div>
                @endif
            </div>

            <!-- Telemetry Footer Status -->
            <div class="grid grid-cols-3 divide-x divide-slate-100 border-t border-slate-100 p-4 text-center bg-white text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">Service Tier</span>
                    <span class="font-bold text-slate-800 mt-0.5 block">
                        {{ $isFlash ? '⚡ Flash Priority (1-2 Hr)' : 'Standard E-Commerce' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">Telemetry Refresh</span>
                    <span class="font-bold text-teal-700 mt-0.5 block">
                        Auto-Polling (15s)
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">COD Payment</span>
                    <span class="font-bold text-slate-800 mt-0.5 block">
                        {{ strtoupper($order->payment_method ?? 'CASH ON DELIVERY') }}
                    </span>
                </div>
            </div>
        </section>

        <!-- Sidebar Details & Financials -->
        <aside class="space-y-6">
            <!-- Rider Information Card -->
            <section class="bg-white rounded-3xl p-6 border border-slate-200 shadow-2xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-helmet-safety text-teal-600"></i> Assigned Delivery Rider
                    </h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Verified
                    </span>
                </div>

                <div class="mt-4 flex items-center gap-3.5">
                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-tr from-teal-700 to-teal-500 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-teal-700/20 shrink-0">
                        {{ strtoupper(substr($order->rider->name ?? 'NETPACK Rider', 0, 2)) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">
                            {{ $order->rider->name ?? 'NETPACK Express Rider' }}
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Vehicle: <strong class="text-slate-700 font-mono">Ba 92 Pa 4412</strong>
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="tel:+97715970123" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs text-center transition flex items-center justify-center gap-1.5">
                        <i class="fas fa-phone text-slate-500"></i> Call Rider
                    </a>
                    <a href="https://wa.me/97715970123" target="_blank" class="py-2 px-3 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold text-xs text-center transition flex items-center justify-center gap-1.5">
                        <i class="fab fa-whatsapp"></i> Chat Support
                    </a>
                </div>
            </section>

            <!-- COD Accounting Card -->
            <section class="bg-white rounded-3xl p-6 border border-slate-200 shadow-2xs">
                <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100">
                    Payment & Settlement
                </h3>

                <dl class="divide-y divide-slate-100 text-xs mt-2">
                    <div class="py-2.5 flex justify-between items-center">
                        <dt class="text-slate-500">COD Amount to Collect</dt>
                        <dd class="font-mono font-black text-slate-900 text-sm">
                            NPR {{ number_format($order->total_amount ?? $order->grand_total ?? 0, 2) }}
                        </dd>
                    </div>

                    <div class="py-2.5 flex justify-between items-center">
                        <dt class="text-slate-500">Payment Method</dt>
                        <dd class="font-semibold text-slate-800">
                            {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'cash_on_delivery')) }}
                        </dd>
                    </div>

                    <div class="py-2.5 flex justify-between items-center">
                        <dt class="text-slate-500">Collection Status</dt>
                        <dd>
                            @if($order->payment_status === 'paid')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    <i class="fas fa-check-circle mr-1"></i> Paid & Reconciled
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                    <i class="fas fa-clock mr-1"></i> Collect on Handover
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>

                <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-500 leading-relaxed">
                    Customer address, phone, payment details, and exact coordinates never appear publicly.
                </div>
            </section>
        </aside>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const defaultKathmandu = [27.7172, 85.3240];
    const map = L.map('liveOrderMap', {
        zoomControl: true,
        scrollWheelZoom: false,
    }).setView(defaultKathmandu, 13);

    // Clean modern OpenStreetMap Voyager-style tiles
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap &copy; CARTO'
    }).addTo(map);

    // Custom Rider Motorcycle Icon with radar wave
    const riderPulseIcon = L.divIcon({
        className: 'custom-rider-marker',
        html: `
            <div style="position: relative; width: 38px; height: 38px; display: flex; items-align: center; justify-content: center;">
                <div class="rider-live-pulse" style="position: absolute; inset: 0; border-radius: 999px; background: rgba(13, 148, 136, 0.45);"></div>
                <div style="position: relative; width: 34px; height: 34px; border-radius: 999px; background: #0d9488; border: 3px solid #ffffff; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
                    <i class="fas fa-motorcycle"></i>
                </div>
            </div>
        `,
        iconSize: [38, 38],
        iconAnchor: [19, 19]
    });

    const destinationIcon = L.divIcon({
        className: 'custom-dest-marker',
        html: `
            <div style="width: 32px; height: 32px; border-radius: 999px; background: #0f172a; border: 3px solid #ffffff; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
                <i class="fas fa-flag-checkered"></i>
            </div>
        `,
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    });

    // Simulated waypoint coordinates around Kathmandu if not yet set
    const riderLat = @json($order->delivery_latitude ? $order->delivery_latitude + 0.005 : 27.7120);
    const riderLng = @json($order->delivery_longitude ? $order->delivery_longitude - 0.008 : 85.3180);
    const destLat = @json($order->delivery_latitude ?? 27.7172);
    const destLng = @json($order->delivery_longitude ?? 85.3240);

    const riderPoint = [riderLat, riderLng];
    const destPoint = [destLat, destLng];

    const riderMarker = L.marker(riderPoint, { icon: riderPulseIcon }).addTo(map).bindTooltip('⚡ Live Express Rider', { permanent: true, direction: 'top', offset: [0, -16] });
    const destMarker = L.marker(destPoint, { icon: destinationIcon }).addTo(map).bindTooltip('Delivery Destination', { permanent: false, direction: 'bottom' });

    // Polyline connecting route
    const routeLine = L.polyline([riderPoint, destPoint], {
        color: '#0d9488',
        weight: 4,
        dashArray: '8, 8',
        opacity: 0.8
    }).addTo(map);

    map.fitBounds([riderPoint, destPoint], { padding: [60, 60], maxZoom: 15 });

    // Real-time polling for authorized users
    @if($canViewLive)
    async function pollRiderGps() {
        try {
            const res = await fetch(@json(route('tracking.orders.live', $order)), { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const payload = await res.json();
            if (payload.data && payload.data.latitude && payload.data.longitude) {
                const newPoint = [payload.data.latitude, payload.data.longitude];
                riderMarker.setLatLng(newPoint);
                routeLine.setLatLngs([newPoint, destPoint]);
            }
        } catch (e) {
            console.warn('GPS Telemetry polling sync error:', e);
        }
    }
    setInterval(pollRiderGps, 15000);
    @endif
});
</script>
@endpush
@endsection
