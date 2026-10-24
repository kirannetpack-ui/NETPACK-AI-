@extends('layouts.app')

@section('title', 'International Rate Matrices - NETPACK Admin')
@section('page-title', 'International Rate Matrices')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-teal-950 rounded-2xl p-6 text-white shadow-sm border border-slate-700/60 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                    Tariff Matrix Desk
                </span>
                <span class="text-xs text-slate-400">&bull; Country & Zone Based</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-white">International Sector Rate Management</h1>
            <p class="text-xs text-slate-300 mt-1 max-w-xl">
                Configure rates by Country or Zone, bound to Gateway Hubs. 0.5kg slabs up to 10kg with dynamic per-kg weight breaks beyond 10kg.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('rates.inquiry') }}" target="_blank"
               class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-white/10">
                <i class="fas fa-calculator text-amber-400"></i>
                <span>Open Rate Inquiry Desk</span>
                <i class="fas fa-external-link-alt text-[10px] opacity-60"></i>
            </a>

            <a href="{{ route('admin.international-rates.settings') }}" 
               class="px-4 py-2 bg-slate-700/80 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-slate-600">
                <i class="fas fa-sliders text-teal-400"></i>
                <span>Tariff & Packaging Feed</span>
            </a>

            <a href="{{ route('admin.international-rates.create') }}" 
               class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm transition flex items-center gap-1.5">
                <i class="fas fa-plus"></i>
                <span>Add Rate Matrix</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Rates</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total_rates']) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Country Direct</p>
            <p class="text-2xl font-black text-teal-600 mt-1">{{ number_format($stats['country_rates']) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Regional Zones</p>
            <p class="text-2xl font-black text-blue-600 mt-1">{{ number_format($stats['zone_rates']) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active Published</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($stats['active_rates']) }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl text-xs flex items-center gap-2">
            <i class="fas fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Rates Table Card -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        
        <!-- Filter Bar -->
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.international-rates.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search country or zone..."
                       class="text-xs px-3 py-2 border border-slate-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-teal-500 min-w-[200px]">

                <select name="service_type" class="text-xs px-3 py-2 border border-slate-200 rounded-lg bg-white outline-none">
                    <option value="">All Services</option>
                    <option value="express" {{ request('service_type') === 'express' ? 'selected' : '' }}>Express Priority</option>
                    <option value="economy" {{ request('service_type') === 'economy' ? 'selected' : '' }}>Economy Gateway Hub</option>
                </select>

                <select name="hub_id" class="text-xs px-3 py-2 border border-slate-200 rounded-lg bg-white outline-none">
                    <option value="">All Hubs</option>
                    <option value="direct" {{ request('hub_id') === 'direct' ? 'selected' : '' }}>Direct Express (Nepal)</option>
                    @foreach($hubs as $hub)
                        <option value="{{ $hub->id }}" {{ request('hub_id') == $hub->id ? 'selected' : '' }}>{{ $hub->hub_code }} - {{ $hub->hub_name }}</option>
                    @endforeach
                </select>

                <select name="agency_id" class="text-xs px-3 py-2 border border-slate-200 rounded-lg bg-white outline-none">
                    <option value="">All Agencies</option>
                    @foreach($agencies as $ag)
                        <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>{{ $ag->code }} - {{ $ag->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'service_type', 'hub_id', 'agency_id', 'rate_type']))
                    <a href="{{ route('admin.international-rates.index') }}" class="text-xs text-slate-500 hover:underline px-2">Reset</a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3">Scope & Target</th>
                        <th class="px-4 py-3">Gateway Hub & Agency</th>
                        <th class="px-4 py-3">Service</th>
                        <th class="px-4 py-3">0.5KG - 10KG (20 Slabs)</th>
                        <th class="px-4 py-3">>10KG Dynamic Ranges</th>
                        <th class="px-4 py-3">Default Charges</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rates as $r)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3 font-medium text-slate-900">
                                @if($r->rate_type === 'country')
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                                        <span class="font-bold text-slate-800">{{ $r->country }}</span>
                                        @if($r->country_code)
                                            <span class="text-[10px] font-mono text-slate-400">({{ $r->country_code }})</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">Country Direct Rate</span>
                                @else
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        <span class="font-bold text-blue-700">{{ $r->zone->name ?? 'Zone' }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">
                                        {{ count($r->zone->countries ?? []) }} Countries in Zone
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                @if($r->hub)
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            {{ $r->hub->hub_code }}
                                        </span>
                                        <span class="text-[11px] font-semibold text-slate-700 truncate max-w-[140px]">{{ $r->hub->country }}</span>
                                    </div>
                                    <div class="mt-1">
                                        @if($r->agency)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1 w-fit" title="{{ $r->agency->name }}">
                                                <i class="fas fa-building text-[8px] text-amber-600"></i>
                                                <span class="font-mono text-[9px]">{{ $r->agency->code }}</span> {{ \Illuminate\Support\Str::limit($r->agency->name, 20) }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-slate-400 italic">All Hub Agencies</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        DIRECT
                                    </span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">Nepal Origin Express</span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $r->service_type === 'express' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $r->service_type === 'express' ? '⚡ Express' : '🌐 Economy' }}
                                </span>
                                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $r->transit_days_min }}–{{ $r->transit_days_max }} Days</span>
                            </td>

                            <td class="px-4 py-3 font-mono">
                                @php
                                    $t05 = $r->weight_tiers['0.5'] ?? null;
                                    $t10 = $r->weight_tiers['10.0'] ?? null;
                                @endphp
                                @if($t05 && $t10)
                                    <span class="text-slate-800 font-bold">0.5k: Rs. {{ number_format($t05) }}</span>
                                    <span class="text-slate-400 mx-1">&rarr;</span>
                                    <span class="text-slate-800 font-bold">10k: Rs. {{ number_format($t10) }}</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5 font-sans">{{ count($r->weight_tiers ?? []) }} slab tiers configured</span>
                                @else
                                    <span class="text-slate-400 italic">No slabs set</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 font-mono">
                                @php $ranges = $r->per_kg_tiers ?? []; @endphp
                                @if(!empty($ranges))
                                    <span class="text-teal-700 font-bold">{{ count($ranges) }} Dynamic Ranges</span>
                                    <span class="text-[10px] text-slate-500 block mt-0.5">
                                        Starts @ Rs. {{ number_format($ranges[0]['rate_per_kg'] ?? 0) }}/kg
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Standard per-kg fallback</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-[11px] text-slate-600">
                                <div>Customs: <span class="font-mono font-bold text-slate-800">Rs. {{ number_format($r->customs_clearance_charge) }}</span></div>
                                <div>Godown: <span class="font-mono font-bold text-slate-800">Rs. {{ number_format($r->godown_charge) }} / kg</span></div>
                            </td>

                            <td class="px-4 py-3">
                                @if($r->is_active)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Active</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Disabled</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.international-rates.edit', $r->id) }}" 
                                       class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="Edit Rate Matrix">
                                        <i class="fas fa-pen text-[10px]"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.international-rates.destroy', $r->id) }}" 
                                          onsubmit="return confirm('Are you sure you want to delete this rate matrix?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 rounded bg-red-50 hover:bg-red-100 text-red-600 transition" title="Delete">
                                            <i class="fas fa-trash text-[10px]"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">
                                <i class="fas fa-file-invoice text-3xl mb-2 block"></i>
                                <p class="text-xs font-semibold">No international rate matrices found.</p>
                                <a href="{{ route('admin.international-rates.create') }}" class="text-xs text-teal-600 font-bold hover:underline mt-1 inline-block">
                                    Create first rate matrix &rarr;
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $rates->links() }}
        </div>
    </div>
</div>
@endsection
