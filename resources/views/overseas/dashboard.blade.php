@extends('layouts.overseas')

@section('title', 'Overseas Gateway Hub Station - COURIER with NETPACK')
@section('page-title', 'Overseas Gateway Hub Station')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header with Global Hub Connectivity Status -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-cyan-950 via-slate-900 to-sky-950 p-6 sm:p-8 text-white border border-cyan-500/20 shadow-xl">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 uppercase tracking-widest">
                        <i class="fas fa-globe text-[9px] mr-1"></i> International Gateway Desk
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <i class="fas fa-circle-check text-[9px] mr-1 text-emerald-400"></i> Partner Verified
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    Overseas Station • {{ Auth::user()->name }}
                </h1>
                <p class="text-sm text-slate-300 max-w-xl">
                    International inbound flight verification, arrival notices, customs clearance stamps, and last-mile carrier handovers across world markets.
                </p>
            </div>

            <!-- Quick Action CTAs -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('overseas.scan') }}" 
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-gradient-to-r from-cyan-500 to-sky-600 hover:from-cyan-400 hover:to-sky-500 text-white font-bold text-sm shadow-lg shadow-cyan-900/40 transition transform hover:-translate-y-0.5">
                    <i class="fas fa-qrcode text-base"></i>
                    <span>Scan Inbound QR</span>
                </a>
                <a href="{{ route('agency.manifests.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-slate-800/90 hover:bg-slate-700 text-white font-semibold text-sm border border-slate-700 transition">
                    <i class="fas fa-plane-arrival text-base text-cyan-400"></i>
                    <span>Inbound Flight Manifests</span>
                </a>
                <a href="{{ route('tracking.page') }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-slate-800/90 hover:bg-slate-700 text-slate-200 font-semibold text-sm border border-slate-700 transition">
                    <i class="fas fa-satellite-dish text-base text-emerald-400"></i>
                    <span>Radar Map</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Core Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Inbound Shipments -->
        <a href="{{ route('overseas.shipments') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-cyan-500/40 hover:shadow-md transition block group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Inbound Shipments</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($stats['total_shipments'] ?? 0) }}</p>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium group-hover:text-cyan-600 transition">
                        View all international packages &rarr;
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-110 transition">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>
        </a>

        <!-- In-Flight / Arrived -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md hover:border-sky-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">In-Flight / Transit</p>
                    <p class="text-2xl sm:text-3xl font-black text-sky-600 mt-1">{{ number_format($stats['in_transit_shipments'] ?? 0) }}</p>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium">
                        {{ $stats['pending_shipments'] ?? 0 }} Pending arrival check
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-plane"></i>
                </div>
            </div>
        </div>

        <!-- Handed Over / Delivered -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Cleared & Delivered</p>
                    <p class="text-2xl sm:text-3xl font-black text-emerald-600 mt-1">{{ number_format($stats['delivered_shipments'] ?? 0) }}</p>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium">
                        Last-mile completed
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
        </div>

        <!-- Active Hub Gateways -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md hover:border-indigo-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Hub Gateway Routing</p>
                    <p class="text-2xl sm:text-3xl font-black text-indigo-600 mt-1">{{ $stats['active_hubs'] ?? 1 }}</p>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium">
                        Dubai • UK • Australia • NZ
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-network-wired"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('overseas.scan') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-cyan-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-cyan-100 text-cyan-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-qrcode"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Scan QR Code</p>
                <p class="text-xs text-slate-400">Mark arrival / customs</p>
            </div>
        </a>

        <a href="{{ route('agency.manifests.index') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-indigo-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-plane-arrival"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Flight Manifests</p>
                <p class="text-xs text-slate-400">Inbound arrival notices</p>
            </div>
        </a>

        <a href="{{ route('overseas.shipments') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-sky-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-list-check"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Shipment Registry</p>
                <p class="text-xs text-slate-400">All inbound cargo</p>
            </div>
        </a>

        <a href="{{ route('tracking.page') }}" target="_blank" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-emerald-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-satellite-dish"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Live Radar Map</p>
                <p class="text-xs text-slate-400">Real-time telemetry</p>
            </div>
        </a>
    </div>

    <!-- Recent Shipments Registry -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Inbound International Shipments</h3>
                <p class="text-xs text-slate-500">Packages assigned to this overseas hub station</p>
            </div>
            <a href="{{ route('overseas.shipments') }}" class="text-xs font-bold text-cyan-600 hover:underline">
                View All Shipments &rarr;
            </a>
        </div>

        <div class="p-4">
            @if(isset($recentShipments) && $recentShipments->isNotEmpty())
                <div class="divide-y divide-slate-100">
                    @foreach($recentShipments as $shipment)
                        <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/80 rounded-xl px-3 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-cyan-50 text-cyan-700 flex items-center justify-center font-bold text-xs">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-xs text-slate-900">{{ $shipment->tracking_number }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                            {{ $shipment->status === 'delivered' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                               ($shipment->status === 'in_transit' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-slate-100 text-slate-700') }}">
                                            {{ str_replace('_', ' ', $shipment->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Destination: <strong>{{ $shipment->receiver_country ?? 'Destination' }}</strong>
                                        • Consignee: {{ $shipment->receiver_name ?? 'Consignee' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 sm:text-right">
                                <a href="{{ route('tracking.show', $shipment->tracking_number) }}" 
                                   class="px-3 py-1.5 rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-700 text-xs font-bold transition">
                                    Radar
                                </a>
                                <a href="{{ route('hawb.print', ['id' => $shipment->id, 'type' => 'international']) }}" target="_blank"
                                   class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                    HAWB
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-slate-400">
                    <i class="fas fa-inbox text-3xl mb-2 text-slate-300"></i>
                    <p class="text-xs">No inbound shipments assigned to this station yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection