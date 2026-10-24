@extends('layouts.app')

@section('title', 'Domestic Rates')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-gradient-to-r from-slate-50 via-white to-teal-50/20">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">
                        <i class="fas fa-truck text-[10px]"></i> Domestic Express Network
                    </span>
                    <span class="text-xs text-slate-400 font-mono">Central Rate Matrix</span>
                </div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Domestic Tariff & Corridor Rates</h1>
                <p class="text-sm text-slate-500 mt-0.5">Central oversight of Standard and Partner-offered Custom Logistics Services across all 7 provinces.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.domestic.rates.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-sm font-semibold transition shadow-sm hover:shadow">
                    <i class="fas fa-plus"></i> Add Corridor Rate
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="p-6 border-b border-slate-100 bg-slate-50/60">
            <form method="GET" action="{{ route('admin.domestic.rates') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Domestic Partner</label>
                    <select name="partner_id" class="w-full text-sm border-slate-200 rounded-xl focus:border-teal-500 focus:ring-teal-500 bg-white">
                        <option value="">All Verified Partners</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" @selected(request('partner_id') == $partner->id)>{{ $partner->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Corridor Zone</label>
                    <select name="zone_id" class="w-full text-sm border-slate-200 rounded-xl focus:border-teal-500 focus:ring-teal-500 bg-white">
                        <option value="">All Delivery Zones</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" @selected(request('zone_id') == $zone->id)>{{ $zone->zone_name }} ({{ $zone->zone_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Service Type</label>
                    <select name="service_type" class="w-full text-sm border-slate-200 rounded-xl focus:border-teal-500 focus:ring-teal-500 bg-white">
                        <option value="">All Services (Standard & Custom)</option>
                        @foreach($serviceTypes as $code => $svc)
                            <option value="{{ $code }}" @selected(request('service_type') == $code)>
                                {{ $svc['icon'] ?? '🏷️' }} {{ $svc['name'] }} {{ !empty($svc['is_custom']) ? '★ Custom' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition">
                        <i class="fas fa-filter mr-1.5 text-xs"></i> Apply Filters
                    </button>
                    @if(request()->anyFilled(['partner_id', 'zone_id', 'service_type']))
                        <a href="{{ route('admin.domestic.rates') }}" class="px-3 py-2 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 rounded-xl text-sm font-medium transition" title="Clear Filters">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-5 flex items-center gap-3">
                    <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <div class="overflow-x-auto rounded-xl border border-slate-200/80 shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold uppercase tracking-wider text-slate-600">
                            <th class="py-3.5 px-4">Partner</th>
                            <th class="py-3.5 px-4">Service Tier</th>
                            <th class="py-3.5 px-4">Rate Type</th>
                            <th class="py-3.5 px-4">Origin / Hub</th>
                            <th class="py-3.5 px-4">Destination Zone</th>
                            <th class="py-3.5 px-4">Base Price (1.0 kg)</th>
                            <th class="py-3.5 px-4">Weight Rate (+NPR/kg)</th>
                            <th class="py-3.5 px-4">Band / SLA</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($rates as $rate)
                            @php
                                $isCustom = !in_array($rate->service_type, ['flash', 'same_day', 'standard', 'himalayan']);
                            @endphp
                            <tr class="hover:bg-teal-50/20 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800">{{ $rate->partner->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-slate-400">{{ $rate->partner->email ?? '' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">{{ $rate->service_icon }}</span>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $rate->service_name }}</div>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                @if($isCustom)
                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                                        Partner Custom
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-slate-100 text-slate-700">
                                                        Standard
                                                    </span>
                                                @endif
                                                <span class="text-xs text-slate-400 font-mono">{{ $rate->service_type }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ ucfirst(str_replace('_', ' ', $rate->rate_type ?? 'door_to_door')) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-medium text-slate-700">{{ $rate->originZone->zone_name ?? $rate->origin_city ?? 'Kathmandu' }}</span>
                                    @if(optional($rate->originZone)->zone_code)
                                        <span class="text-xs text-slate-400 block font-mono">{{ $rate->originZone->zone_code }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-teal-800">{{ $rate->destinationZone->zone_name ?? $rate->destination_city ?? 'National' }}</span>
                                    @if(optional($rate->destinationZone)->zone_code)
                                        <span class="text-xs text-teal-600 block font-mono">{{ $rate->destinationZone->zone_code }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-bold text-slate-900">Rs. {{ number_format($rate->base_rate, 2) }}</span>
                                    <span class="text-[11px] text-slate-400 block">First 1.0 kg</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-bold text-teal-700">+Rs. {{ number_format($rate->per_kg_rate, 2) }}</span>
                                    <span class="text-[11px] text-slate-400 block">Per add'l kg</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-xs text-slate-600 font-medium">{{ $rate->weight_from }} - {{ $rate->weight_to }} kg</div>
                                    <div class="text-[11px] text-slate-400">
                                        @if($rate->estimated_hours)
                                            ⚡ {{ $rate->estimated_hours }}h SLA
                                        @elseif($rate->estimated_days)
                                            📅 {{ $rate->estimated_days }}d SLA
                                        @else
                                            Standard SLA
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $rate->approval_status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($rate->approval_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ ucfirst($rate->approval_status ?? 'approved') }}
                                        </span>
                                        @if($rate->is_active)
                                            <span class="text-[10px] text-emerald-600 font-medium flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                            </span>
                                        @else
                                            <span class="text-[10px] text-slate-400 font-medium flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($rate->approval_status === 'pending')
                                            <form method="POST" action="{{ route('admin.domestic.rates.approve', $rate) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="p-2 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 rounded-lg transition" title="Approve Rate">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.domestic.rates.reject', $rate) }}" class="inline flex items-center gap-1">
                                                @csrf
                                                <input name="rejection_reason" required minlength="5" placeholder="Reason" class="w-24 rounded border-slate-300 text-xs px-2 py-1">
                                                <button type="submit" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition" title="Reject Rate">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.domestic.rates.edit', $rate->id) }}" class="p-2 text-slate-500 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition" title="Edit Rate">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.domestic.rates.destroy', $rate->id) }}" class="inline" onsubmit="return confirm('Delete rate for {{ $rate->service_name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete Rate">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-12 text-slate-400">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <p class="font-semibold text-slate-700">No domestic rates match your criteria.</p>
                                    <p class="text-xs text-slate-500 mt-1">Try clearing filters or click "Add Corridor Rate" to provision a new one.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                {{ $rates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
