@extends('layouts.partner')

@section('title', 'Domestic Service Rate Matrix')
@section('page-title', 'Service Rates & Rate Cards')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Banner -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="p-2 bg-teal-50 text-teal-600 rounded-lg">
                        <i class="fas fa-layer-group text-lg"></i>
                    </span>
                    <h1 class="text-xl font-bold text-gray-800">Domestic Partner Rate Platform</h1>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Unified single cockpit to configure Base Prices (first 1.0 kg), weight-wise incremental rates, and launch custom services for your delivery corridors.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="openCustomServiceModal()" 
                        class="bg-emerald-600 text-white px-4 py-2.5 rounded-lg hover:bg-emerald-700 transition font-medium text-sm flex items-center gap-2 shadow-sm">
                    <i class="fas fa-sparkles"></i> + Offer New Custom Service
                </button>
                <a href="{{ route('partner.zones.create') }}" 
                   class="bg-gray-100 text-gray-700 px-4 py-2.5 rounded-lg hover:bg-gray-200 transition font-medium text-sm flex items-center gap-2 border border-gray-200">
                    <i class="fas fa-map-marker-alt text-gray-500"></i> Add Delivery Zone
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mt-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mt-4 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-lg text-sm">
                <div class="font-medium flex items-center gap-2 mb-1">
                    <i class="fas fa-exclamation-circle text-rose-500"></i>
                    <span>Please correct the errors below:</span>
                </div>
                <ul class="list-disc list-inside text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Zone Selector Pills -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Select Active Delivery Zone</span>
            <span class="text-xs text-gray-500">{{ $zones->count() }} Corridors Configured</span>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($zones as $zone)
                @php $isSelected = ($selectedZone && $selectedZone->id === $zone->id); @endphp
                <a href="{{ route('partner.rates.index', ['zone' => $zone->id]) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2 border {{ $isSelected ? 'bg-teal-600 text-white border-teal-600 shadow-sm' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                    <i class="fas {{ $isSelected ? 'fa-check-circle' : 'fa-map-pin' }} text-xs opacity-75"></i>
                    <span>{{ $zone->zone_name }}</span>
                    <span class="text-xs opacity-80 px-1.5 py-0.5 rounded bg-black/10 font-mono">{{ $zone->zone_code }}</span>
                </a>
            @endforeach
        </div>
    </div>

    @if($selectedZone)
        <!-- Single Platform Rate Form for Selected Zone -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-2">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-gray-800">{{ $selectedZone->zone_name }}</h2>
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium bg-teal-100 text-teal-800 font-mono">
                            {{ $selectedZone->zone_code }}
                        </span>
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium bg-gray-200 text-gray-700">
                            {{ ucfirst($selectedZone->zone_type ?? 'urban') }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Districts: {{ !empty($selectedZone->districts) ? implode(', ', $selectedZone->districts) : 'All primary wards' }}
                    </p>
                </div>
                <div class="text-xs text-gray-500 bg-white border border-gray-200 px-3 py-1.5 rounded-lg flex items-center gap-2">
                    <i class="fas fa-info-circle text-teal-600"></i>
                    <span>Standard Pricing: <strong>Base Price</strong> applies to first 1.0 kg; extra weight billed at <strong>Rate per kg</strong>.</span>
                </div>
            </div>

            <form method="POST" action="{{ route('partner.rates.update', $selectedZone->id) }}" id="rateMatrixForm">
                @csrf
                @method('PUT')

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <th class="py-3 px-6">Service Offering</th>
                                <th class="py-3 px-4">Transit SLA</th>
                                <th class="py-3 px-4 w-44">Base Price (1.0 kg)</th>
                                <th class="py-3 px-4 w-44">Weight-wise (+NPR/kg)</th>
                                <th class="py-3 px-6 w-60">Interactive Quote Preview (2.5 kg sample)</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @foreach($services as $code => $svc)
                                @php
                                    $rates = $rateMatrix[$selectedZone->id][$code] ?? [
                                        'base_rate' => 0,
                                        'per_kg_rate' => 0,
                                        'estimated_hours' => $svc['default_hours'] ?? 24,
                                        'is_active' => true,
                                    ];
                                    $color = $svc['color'] ?? 'teal';
                                @endphp
                                <tr class="hover:bg-gray-50/80 transition rate-row" data-code="{{ $code }}">
                                    <!-- Service Info -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-start gap-3">
                                            <span class="p-2 rounded-lg bg-{{ $color }}-50 text-{{ $color }}-600 mt-0.5">
                                                <i class="fas {{ $svc['icon'] }} text-base"></i>
                                            </span>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-gray-900">{{ $svc['label'] }}</span>
                                                    @if(!empty($svc['is_custom']))
                                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800 uppercase tracking-wide">
                                                            Custom Service
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ $svc['description'] }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Transit SLA Input -->
                                    <td class="py-4 px-4">
                                        <div class="relative w-28">
                                            <input type="number" step="1" min="1" max="720"
                                                   name="rates[{{ $code }}][estimated_hours]" 
                                                   value="{{ old("rates.$code.estimated_hours", $rates['estimated_hours']) }}"
                                                   class="w-full border rounded-lg px-2.5 py-1.5 text-xs text-gray-800 pr-10 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                                            <span class="absolute right-2.5 top-2 text-[10px] font-bold text-gray-400">HRS</span>
                                        </div>
                                    </td>

                                    <!-- Base Price Input -->
                                    <td class="py-4 px-4">
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-xs font-semibold text-gray-400">Rs.</span>
                                            <input type="number" step="0.5" min="0" 
                                                   name="rates[{{ $code }}][base_rate]"
                                                   id="base_rate_{{ $code }}"
                                                   value="{{ old("rates.$code.base_rate", $rates['base_rate']) }}" 
                                                   oninput="recalcRow('{{ $code }}')"
                                                   placeholder="0.00"
                                                   class="w-full border rounded-lg pl-9 pr-3 py-1.5 text-sm font-semibold text-gray-900 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                                        </div>
                                        <span class="text-[10px] text-gray-400 block mt-1">Covers first 1.0 kg</span>
                                    </td>

                                    <!-- Weight-wise Rate Input -->
                                    <td class="py-4 px-4">
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-xs font-semibold text-gray-400">+Rs.</span>
                                            <input type="number" step="0.5" min="0" 
                                                   name="rates[{{ $code }}][per_kg_rate]"
                                                   id="per_kg_rate_{{ $code }}"
                                                   value="{{ old("rates.$code.per_kg_rate", $rates['per_kg_rate']) }}" 
                                                   oninput="recalcRow('{{ $code }}')"
                                                   placeholder="0.00"
                                                   class="w-full border rounded-lg pl-11 pr-3 py-1.5 text-sm font-semibold text-gray-900 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                                        </div>
                                        <span class="text-[10px] text-gray-400 block mt-1">Per extra kg</span>
                                    </td>

                                    <!-- Interactive Calculation Preview -->
                                    <td class="py-4 px-6">
                                        <div class="bg-gray-50 border border-gray-100 rounded-lg p-2.5 text-xs">
                                            <div class="flex justify-between items-center text-gray-600 font-mono">
                                                <span>2.5 kg sample quote:</span>
                                                <span class="font-bold text-teal-700 text-sm" id="calc_preview_{{ $code }}">
                                                    Rs. {{ number_format($rates['base_rate'] + (2.5 * $rates['per_kg_rate']), 2) }}
                                                </span>
                                            </div>
                                            <div class="text-[10px] text-gray-400 mt-1 flex items-center gap-1">
                                                <i class="fas fa-calculator text-[9px]"></i>
                                                <span id="calc_formula_{{ $code }}">
                                                    Rs. {{ $rates['base_rate'] }} + (2.5kg × Rs. {{ $rates['per_kg_rate'] }})
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Active Toggle -->
                                    <td class="py-4 px-4 text-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="rates[{{ $code }}][is_active]" value="1" 
                                                   {{ $rates['is_active'] ? 'checked' : '' }}
                                                   class="sr-only peer">
                                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-teal-600"></div>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-xs text-gray-500 flex items-center gap-1.5">
                        <i class="fas fa-shield-check text-teal-600"></i>
                        <span>Rates will be immediately updated in the live pricing engine for client quotes and consignment bookings.</span>
                    </div>
                    <button type="submit" 
                            class="bg-teal-600 text-white px-6 py-2.5 rounded-lg hover:bg-teal-700 transition font-bold text-sm flex items-center gap-2 shadow-sm">
                        <i class="fas fa-save"></i> Save Rate Matrix for {{ $selectedZone->zone_name }}
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <i class="fas fa-map-marked-alt text-4xl text-gray-300 mb-3"></i>
            <h3 class="text-lg font-bold text-gray-800">No Delivery Zones Configured</h3>
            <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">
                Please create your first delivery zone or hub corridor to begin configuring Base Prices and weight-wise courier rates.
            </p>
            <a href="{{ route('partner.zones.create') }}" class="mt-4 inline-flex items-center gap-2 bg-teal-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-teal-700 transition">
                <i class="fas fa-plus"></i> Create Initial Zone
            </a>
        </div>
    @endif
</div>

<!-- Add Custom Service Modal -->
<div id="customServiceModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-lg w-full overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-emerald-600 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-sparkles text-lg"></i>
                <h3 class="font-bold text-base">Offer New Customized Service</h3>
            </div>
            <button type="button" onclick="closeCustomServiceModal()" class="text-white/80 hover:text-white text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('partner.rates.custom-service') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Service Name *</label>
                <input type="text" name="name" id="custom_service_name" required 
                       oninput="autoGenServiceCode()"
                       placeholder="e.g. Overnight Priority Cargo, Heavy Fragile, Cold Chain"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <p class="text-[11px] text-gray-500 mt-0.5">Displayed to clients and sellers booking through this domestic corridor.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Service Identifier Code *</label>
                    <input type="text" name="code" id="custom_service_code" required
                           placeholder="e.g. overnight_priority"
                           class="w-full border rounded-lg px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Transit SLA (Hours) *</label>
                    <input type="number" step="0.5" min="0.5" max="720" name="transit_time_hours" required
                           value="24"
                           placeholder="24"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Base Price (First 1.0 kg) *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs text-gray-400 font-semibold">Rs.</span>
                        <input type="number" step="1" min="0" name="base_rate" id="modal_base_rate" required
                               value="120" oninput="recalcModalPreview()"
                               class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Rate / Extra kg *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs text-gray-400 font-semibold">+Rs.</span>
                        <input type="number" step="1" min="0" name="per_kg_rate" id="modal_per_kg_rate" required
                               value="30" oninput="recalcModalPreview()"
                               class="w-full border rounded-lg pl-11 pr-3 py-2 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Service Description</label>
                <textarea name="description" rows="2" 
                          placeholder="Explain the unique value, handling precautions or specialized vehicle equipment..."
                          class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
            </div>

            <!-- Live Preview Card in Modal -->
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 text-xs">
                <div class="flex items-center justify-between text-emerald-900 font-bold">
                    <span>Sample 2.5 kg Package Quote:</span>
                    <span id="modal_calc_preview" class="text-sm font-mono font-extrabold text-emerald-800">Rs. 165.00</span>
                </div>
                <div class="text-[11px] text-emerald-700 mt-1" id="modal_calc_formula">
                    First 1.0 kg = Rs. 120 + Additional 1.5 kg @ Rs. 30/kg
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeCustomServiceModal()" 
                        class="px-4 py-2 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="bg-emerald-600 text-white px-5 py-2 rounded-lg text-xs font-bold hover:bg-emerald-700 transition flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-plus-circle"></i> Launch & Provision Service
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function recalcRow(code) {
    const baseInput = document.getElementById('base_rate_' + code);
    const perKgInput = document.getElementById('per_kg_rate_' + code);
    const previewEl = document.getElementById('calc_preview_' + code);
    const formulaEl = document.getElementById('calc_formula_' + code);

    if (!baseInput || !perKgInput || !previewEl || !formulaEl) return;

    const base = parseFloat(baseInput.value) || 0;
    const perKg = parseFloat(perKgInput.value) || 0;
    const weight = 2.5; // for a 2.5 kg sample package
    const total = base + (weight * perKg);

    previewEl.textContent = 'Rs. ' + total.toFixed(2);
    formulaEl.textContent = `Rs. ${base} + (${weight}kg × Rs. ${perKg})`;
}

function openCustomServiceModal() {
    document.getElementById('customServiceModal').classList.remove('hidden');
    recalcModalPreview();
}

function closeCustomServiceModal() {
    document.getElementById('customServiceModal').classList.add('hidden');
}

function autoGenServiceCode() {
    const nameVal = document.getElementById('custom_service_name').value;
    const codeInput = document.getElementById('custom_service_code');
    if (nameVal && codeInput) {
        codeInput.value = nameVal.toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }
}

function recalcModalPreview() {
    const base = parseFloat(document.getElementById('modal_base_rate').value) || 0;
    const perKg = parseFloat(document.getElementById('modal_per_kg_rate').value) || 0;
    const weight = 2.5;
    const total = base + (weight * perKg);

    document.getElementById('modal_calc_preview').textContent = 'Rs. ' + total.toFixed(2);
    document.getElementById('modal_calc_formula').textContent = `Base Rs. ${base} + (${weight}kg × Rs. ${perKg}/kg)`;
}
</script>
@endsection