@extends('layouts.partner')

@section('title', 'Edit Rates - ' . $zone->zone_name)
@section('page-title', 'Configure Zone Rate Card')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="p-2 bg-teal-50 text-teal-600 rounded-lg">
                        <i class="fas fa-sliders-h"></i>
                    </span>
                    <h1 class="text-xl font-bold text-gray-800">Zone Rate Matrix: {{ $zone->zone_name }}</h1>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Zone Code: <span class="font-mono font-bold text-teal-700">{{ $zone->zone_code }}</span> | 
                    Type: <span class="font-medium text-gray-700">{{ ucfirst($zone->zone_type ?? 'urban') }}</span> | 
                    Districts: {{ !empty($zone->districts) ? implode(', ', $zone->districts) : 'All primary wards' }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('partner.rates.index', ['zone' => $zone->id]) }}" 
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back to Platform
                </a>
            </div>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg mb-4 text-sm flex items-center gap-2">
                    <i class="fas fa-check-circle text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-lg mb-4 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('partner.rates.update', $zone->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    @foreach($services as $serviceKey => $service)
                        @php
                            $rates = $service['rates'] ?? ['base_rate' => 0, 'per_kg_rate' => 0, 'estimated_hours' => $service['default_hours'] ?? 24, 'is_active' => true];
                            $color = $service['color'] ?? 'teal';
                        @endphp
                        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-teal-300 transition shadow-xs">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-gray-100 mb-4">
                                <div class="flex items-center gap-3">
                                    <span class="p-2 rounded-lg bg-{{ $color }}-50 text-{{ $color }}-600">
                                        <i class="fas {{ $service['icon'] ?? 'fa-cube' }}"></i>
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-gray-900 text-base">{{ $service['label'] }}</h4>
                                        <p class="text-xs text-gray-500">{{ $service['description'] ?? '' }}</p>
                                    </div>
                                </div>
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <span class="text-xs font-medium text-gray-600">Active on Corridor</span>
                                    <input type="checkbox" name="rates[{{ $serviceKey }}][is_active]" value="1" 
                                           {{ !empty($rates['is_active']) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-teal-600 focus:ring-teal-500 h-4 w-4">
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                        Base Price (NPR) *
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-xs font-semibold text-gray-400">Rs.</span>
                                        <input type="number" name="rates[{{ $serviceKey }}][base_rate]" step="0.5" min="0"
                                               id="base_{{ $serviceKey }}"
                                               oninput="recalcEditRow('{{ $serviceKey }}')"
                                               value="{{ old("rates.$serviceKey.base_rate", $rates['base_rate'] ?? 0) }}" 
                                               class="w-full pl-9 pr-3 py-2 border rounded-lg text-sm font-semibold focus:ring-2 focus:ring-teal-500 focus:outline-none">
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-1">Covers first 1.0 kg</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                        Weight-wise Rate (+NPR/kg) *
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-xs font-semibold text-gray-400">+Rs.</span>
                                        <input type="number" name="rates[{{ $serviceKey }}][per_kg_rate]" step="0.5" min="0"
                                               id="per_kg_{{ $serviceKey }}"
                                               oninput="recalcEditRow('{{ $serviceKey }}')"
                                               value="{{ old("rates.$serviceKey.per_kg_rate", $rates['per_kg_rate'] ?? 0) }}" 
                                               class="w-full pl-11 pr-3 py-2 border rounded-lg text-sm font-semibold focus:ring-2 focus:ring-teal-500 focus:outline-none">
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-1">Charge per additional kg</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                        Transit SLA (Hours)
                                    </label>
                                    <div class="relative">
                                        <input type="number" name="rates[{{ $serviceKey }}][estimated_hours]" step="1" min="1" max="720"
                                               value="{{ old("rates.$serviceKey.estimated_hours", $rates['estimated_hours'] ?? 24) }}" 
                                               class="w-full pr-10 pl-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                                        <span class="absolute right-3 top-2.5 text-xs font-bold text-gray-400">HRS</span>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-1">Delivery commitment</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                        Quote Preview (2.5 kg)
                                    </label>
                                    <div class="bg-gray-50 border border-gray-100 rounded-lg p-2.5 text-xs">
                                        <div class="flex justify-between items-center text-gray-600 font-mono">
                                            <span>Total:</span>
                                            <span class="font-bold text-teal-700 text-sm" id="preview_{{ $serviceKey }}">
                                                Rs. {{ number_format(($rates['base_rate'] ?? 0) + (1.5 * ($rates['per_kg_rate'] ?? 0)), 2) }}
                                            </span>
                                        </div>
                                        <span class="text-[10px] text-gray-400 block mt-0.5" id="formula_{{ $serviceKey }}">
                                            Rs. {{ $rates['base_rate'] ?? 0 }} + 1.5kg extra
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <a href="{{ route('partner.rates.index', ['zone' => $zone->id]) }}" 
                       class="text-sm font-medium text-gray-500 hover:text-gray-700">
                        Cancel & Return
                    </a>
                    <button type="submit" 
                            class="bg-teal-600 text-white px-6 py-2.5 rounded-lg hover:bg-teal-700 transition font-bold text-sm flex items-center gap-2 shadow-sm">
                        <i class="fas fa-save"></i> Save Rate Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function recalcEditRow(key) {
    const baseInput = document.getElementById('base_' + key);
    const perKgInput = document.getElementById('per_kg_' + key);
    const previewEl = document.getElementById('preview_' + key);
    const formulaEl = document.getElementById('formula_' + key);

    if (!baseInput || !perKgInput || !previewEl || !formulaEl) return;

    const base = parseFloat(baseInput.value) || 0;
    const perKg = parseFloat(perKgInput.value) || 0;
    const extraWeight = 1.5;
    const total = base + (extraWeight * perKg);

    previewEl.textContent = 'Rs. ' + total.toFixed(2);
    formulaEl.textContent = `Rs. ${base} + (${extraWeight}kg × Rs. ${perKg})`;
}
</script>
@endsection