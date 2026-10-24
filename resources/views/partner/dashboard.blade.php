@extends('layouts.partner')

@section('title', 'Partner Hub Station - COURIER with NETPACK')
@section('page-title', 'Partner Hub Station')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Partner Welcome Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-950 via-slate-900 to-orange-950 p-6 sm:p-8 text-white border border-amber-500/20 shadow-xl">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase tracking-widest">
                        <i class="fas fa-handshake text-[9px] mr-1"></i> Regional Partner Hub
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <i class="fas fa-circle-check text-[9px] mr-1 text-emerald-400"></i> Active Dispatch Partner
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    {{ $partner->company_name ?? $partner->name }} Station
                </h1>
                <p class="text-sm text-slate-300 max-w-xl">
                    Manage regional delivery zones, verify incoming inbound manifests, scan consignment QR codes, and monitor transit SLA reminders.
                </p>
            </div>

            <!-- Quick Action CTAs -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('partner.scan') }}" 
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-400 hover:to-orange-500 text-white font-bold text-sm shadow-lg shadow-amber-900/40 transition transform hover:-translate-y-0.5">
                    <i class="fas fa-qrcode text-base"></i>
                    <span>Scan Consignment QR</span>
                </a>
                <a href="{{ route('partner.deliveries.attention') }}" 
                   class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-slate-800/90 hover:bg-slate-700 text-white font-semibold text-sm border border-slate-700 transition">
                    <i class="fas fa-triangle-exclamation text-base text-rose-400"></i>
                    <span>SLA Reminders</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Core Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Pickups / Deliveries -->
        <a href="{{ route('partner.deliveries.index') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-amber-500/40 hover:shadow-md transition block group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Assigned Deliveries</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($stats['total_pickups'] ?? 0) }}</p>
                    <div class="flex items-center gap-2 mt-2 text-[11px]">
                        <span class="text-amber-600 font-bold">{{ $stats['pending_pickups'] ?? 0 }} Pending</span> •
                        <span class="text-emerald-600 font-bold">{{ $stats['completed_pickups'] ?? 0 }} Completed</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-110 transition">
                    <i class="fas fa-truck-fast"></i>
                </div>
            </div>
        </a>

        <!-- Attention Needed (SLA Alarms) -->
        <a href="{{ route('partner.deliveries.attention') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-rose-500/40 hover:shadow-md transition block group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Attention Needed</p>
                    @php
                        $partnerAlerts = \App\Models\PickupRequest::where('partner_id', auth()->id())->where('is_delayed', true)->where('status', '!=', 'delivered')->count();
                    @endphp
                    <p class="text-2xl sm:text-3xl font-black text-rose-600 mt-1">{{ $partnerAlerts }}</p>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium group-hover:text-rose-600 transition">
                        Critical SLA deadlines &rarr;
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-110 transition">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </div>
        </a>

        <!-- Delivery Zones -->
        <a href="{{ route('partner.zones.index') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-blue-500/40 hover:shadow-md transition block group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Delivery Zones</p>
                    <p class="text-2xl sm:text-3xl font-black text-blue-600 mt-1">{{ number_format($stats['total_zones'] ?? 0) }}</p>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium group-hover:text-blue-600 transition">
                        Coverage & routing rules &rarr;
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-110 transition">
                    <i class="fas fa-map-location-dot"></i>
                </div>
            </div>
        </a>

        <!-- Regional Manifests & Inbound -->
        <a href="{{ route('domestic.manifests.index') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-teal-500/40 hover:shadow-md transition block group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Inbound Manifests</p>
                    <p class="text-2xl sm:text-3xl font-black text-teal-600 mt-1">{{ \App\Models\Manifest::countDomestic() }}</p>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium group-hover:text-teal-600 transition">
                        Arrival notice checklist &rarr;
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-110 transition">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Quick Operations Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('partner.scan') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-amber-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-barcode"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Scan Delivery</p>
                <p class="text-xs text-slate-400">Barcode & QR desk</p>
            </div>
        </a>

        <a href="{{ route('partner.deliveries.index') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-blue-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-truck-fast"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">My Deliveries</p>
                <p class="text-xs text-slate-400">Assigned pickups & drops</p>
            </div>
        </a>

        <a href="{{ route('partner.zones.index') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-indigo-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-map"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Delivery Zones</p>
                <p class="text-xs text-slate-400">Regional coverage</p>
            </div>
        </a>

        <a href="{{ route('partner.rates.index') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-emerald-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Manage Rates</p>
                <p class="text-xs text-slate-400">Zone tariffs</p>
            </div>
        </a>
    </div>

    <!-- Zones and Deliveries Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Delivery Zones Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base">Configured Delivery Zones</h3>
                <a href="{{ route('partner.zones.create') }}" class="text-xs font-bold text-amber-600 hover:underline">
                    + Add New Zone
                </a>
            </div>
            <div class="space-y-3">
                @forelse($zones ?? [] as $zone)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-xs">
                        <div>
                            <p class="font-bold text-slate-800">{{ $zone->zone_name }}</p>
                            <p class="text-[10px] text-slate-400">{{ $zone->zone_type_label ?? $zone->zone_type ?? 'Regional' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $zone->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                {{ $zone->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <a href="{{ route('partner.zones.edit', $zone->id) }}" class="text-slate-400 hover:text-slate-600">
                                <i class="fas fa-pen text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No zones configured yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Deliveries Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base">Recent Assigned Pickups</h3>
                <a href="{{ route('partner.deliveries.index') }}" class="text-xs font-bold text-amber-600 hover:underline">
                    View All &rarr;
                </a>
            </div>
            <div class="space-y-3">
                @forelse($recentPickups ?? [] as $pickup)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-xs">
                        <div>
                            <p class="font-bold text-slate-800">#{{ $pickup->id }} • {{ $pickup->pickup_location ?? 'Pickup Point' }}</p>
                            <p class="text-[10px] text-slate-400">{{ $pickup->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                            {{ $pickup->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                            {{ $pickup->status }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No recent deliveries assigned.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection