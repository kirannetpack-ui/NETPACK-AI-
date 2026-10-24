@extends('layouts.partner')

@section('title', 'My Zones')
@section('page-title', 'My Delivery Zones')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header & Zone Actions -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg font-black shadow-xs">
                    <i class="fas fa-map-marked-alt"></i>
                </span>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Partner Delivery Zones & Depots</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage your operating provinces, districts, and capital regional depots.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <button type="button" 
                    id="toggleTerritoryBtn"
                    onclick="toggleTerritoryPanel()"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 hover:border-teal-500 hover:text-teal-600 text-slate-700 bg-white text-xs font-bold transition shadow-xs flex items-center gap-2">
                <i class="fas fa-sliders text-teal-600"></i>
                <span id="toggleTerritoryBtnText">{{ !empty($operatingDistricts) ? 'Manage Operating Territory' : 'Configure Territory' }}</span>
            </button>

            <a href="{{ route('partner.zones.create') }}" class="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-2">
                <i class="fas fa-plus"></i> Add Custom Zone
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-xs">
            <i class="fas fa-check-circle text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-xs">
            <i class="fas fa-exclamation-circle text-rose-600 text-sm"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Active Operating Coverage Summary Badge (When Configured) -->
    @if(!empty($operatingDistricts))
        <div class="bg-gradient-to-r from-teal-900 to-slate-900 rounded-2xl p-5 text-white shadow-md">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                            Active Operating Network
                        </span>
                        <span class="text-xs text-slate-300">
                            <strong>{{ count($operatingProvinces) }}</strong> Province(s) &bull; <strong>{{ count($operatingDistricts) }}</strong> District(s) Activated
                        </span>
                    </div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span>Coverage:</span>
                        <span class="text-teal-300 font-normal">
                            {{ implode(', ', $operatingProvinces) }}
                        </span>
                    </h3>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="openTerritoryPanel()" class="px-3.5 py-1.5 bg-teal-500/20 hover:bg-teal-500/30 text-teal-200 border border-teal-400/40 rounded-xl text-xs font-semibold transition flex items-center gap-1.5">
                        <i class="fas fa-pen-to-square text-[11px]"></i> Edit Coverage
                    </button>
                </div>
            </div>

            <!-- District Pills with Designated Capital Depots -->
            <div class="mt-4 pt-4 border-t border-slate-800 flex flex-wrap gap-1.5 max-h-36 overflow-y-auto">
                @foreach($operatingDistricts as $d)
                    @php
                        $capital = \App\Services\NepalGeographicalService::getDistrictCapital($d);
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] bg-slate-800/90 text-slate-200 border border-slate-700">
                        <i class="fas fa-location-dot text-teal-400 text-[9px]"></i>
                        <strong class="text-white">{{ $d }}</strong>
                        <span class="text-slate-400 text-[10px]">&bull; {{ $capital }} Depot</span>
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Multi-Province & Multi-District Operating Territory Setup Panel -->
    <div id="territoryPanel" class="{{ empty($operatingDistricts) ? 'block' : 'hidden' }} bg-white rounded-2xl shadow-sm border border-amber-200/90 overflow-hidden transition-all duration-300">
        <div class="bg-gradient-to-r from-amber-50 to-orange-50/50 p-6 border-b border-amber-200/80">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-200/70 text-amber-900 flex items-center justify-center text-base flex-shrink-0 mt-0.5">
                        <i class="fas fa-map-location-dot"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900">Set Your Operating Territory (Multi-Province & Multi-District)</h2>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Partners can operate in one, more than one, or all districts across multiple provinces. Every district is anchored by its official regional depot in the district capital.
                        </p>
                    </div>
                </div>

                @if(!empty($operatingDistricts))
                    <button type="button" onclick="closeTerritoryPanel()" class="text-slate-400 hover:text-slate-600 text-sm p-1.5 rounded-lg">
                        <i class="fas fa-times"></i>
                    </button>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('partner.zones.set-operating-district') }}" class="p-6 space-y-6" id="territoryForm">
            @csrf

            <!-- Step 1: Multi-Province Selector -->
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        1. Select Operating Province(s) <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="selectAllProvinces()" class="text-[11px] font-bold text-teal-700 hover:text-teal-900 underline">
                            Select All 7 Provinces (Nationwide)
                        </button>
                        <span class="text-slate-300">|</span>
                        <button type="button" onclick="clearAllProvinces()" class="text-[11px] font-bold text-slate-500 hover:text-slate-700 underline">
                            Clear
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-2">
                    @foreach($provincesWithCapitals as $pName => $pData)
                        @php
                            $isProvActive = in_array($pName, old('provinces', $operatingProvinces ?? []));
                            $distCount = count($pData['districts']);
                        @endphp
                        <label class="relative flex flex-col p-3 rounded-xl border text-center cursor-pointer select-none transition-all province-card {{ $isProvActive ? 'border-teal-600 bg-teal-50/70 shadow-xs' : 'border-slate-200 bg-slate-50/50 hover:border-slate-300' }}" id="prov_card_{{ $pData['code'] }}">
                            <input type="checkbox" 
                                   name="provinces[]" 
                                   value="{{ $pName }}" 
                                   id="prov_chk_{{ $pData['code'] }}" 
                                   class="sr-only province-checkbox" 
                                   data-code="{{ $pData['code'] }}"
                                   onchange="onProvinceToggle('{{ $pData['code'] }}')"
                                   {{ $isProvActive ? 'checked' : '' }}>
                            
                            <span class="text-xs font-bold text-slate-900 block leading-tight">{{ $pName }}</span>
                            <span class="text-[10px] text-slate-500 block mt-1">{{ $distCount }} Districts</span>
                            <span class="text-[9px] font-medium text-teal-700 block mt-0.5">Cap: {{ $pData['capital'] }}</span>
                            
                            <div class="mt-2 text-xs text-teal-600 chk-indicator {{ $isProvActive ? 'block' : 'hidden' }}">
                                <i class="fas fa-circle-check"></i>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Quick District Search -->
            <div class="relative">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        2. Choose Operating Districts & Capital Depots <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[11px] text-slate-500">
                        Filter by district or capital depot name
                    </span>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" 
                           id="districtFilterInput" 
                           oninput="filterDistrictsRealtime()"
                           placeholder="Type to quick search (e.g. Kaski, Pokhara, Chitwan, Bharatpur, Birgunj)..." 
                           class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition">
                </div>
            </div>

            <!-- Step 2: District Containers per Selected Province -->
            <div class="space-y-4" id="provinceDistrictsContainer">
                @foreach($provincesWithCapitals as $pName => $pData)
                    @php
                        $isProvActive = in_array($pName, old('provinces', $operatingProvinces ?? []));
                    @endphp
                    <div class="province-district-section rounded-xl border border-slate-200 overflow-hidden bg-slate-50/40 {{ $isProvActive ? 'block' : 'hidden' }}" 
                         id="dist_sec_{{ $pData['code'] }}" 
                         data-province="{{ $pName }}" 
                         data-code="{{ $pData['code'] }}">
                        
                        <div class="px-4 py-2.5 bg-slate-100/80 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-teal-600 text-white flex items-center justify-center text-[10px] font-black">
                                    {{ $pData['code'] }}
                                </span>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-900">{{ $pName }}</h3>
                                    <span class="text-[10px] text-slate-500">Province Capital: <strong>{{ $pData['capital'] }}</strong></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        onclick="selectAllInProvince('{{ $pData['code'] }}')" 
                                        class="px-2.5 py-1 bg-white hover:bg-teal-50 text-teal-700 border border-slate-200 rounded-lg text-[11px] font-bold transition">
                                    <i class="fas fa-check-double mr-1"></i> Select All ({{ count($pData['districts']) }})
                                </button>
                                <button type="button" 
                                        onclick="deselectAllInProvince('{{ $pData['code'] }}')" 
                                        class="px-2.5 py-1 bg-white hover:bg-rose-50 text-slate-500 hover:text-rose-600 border border-slate-200 rounded-lg text-[11px] font-bold transition">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5">
                            @foreach($pData['districts'] as $dItem)
                                @php
                                    $dName = $dItem['district'];
                                    $dCap = $dItem['capital'];
                                    $isDistChecked = in_array($dName, old('districts', $operatingDistricts ?? []));
                                @endphp
                                <label class="district-item relative flex items-start gap-2.5 p-2.5 rounded-xl border bg-white cursor-pointer select-none transition hover:border-teal-400 hover:shadow-xs {{ $isDistChecked ? 'border-teal-500 bg-teal-50/30' : 'border-slate-200' }}"
                                       data-province-code="{{ $pData['code'] }}"
                                       data-district="{{ strtolower($dName) }}"
                                       data-capital="{{ strtolower($dCap) }}">
                                    <input type="checkbox" 
                                           name="districts[]" 
                                           value="{{ $dName }}" 
                                           class="district-checkbox rounded border-slate-300 text-teal-600 focus:ring-teal-500 mt-0.5" 
                                           data-code="{{ $pData['code'] }}"
                                           onchange="updateCounters()"
                                           {{ $isDistChecked ? 'checked' : '' }}>
                                    <div class="flex-1 min-w-0">
                                        <span class="block text-xs font-bold text-slate-800 leading-snug">{{ $dName }}</span>
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-800 border border-amber-200/80 mt-1" title="Designated District Capital Regional Depot">
                                            <i class="fas fa-warehouse text-[9px] text-amber-600"></i>
                                            <span>{{ $dCap }} Depot</span>
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div id="noProvinceNotice" class="text-center py-8 border-2 border-dashed border-slate-200 rounded-2xl {{ empty($operatingProvinces) ? 'block' : 'hidden' }}">
                    <i class="fas fa-hand-pointer text-slate-300 text-3xl mb-2 block"></i>
                    <p class="text-xs font-semibold text-slate-700">Please select at least one Province above</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Districts with their constitutional capital depots will appear for your selection.</p>
                </div>
            </div>

            <!-- Options & Submission -->
            <div class="pt-4 border-t border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="auto_create_depots" value="1" checked class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span class="text-xs text-slate-700 font-medium">
                        Automatically configure designated <strong>Regional Depots in district capitals</strong> for selected districts.
                    </span>
                </label>

                <div class="flex items-center gap-3">
                    <span id="selectedSummaryBadge" class="text-xs font-bold text-slate-700 px-3 py-1.5 bg-slate-100 rounded-xl">
                        Selected: <strong class="text-teal-700" id="selectedCountDisplay">0 Districts</strong>
                    </span>

                    <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-2">
                        <i class="fas fa-check"></i> Set Operating District & Territory Coverage
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Existing Delivery Zones Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Active Partner Zones & District Depots</h2>
                <p class="text-xs text-slate-500 mt-0.5">Customized territory rates and delivery corridors registered to your account.</p>
            </div>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                Total: {{ $zones->total() }}
            </span>
        </div>

        @if($zones->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Zone / Depot Name</th>
                            <th class="py-3 px-4">District & Capital Hub</th>
                            <th class="py-3 px-4">Standard Base Rate</th>
                            <th class="py-3 px-4">Wards Coverage</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Approval</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($zones as $zone)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center text-[11px]">
                                            <i class="fas fa-warehouse"></i>
                                        </span>
                                        <div>
                                            <span class="block font-bold text-slate-900">{{ $zone->zone_name }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $zone->zone_code }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if(!empty($zone->districts))
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($zone->districts as $d)
                                                @php
                                                    $dCap = \App\Services\NepalGeographicalService::getDistrictCapital($d);
                                                @endphp
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] bg-slate-100 text-slate-800 border border-slate-200">
                                                    <strong>{{ $d }}</strong>
                                                    <span class="text-slate-500 font-normal">({{ $dCap }} Depot)</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400">All local coverage</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-700">
                                    @if($zone->standard_base_rate > 0)
                                        <span class="text-teal-700 font-bold">NPR {{ number_format($zone->standard_base_rate, 2) }}</span>
                                        <span class="text-[10px] text-slate-400 block">+NPR {{ number_format($zone->standard_per_kg_rate ?? 0, 2) }}/kg</span>
                                    @else
                                        <span class="text-slate-400">Standard Grid</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ implode(', ', $zone->wards ?? []) ?: 'All Primary Wards' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($zone->is_active)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($zone->approval_status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                            <i class="fas fa-check-circle text-[10px]"></i> Approved
                                        </span>
                                    @elseif($zone->approval_status === 'rejected')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                            <i class="fas fa-times-circle text-[10px]"></i> Rejected
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                            <i class="fas fa-clock text-[10px]"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('partner.zones.edit', $zone->id) }}" 
                                           class="p-1.5 text-slate-500 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition" title="Edit Zone">
                                            <i class="fas fa-pen-to-square"></i>
                                        </a>
                                        <a href="{{ route('partner.zones.show', $zone->id) }}" 
                                           class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form method="POST" action="{{ route('partner.zones.destroy', $zone->id) }}" 
                                              class="inline" onsubmit="return confirm('Delete this delivery zone?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete Zone">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-200">
                {{ $zones->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-map-location-dot text-4xl text-slate-300 mb-3 block"></i>
                <h3 class="text-sm font-bold text-slate-800">No Delivery Zones Configured Yet</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">Activate your operating provinces and districts above to auto-create regional depots, or create a custom zone.</p>
                <div class="mt-4 flex items-center justify-center gap-3">
                    <button type="button" onclick="openTerritoryPanel()" class="px-5 py-2 bg-teal-600 text-white rounded-xl text-xs font-bold hover:bg-teal-700 transition shadow-sm">
                        <i class="fas fa-sliders mr-1.5"></i> Configure Operating Coverage
                    </button>
                    <a href="{{ route('partner.zones.create') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition">
                        <i class="fas fa-plus mr-1"></i> Add Custom Zone
                    </a>
                </div>
            </div>
        @endif
    </div>

</div>

<script>
    function toggleTerritoryPanel() {
        const panel = document.getElementById('territoryPanel');
        if (panel.classList.contains('hidden')) {
            openTerritoryPanel();
        } else {
            closeTerritoryPanel();
        }
    }

    function openTerritoryPanel() {
        const panel = document.getElementById('territoryPanel');
        panel.classList.remove('hidden');
        document.getElementById('toggleTerritoryBtnText').innerText = 'Close Territory Editor';
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function closeTerritoryPanel() {
        const panel = document.getElementById('territoryPanel');
        panel.classList.add('hidden');
        document.getElementById('toggleTerritoryBtnText').innerText = 'Manage Operating Territory';
    }

    function onProvinceToggle(code) {
        const chk = document.getElementById('prov_chk_' + code);
        const card = document.getElementById('prov_card_' + code);
        const section = document.getElementById('dist_sec_' + code);
        const indicator = card.querySelector('.chk-indicator');

        if (chk.checked) {
            card.classList.add('border-teal-600', 'bg-teal-50/70', 'shadow-xs');
            card.classList.remove('border-slate-200', 'bg-slate-50/50');
            indicator.classList.remove('hidden');
            section.classList.remove('hidden');
        } else {
            card.classList.remove('border-teal-600', 'bg-teal-50/70', 'shadow-xs');
            card.classList.add('border-slate-200', 'bg-slate-50/50');
            indicator.classList.add('hidden');
            section.classList.add('hidden');
            // Uncheck districts of unselected province
            section.querySelectorAll('.district-checkbox').forEach(cb => cb.checked = false);
        }

        updateEmptyNotice();
        updateCounters();
    }

    function selectAllProvinces() {
        document.querySelectorAll('.province-checkbox').forEach(chk => {
            chk.checked = true;
            onProvinceToggle(chk.dataset.code);
            selectAllInProvince(chk.dataset.code);
        });
    }

    function clearAllProvinces() {
        document.querySelectorAll('.province-checkbox').forEach(chk => {
            chk.checked = false;
            onProvinceToggle(chk.dataset.code);
        });
        document.querySelectorAll('.district-checkbox').forEach(cb => cb.checked = false);
        updateCounters();
    }

    function selectAllInProvince(code) {
        const section = document.getElementById('dist_sec_' + code);
        if (!section) return;
        section.querySelectorAll('.district-checkbox').forEach(cb => cb.checked = true);
        updateCounters();
    }

    function deselectAllInProvince(code) {
        const section = document.getElementById('dist_sec_' + code);
        if (!section) return;
        section.querySelectorAll('.district-checkbox').forEach(cb => cb.checked = false);
        updateCounters();
    }

    function updateEmptyNotice() {
        const anyChecked = Array.from(document.querySelectorAll('.province-checkbox')).some(c => c.checked);
        const notice = document.getElementById('noProvinceNotice');
        if (notice) {
            notice.style.display = anyChecked ? 'none' : 'block';
        }
    }

    function updateCounters() {
        const checkedDistricts = document.querySelectorAll('.district-checkbox:checked');
        const checkedProvinces = document.querySelectorAll('.province-checkbox:checked');
        const display = document.getElementById('selectedCountDisplay');
        if (display) {
            display.innerText = `${checkedDistricts.length} Districts (${checkedProvinces.length} Provinces)`;
        }

        // Highlight checked district card
        document.querySelectorAll('.district-item').forEach(item => {
            const cb = item.querySelector('.district-checkbox');
            if (cb && cb.checked) {
                item.classList.add('border-teal-500', 'bg-teal-50/30');
                item.classList.remove('border-slate-200');
            } else {
                item.classList.remove('border-teal-500', 'bg-teal-50/30');
                item.classList.add('border-slate-200');
            }
        });
    }

    function filterDistrictsRealtime() {
        const query = document.getElementById('districtFilterInput').value.trim().toLowerCase();
        const items = document.querySelectorAll('.district-item');

        items.forEach(item => {
            const dName = item.dataset.district || '';
            const dCap = item.dataset.capital || '';
            const pCode = item.dataset.provinceCode;
            const provSec = document.getElementById('dist_sec_' + pCode);

            if (!query) {
                item.style.display = '';
            } else {
                if (dName.includes(query) || dCap.includes(query)) {
                    item.style.display = '';
                    // Auto-show province section if hidden
                    if (provSec && provSec.classList.contains('hidden')) {
                        const provChk = document.getElementById('prov_chk_' + pCode);
                        if (provChk && !provChk.checked) {
                            provChk.checked = true;
                            onProvinceToggle(pCode);
                        }
                    }
                } else {
                    item.style.display = 'none';
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateEmptyNotice();
        updateCounters();
    });
</script>
@endsection