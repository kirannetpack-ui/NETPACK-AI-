@extends('layouts.app')

@section('title', 'Delivery Service Areas & Radius')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header Hero Banner -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 text-white border border-teal-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                <i class="fas fa-map-location-dot text-teal-400"></i> Geo-Routing & Service Coverage
            </span>
            <h1 class="text-xl md:text-2xl font-black mt-1">Operating Service Areas</h1>
            <p class="text-xs text-slate-300 mt-0.5">
                Define the districts, municipalities, and local neighborhoods where you accept delivery jobs.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right">
                <p class="text-[10px] uppercase font-bold text-slate-400">Current Radius</p>
                <p class="text-xl font-black text-teal-400 font-mono">{{ $rider->service_radius_km ?? 10 }} KM</p>
            </div>
            <a href="{{ route('rider.settings') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-teal-300 border border-slate-700 rounded-xl text-xs font-bold transition">
                Capacity Settings
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-xl text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Add Service Area Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 border-b pb-2 flex items-center gap-2">
            <i class="fas fa-plus-circle text-teal-600"></i> Add Target Delivery Territory / Area
        </h3>
        <p class="text-xs text-slate-500">
            Jobs originating from or delivering to these areas will be prioritized on your radar marketplace.
        </p>

        <form method="POST" action="{{ route('rider.service-areas.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-3">
                    <x-nepal-territory-picker 
                        provinceName="province"
                        districtName="district"
                        :selectedProvince="old('province', $rider->province ?? 'Bagmati')"
                        :selectedDistrict="old('district', $rider->district ?? 'Kathmandu')"
                        provinceLabel="Operating Province"
                        districtLabel="Operating District *"
                        helperText="Select district where you provide parcel pickups & deliveries."
                        :required="true"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Municipality / Local Unit (Optional)</label>
                    <input type="text" name="municipality" value="{{ old('municipality') }}" placeholder="e.g. Lalitpur Metropolitan City"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ward Number (Optional)</label>
                    <input type="text" name="ward" value="{{ old('ward') }}" placeholder="e.g. 3"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Specific Area / Landmark (Optional)</label>
                    <input type="text" name="area_name" value="{{ old('area_name') }}" placeholder="e.g. New Road / Kuleshwor / Jawalakhel"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
            </div>

            <!-- Quick Add Area Chips -->
            <div class="pt-2">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Quick Presets (Click to autofill Area):</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Kuleshwor', 'Kalanki', 'Tripureshwor', 'New Road', 'Baneshwor', 'Koteshwor', 'Chabahil', 'Maharajgunj', 'Pulchowk', 'Jawalakhel', 'Satdobato', 'Suryabinayak'] as $preset)
                        <button type="button" onclick="document.querySelector('input[name=area_name]').value='{{ $preset }}'"
                                class="px-2.5 py-1 bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-200">
                            + {{ $preset }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl transition shadow-sm">
                    <i class="fas fa-location-arrow mr-1.5"></i> Save Service Area
                </button>
            </div>
        </form>
    </div>

    <!-- Active Service Areas List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">Configured Coverage Zones</h3>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 border border-teal-200">
                {{ $serviceAreas->count() }} Areas Defined
            </span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($serviceAreas as $area)
            <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition text-xs">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-900 text-sm">
                            {{ $area->district }}
                            @if($area->area_name)
                                <span class="text-teal-700">({{ $area->area_name }})</span>
                            @endif
                        </span>
                        @if($area->is_active)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500">Inactive</span>
                        @endif
                    </div>
                    <p class="text-slate-500 text-[11px]">
                        Province: {{ $area->province ?? 'Bagmati' }}
                        @if($area->municipality) &bull; Municipality: {{ $area->municipality }} @endif
                        @if($area->ward) &bull; Ward: {{ $area->ward }} @endif
                        &bull; Coverage: {{ $area->service_radius_km ?? 10 }} KM Radius
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('rider.service-areas.toggle', $area->id) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition {{ $area->is_active ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                            {{ $area->is_active ? 'Pause' : 'Activate' }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('rider.service-areas.destroy', $area->id) }}" onsubmit="return confirm('Remove this service area?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-400 text-xs">
                <i class="fas fa-map-marked-alt text-3xl text-slate-300 mb-2"></i>
                <p class="font-bold text-slate-700">No Custom Service Areas Configured</p>
                <p class="mt-1 text-slate-400">Your jobs are currently matched using your primary district: <strong class="text-slate-700">{{ $rider->district ?? 'Kathmandu Valley' }}</strong>.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
