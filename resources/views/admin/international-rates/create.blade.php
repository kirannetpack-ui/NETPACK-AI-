@extends('layouts.app')

@section('title', 'Configure International Rate Matrix - NETPACK Admin')
@section('page-title', 'Configure International Rate Matrix')

@section('content')
<script>
function rateMatrixForm() {
    return {
        rateType: @json(old('rate_type', 'country')),
        serviceType: '{{ old("service_type", "economy") }}',
        selectedHubId: '{{ old("hub_id", $preselectedHubId ?? "") }}',
        selectedAgencyId: '{{ old("agency_id", "") }}',
        hubs: @json($hubsJson),
        get currentHub() {
            return this.hubs.find(h => String(h.id) === String(this.selectedHubId)) || null;
        },
        get hubAgencies() {
            return this.currentHub ? (this.currentHub.agencies || []) : [];
        },
        get coveredCountries() {
            return this.currentHub ? (this.currentHub.coverage_countries || []) : [];
        },
        selectCountry(countryName) {
            const input = document.getElementById('target_country_input');
            if (input) {
                input.value = countryName;
                input.dispatchEvent(new Event('input'));
            }
        },
        baseHalfKg: 2500,
        incrementHalfKg: 400,
        ranges: (@json(old('per_kg_tiers'))) || [
            { min_weight: 10.1, max_weight: 20.0, rate_per_kg: 1050 },
            { min_weight: 20.1, max_weight: 45.0, rate_per_kg: 950 },
            { min_weight: 45.1, max_weight: 70.0, rate_per_kg: 850 },
            { min_weight: 70.1, max_weight: 100.0, rate_per_kg: 780 },
            { min_weight: 100.1, max_weight: 9999.0, rate_per_kg: 700 }
        ],

        autoFillSlabs() {
            const base = parseFloat(this.baseHalfKg) || 0;
            const inc = parseFloat(this.incrementHalfKg) || 0;
            const weights = ['0.5','1.0','1.5','2.0','2.5','3.0','3.5','4.0','4.5','5.0','5.5','6.0','6.5','7.0','7.5','8.0','8.5','9.0','9.5','10.0'];

            weights.forEach((w, idx) => {
                const input = document.getElementById('tier_' + w.replace('.', '_'));
                if (input) {
                    input.value = Math.round(base + (idx * inc));
                }
            });
        },

        addRange() {
            const lastRange = this.ranges[this.ranges.length - 1];
            const nextMin = lastRange ? Math.round((parseFloat(lastRange.max_weight) + 0.1) * 10) / 10 : 10.1;
            this.ranges.push({
                min_weight: nextMin,
                max_weight: Math.round((nextMin + 15) * 10) / 10,
                rate_per_kg: 800
            });
        },

        removeRange(idx) {
            this.ranges.splice(idx, 1);
        }
    };
}
</script>

<div class="max-w-6xl mx-auto space-y-5 rate-matrix-page" x-data="rateMatrixForm()">

    <div class="np-page-intro flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
            <p class="np-eyebrow">International / Tariffs / New rate matrix</p>
            <h1>New international rate matrix</h1>
            <p class="np-page-description">Set destination coverage, weight-based pricing and operational charges. All amounts are in Nepalese rupees (NPR).</p>
        </div>
        <a href="{{ route('admin.international-rates.index') }}" class="text-xs text-slate-500 mt-1 whitespace-nowrap">&larr; All rate matrices</a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-xs">
            <p class="font-bold mb-1">Please correct the following errors:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.international-rates.store') }}" class="space-y-6">
        @csrf

        <!-- SECTION 1: TARGETING, HUB & SERVICE -->
        <div class="np-section space-y-4">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2.5 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <i class="fas fa-bullseye text-teal-600"></i>
                    <span>Destination & service</span>
                </span>
                <span class="text-[11px] font-normal text-slate-400">Choose the route and service this rate applies to.</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                <div>
                    <label for="rate-hub-id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Gateway hub</label>
                    <select name="hub_id" x-model="selectedHubId"
                            @change="if(currentHub && !serviceType) { serviceType = 'economy'; } selectedAgencyId = '';"
                            class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none bg-white font-medium" id="rate-hub-id">
                        <option value="">Direct Express (Nepal Origin Direct Carrier)</option>
                        @foreach($hubs as $hub)
                            <option value="{{ $hub->id }}" {{ (string)old('hub_id', $preselectedHubId ?? '') === (string)$hub->id ? 'selected' : '' }}>
                                {{ $hub->hub_code }} - {{ $hub->hub_name }} ({{ $hub->mode_type }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="rate-agency-id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Hub agency</label>
                    <select name="agency_id" x-model="selectedAgencyId" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none bg-white font-medium" id="rate-agency-id">
                        <option value="">All Hub Agencies (Default)</option>
                        <template x-for="ag in hubAgencies" :key="ag.id">
                            <option :value="ag.id" x-text="ag.code + ' - ' + ag.name" :selected="String(ag.id) === String(selectedAgencyId)"></option>
                        </template>
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">Rate entered for specific hub agency</p>
                </div>

                <div>
                    <label for="rate-rate-type" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Rate scope</label>
                    <select name="rate_type" x-model="rateType" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none bg-white" id="rate-rate-type">
                        <option value="country">Country-Wise (Single Destination Country)</option>
                        <option value="zone">Zone-Wise (Geographic Country Group)</option>
                    </select>
                </div>

                <div>
                    <label for="rate-service-type" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Service type</label>
                    <select name="service_type" x-model="serviceType" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none bg-white font-medium" id="rate-service-type">
                        <option value="express">Priority express</option>
                        <option value="economy">Economy air cargo</option>
                    </select>
                </div>
            </div>

            <!-- Hub Clearance & Delivery Destinations Banner -->
            <div x-show="currentHub" class="p-4 bg-slate-50 rounded-lg border border-slate-200 space-y-2">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-200 text-slate-700" x-text="currentHub?.hub_code"></span>
                        <span class="text-xs font-bold text-slate-800 dark:text-white" x-text="currentHub?.hub_name"></span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300" x-text="'Customs: ' + (currentHub?.mode_type || 'DDP')"></span>
                    </div>
                    <span class="text-[11px] text-slate-500 font-medium">Click any destination country below to 1-click select:</span>
                </div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                        <i class="fas fa-plane-arrival text-indigo-500 mr-1"></i> Defined Clearance & Delivery Countries for this Hub:
                    </p>
                    <template x-if="coveredCountries.length > 0">
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="country in coveredCountries" :key="country">
                                <button type="button" @click="selectCountry(country)"
                                        class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-white dark:bg-slate-800 text-slate-600 border border-slate-200 hover:bg-slate-100 transition flex items-center gap-1.5 shadow-xs">
                                    <i class="fas fa-check-circle text-[10px] text-teal-500"></i>
                                    <span x-text="country"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                    <template x-if="coveredCountries.length === 0">
                        <p class="text-xs text-slate-500 italic">No specific clearance countries defined on this hub yet. You can type destination country below or update the hub.</p>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                <!-- Country Selector -->
                <div x-show="rateType === 'country'" class="md:col-span-2">
                    <label for="target_country_input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Destination country * <span class="text-[10px] font-normal text-slate-400"></span>
                    </label>
                    <input type="text" name="country" id="target_country_input" list="hub_countries_list"
                           placeholder="e.g. Australia, United Kingdom, United Arab Emirates, United States"
                           value="{{ old('country') }}"
                           class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none font-medium">
                    <datalist id="hub_countries_list">
                        <template x-for="country in coveredCountries" :key="country">
                            <option :value="country"></option>
                        </template>
                    </datalist>
                </div>

                <div x-show="rateType === 'country'">
                    <label for="rate-country-code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Country code (optional)</label>
                    <input type="text" name="country_code" placeholder="e.g. AU, GB, AE, US" value="{{ old('country_code') }}"
                           class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none uppercase font-mono" id="rate-country-code">
                </div>

                <!-- Zone Selector -->
                <div x-show="rateType === 'zone'" class="md:col-span-3">
                    <label for="rate-zone-id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Destination zone *</label>
                    <select name="zone_id" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none bg-white" id="rate-zone-id">
                        <option value="">Select an International Zone</option>
                        @foreach($zones as $z)
                            <option value="{{ $z->id }}" {{ old('zone_id') == $z->id ? 'selected' : '' }}>
                                {{ $z->name }} ({{ count($z->countries ?? []) }} countries)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2 md:col-span-3 sm:w-80">
                    <div>
                        <label for="rate-transit-days-min" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Minimum transit days</label>
                        <input type="number" name="transit_days_min" value="{{ old('transit_days_min', 3) }}" min="1" required
                               class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none font-mono" id="rate-transit-days-min">
                    </div>
                    <div>
                        <label for="rate-transit-days-max" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Maximum transit days</label>
                        <input type="number" name="transit_days_max" value="{{ old('transit_days_max', 6) }}" min="1" required
                               class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none font-mono" id="rate-transit-days-max">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: 0.5 KG TO 10.0 KG FIXED SLABS (DIFFERENCE OF 0.5 KILOS TILL 10 KILOS) -->
        <div class="np-section space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-layer-group text-teal-600"></i>
                        <span>Weight slabs · 0.5–10 kg</span>
                    </h2>
                    <p class="text-[11px] text-slate-500">Enter the total freight price for each weight bracket, or generate an initial sequence.</p>
                </div>

                <!-- Auto-fill Tool -->
                <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-200">
                    <span class="text-[10px] uppercase font-bold text-slate-500">Generator:</span>
                    <input type="number" x-model.number="baseHalfKg" placeholder="Base 0.5k" title="0.5kg Base Price"
                           class="w-20 text-[11px] px-2 py-1 bg-white border border-slate-200 rounded font-mono">
                    <input type="number" x-model.number="incrementHalfKg" placeholder="+0.5k" title="Increment per 0.5kg step"
                           class="w-20 text-[11px] px-2 py-1 bg-white border border-slate-200 rounded font-mono">
                    <button type="button" @click="autoFillSlabs()"
                            class="px-2.5 py-1 bg-teal-600 hover:bg-teal-700 text-white text-[10px] font-bold uppercase rounded shadow-xs transition">
                        Generate slabs
                    </button>
                </div>
            </div>

            <!-- 20-Cell Grid (4 columns of 5) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3">
                @foreach(['0.5','1.0','1.5','2.0','2.5','3.0','3.5','4.0','4.5','5.0','5.5','6.0','6.5','7.0','7.5','8.0','8.5','9.0','9.5','10.0'] as $weight)
                    <div class="p-2.5 bg-slate-50/80 rounded-xl border border-slate-200/90">
                        <label for="tier_{{ str_replace('.', '_', $weight) }}" class="block text-[11px] font-bold text-slate-700 mb-1">
                            {{ $weight }} KG Slab
                        </label>
                        <div class="relative">
                            <span class="absolute left-2.5 top-1.5 text-[10px] text-slate-400 font-bold">Rs.</span>
                            <input type="number" step="0.01" min="0"
                                   name="weight_tiers[{{ $weight }}]"
                                   id="tier_{{ str_replace('.', '_', $weight) }}"
                                   value="{{ old('weight_tiers', [])[$weight] ?? '' }}"
                                   placeholder="0"
                                   class="w-full pl-7 pr-2 py-1.5 text-xs font-mono font-bold bg-white border border-slate-300 rounded-lg outline-none focus:ring-1 focus:ring-teal-500">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- SECTION 3: DYNAMIC PER-KG RANGES ABOVE 10.0 KG -->
        <div class="np-section space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-chart-line text-teal-600"></i>
                        <span>Per-kilogram rates · above 10 kg</span>
                    </h2>
                    <p class="text-[11px] text-slate-500">Configurable weight break ranges beyond 10kg with per-kg rate calculation.</p>
                </div>

                <button type="button" @click="addRange()"
                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition flex items-center gap-1.5 border border-slate-200">
                    <i class="fas fa-plus text-teal-600"></i>
                    <span>Add Weight Range</span>
                </button>
            </div>

            <!-- Dynamic Range Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-3 py-2">Min Weight (KG)</th>
                            <th class="px-3 py-2">Max Weight (KG)</th>
                            <th class="px-3 py-2">Rate Per KG (NPR)</th>
                            <th class="px-3 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(rng, idx) in ranges" :key="idx">
                            <tr>
                                <td class="px-3 py-2">
                                    <input type="number" step="0.1" :name="'per_kg_tiers[' + idx + '][min_weight]'" x-model.number="rng.min_weight" required
                                           class="w-28 text-xs font-mono px-2.5 py-1.5 border border-slate-200 rounded-lg outline-none">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="0.1" :name="'per_kg_tiers[' + idx + '][max_weight]'" x-model.number="rng.max_weight" required
                                           class="w-28 text-xs font-mono px-2.5 py-1.5 border border-slate-200 rounded-lg outline-none">
                                </td>
                                <td class="px-3 py-2">
                                    <div class="relative w-36">
                                        <span class="absolute left-2.5 top-1.5 text-[10px] text-slate-400 font-bold">Rs.</span>
                                        <input type="number" step="1" :name="'per_kg_tiers[' + idx + '][rate_per_kg]'" x-model.number="rng.rate_per_kg" required
                                               class="w-full pl-7 pr-2 py-1.5 text-xs font-mono font-bold border border-slate-200 rounded-lg outline-none">
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <button type="button" @click="removeRange(idx)" class="text-red-500 hover:text-red-700 p-1" title="Remove Range">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="!ranges || ranges.length === 0">
                            <td colspan="4" class="px-3 py-4 text-center text-slate-400 italic">
                                No per-kilo weight ranges configured. Click "+ Add Weight Range" to configure tiers above 10kg.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION 4: DEFAULT STANDARD CHARGES -->
        <div class="np-section space-y-4">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2.5 flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar text-teal-600"></i>
                <span>Operational charges</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                <div>
                    <label for="rate-customs-clearance-charge" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Customs clearance (NPR) *</label>
                    <input type="number" step="10" name="customs_clearance_charge" value="{{ old('customs_clearance_charge', $defaultCustoms ?? 500) }}" required
                           class="w-full text-xs font-mono font-bold px-3 py-2 border border-slate-200 rounded-lg outline-none" id="rate-customs-clearance-charge">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Standard export customs brokerage</span>
                </div>

                <div>
                    <label for="rate-godown-charge" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Terminal handling (NPR/kg) *</label>
                    <input type="number" step="any" min="0" name="godown_charge" value="{{ old('godown_charge') }}" placeholder="Enter rate per kg (e.g. 50)" required
                           class="w-full text-xs font-mono font-bold px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-teal-500" id="rate-godown-charge">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Manually entered per-kilo handling fee</span>
                </div>

                <div>
                    <label for="rate-fuel-surcharge-percent" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Fuel surcharge (%)</label>
                    <input type="number" step="0.1" name="fuel_surcharge_percent" value="{{ old('fuel_surcharge_percent', 0) }}"
                           class="w-full text-xs font-mono px-3 py-2 border border-slate-200 rounded-lg outline-none" id="rate-fuel-surcharge-percent">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Percentage applied to base freight</span>
                </div>

                <div>
                    <label for="rate-doc-fee" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Documentation fee (NPR)</label>
                    <input type="number" step="10" name="doc_fee" value="{{ old('doc_fee', 0) }}"
                           class="w-full text-xs font-mono px-3 py-2 border border-slate-200 rounded-lg outline-none" id="rate-doc-fee">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Air waybill issuance fee</span>
                </div>
            </div>

            <div>
                <label for="rate-notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Operational notes</label>
                <textarea name="notes" rows="2" placeholder="e.g. Carrier specific routing instructions, DDP customs broker details..."
                          class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none" id="rate-notes">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded text-teal-600 focus:ring-teal-500">
                <label for="is_active" class="text-xs font-semibold text-slate-700">Publish immediately to Client Rate Inquiry Desk</label>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="np-form-actions">
            <p>Review the rates before publishing them to customers.</p>
            <div class="flex items-center gap-3">
            <a href="{{ route('admin.international-rates.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md transition flex items-center gap-2">
                <i class="fas fa-save"></i>
                <span>Save rate matrix</span>
            </button>
            </div>
        </div>
    </form>
</div>
@endsection
