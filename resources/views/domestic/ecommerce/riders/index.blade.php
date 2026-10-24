@extends('layouts.app')

@section('title', 'Direct Rider Network & KYC Verification - E-Commerce Delivery')

@section('content')
<div class="space-y-6">
    <!-- Header Hero Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 p-6 text-white border border-teal-800/40 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                    <i class="fas fa-motorcycle text-teal-400"></i> E-Commerce Delivery Network
                </div>
                <h1 class="text-xl md:text-2xl font-black tracking-tight">Direct Individual Rider Network</h1>
                <p class="text-xs text-slate-300 max-w-2xl">
                    Manage direct rider registrations, KYC identity verification, vehicle inspection, informational platform affiliations (Pathao, inDrive, etc.), and segregated COD cash limits.
                </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('domestic.ecommerce.riders.rates') }}" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-teal-300 border border-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-2">
                    <i class="fas fa-calculator"></i> Fare Formula
                </a>
                <a href="{{ route('domestic.ecommerce.riders.cod') }}" class="px-3 py-2 bg-teal-600 hover:bg-teal-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
                    <i class="fas fa-hand-holding-dollar"></i> COD Ledgers
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Telemetry Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-slate-500">Total Riders</p>
                <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-id-card"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-slate-800 mt-2">{{ $riders->total() }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Registered providers</p>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-amber-200/80 shadow-xs bg-amber-50/20">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-amber-700">Pending KYC</p>
                <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-user-clock"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-amber-700 mt-2">{{ $pendingCount }}</p>
            <p class="text-[11px] text-amber-600 mt-0.5">Awaiting document review</p>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-emerald-200/80 shadow-xs bg-emerald-50/20">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-emerald-700">Verified Active</p>
                <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-user-check"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-emerald-700 mt-2">{{ $verifiedCount }}</p>
            <p class="text-[11px] text-emerald-600 mt-0.5">Approved for dispatch</p>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-purple-200/80 shadow-xs bg-purple-50/20">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-purple-700">Outstanding COD</p>
                <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-money-bill-wave"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-purple-700 mt-2">Rs. {{ number_format($totalCodOutstanding) }}</p>
            <p class="text-[11px] text-purple-600 mt-0.5">Cash held across network</p>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('domestic.ecommerce.riders.index') }}" class="flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by rider name, mobile, rider code (RDR-...), or vehicle number..."
                       class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-teal-500">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <select name="status" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white text-slate-700 focus:outline-none focus:border-teal-500">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending KYC</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>

                <select name="affiliation" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white text-slate-700 focus:outline-none focus:border-teal-500">
                    <option value="all">All Affiliations</option>
                    <option value="independent" {{ request('affiliation') === 'independent' ? 'selected' : '' }}>Independent / NETPACK</option>
                    <option value="pathao" {{ request('affiliation') === 'pathao' ? 'selected' : '' }}>Also Pathao</option>
                    <option value="indrive" {{ request('affiliation') === 'indrive' ? 'selected' : '' }}>Also inDrive</option>
                    <option value="parcel" {{ request('affiliation') === 'parcel' ? 'selected' : '' }}>Also Parcel</option>
                    <option value="courier_co" {{ request('affiliation') === 'courier_co' ? 'selected' : '' }}>Courier Company</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Riders Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Rider Info</th>
                        <th class="px-4 py-3">Vehicle & License</th>
                        <th class="px-4 py-3">Platform Affiliation</th>
                        <th class="px-4 py-3">COD Limit & Balance</th>
                        <th class="px-4 py-3">Rating & Deliveries</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($riders as $rider)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-teal-100 text-teal-700 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                    {{ strtoupper(substr($rider->full_name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 truncate">{{ $rider->full_name }}</p>
                                    <p class="text-[10px] font-mono text-teal-600 font-semibold">{{ $rider->rider_code }} &bull; {{ $rider->mobile }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-bold text-slate-800 uppercase text-[11px]">{{ $rider->vehicle_type }}</p>
                            <p class="text-[10px] font-mono text-slate-500">{{ $rider->vehicle_number ?? 'No Plate' }}</p>
                            <p class="text-[10px] text-slate-400">Lic: {{ $rider->driving_license_number ?? 'N/A' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($rider->affiliation && $rider->affiliation !== 'none')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200 uppercase">
                                    <i class="fas fa-info-circle text-[9px]"></i> Works w/ {{ ucfirst($rider->affiliation) }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">
                                    Independent
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="space-y-0.5">
                                <p class="text-[11px] font-bold text-slate-800">
                                    Limit: <span class="text-teal-700">Rs. {{ number_format($rider->cod_limit) }}</span>
                                    <span class="text-[9px] uppercase font-mono text-slate-400">({{ $rider->cod_level }})</span>
                                </p>
                                <p class="text-[10px] text-slate-500">
                                    Held: <span class="font-mono font-bold {{ $rider->current_outstanding_cod > 0 ? 'text-amber-600' : 'text-slate-400' }}">Rs. {{ number_format($rider->current_outstanding_cod) }}</span>
                                </p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1 text-amber-500 font-bold text-[11px]">
                                <i class="fas fa-star text-[10px]"></i>
                                <span>{{ number_format($rider->rating, 1) }}</span>
                            </div>
                            <p class="text-[10px] text-slate-400">{{ $rider->total_completed_deliveries }} completed</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($rider->verification_status === 'verified')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Verified
                                </span>
                            @elseif($rider->verification_status === 'pending')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                                    Pending KYC
                                </span>
                            @elseif($rider->verification_status === 'suspended')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                    Suspended
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                    {{ ucfirst($rider->verification_status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('domestic.ecommerce.riders.show', $rider->id) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold text-[11px] transition">
                                <i class="fas fa-user-gear"></i> Inspect & KYC
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                            <i class="fas fa-motorcycle text-3xl mb-2 text-slate-300"></i>
                            <p class="text-xs">No registered riders found matching your filters.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($riders->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $riders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
