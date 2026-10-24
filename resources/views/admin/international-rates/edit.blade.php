@extends('layouts.app')

@section('title', 'Edit International Rate Matrix - NETPACK Admin')
@section('page-title', 'Edit International Rate Matrix')

@section('content')
<script>
function rateMatrixEditForm() {
    return {
        rateType: '{{ $rate->rate_type }}',
        serviceType: '{{ $rate->service_type }}',
        selectedHubId: '{{ old("hub_id", $rate->hub_id ?? "") }}',
        selectedAgencyId: '{{ old("agency_id", $rate->agency_id ?? "") }}',
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
        baseHalfKg: {{ $rate->weight_tiers['0.5'] ?? 2500 }},
        incrementHalfKg: 400,
        ranges: (@json(old('per_kg_tiers', $rate->per_kg_tiers))) || [
            { min_weight: 10.1, max_weight: 20.0, rate_per_kg: 1050 },
            { min_weight: 20.1, max_weight: 45.0, rate_per_kg: 950 }
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

<div class="max-w-5xl mx-auto space-y-6" x-data="rateMatrixEditForm()">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Edit International Rate Matrix</h1>
            <p class="text-xs text-slate-500 mt-0.5">Adjust weight tiers, transit times, destination coverage, and hub agency parameters.</p>
        </div>
        <a href="{{ route('admin.international-rates.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">
            &larr; Back to Rates List
        </a>
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

    <form method="POST" action="{{ route('admin.international-rates.update', $rate->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- SECTION 1: TARGETING, HUB & SERVICE -->
        <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200/80 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2.5 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <i class="fas fa-bullseye text-teal-600"></i>
                    <span>1. Destination Scope, Hub & Service Details</span>
                </span>
                <span class="text-[11px] font-normal text-slate-400">Rates mapped to destination gateway clearance</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Gateway Hub *</label>
                    <select name="hub_id" x-model="selectedHubId" 
                            @change="selectedAgencyId = '';"
                            class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none bg-white font-medium">
                        <option value="">Direct Express (Nepal Origin Direct Carrier)</option>
                        @foreach($hubs as $hub)
                            <option value="{{ $hub->id }}" {{ (string)old('hub_id', $rate->hub_id) === (string)$hub->id ? 'selected' : '' }}>
                                {{ $hub->hub_code }} - {{ $hub->hub_name }} ({{ $hub->mode_type }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Hub Agency</label>
                    <select name="agency_id" x-model="selectedAgencyId" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none bg-white font-medium">
                        <option value="">All Hub Agencies (Default)</option>
                        <template x-for="ag in hubAgencies" :key="ag.id">
                            <option :value="ag.id" x-text="ag.code + ' - ' + ag.name" :selected="String(ag.id) === String(selectedAgencyId)"></option>
                        </template>
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">Rate entered for specific hub agency</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Rate Scope Type *</label>
                    <select name="rate_type" x-model="rateType" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none bg-white">
                        <option value="country">Country-Wise (Single Destination Country)</option>
                        <option value="zone">Zone-Wise (Geographic Country Group)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Service Type *</label>
                    <select name="service_type" x-model="serviceType" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none bg-white font-medium">
                        <option value="express">⚡ Priority Express (3–4 Days Direct Carrier)</option>
                        <option value="economy">🌐 Economy Air Cargo Service (6–8 Days Gateway Hub)</option>
                    </select>
                </div>
            </div>

            <!-- Hub Clearance & Delivery Destinations Banner -->
            <div x-show="currentHub" class="p-3.5 bg-indigo-50/70 dark:bg-indigo-950/30 rounded-xl border border-indigo-200 dark:border-indigo-800/60 space-y-2">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-indigo-600 text-white" x-text="currentHub?.hub_code"></span>
                        <span class="text-xs font-bold text-slate-800 dark:text-white" x-text="currentHub?.hub_name"></span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300" x-text="'Customs: ' + (currentHub?.mode_type || 'DDP')"></span>
                    </div>
                    <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-medium">Click any destination country below to 1-click select:</span>
                </div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                        <i class="fas fa-plane-arrival text-indigo-500 mr-1"></i> Defined Clearance & Delivery Countries for this Hub:
                    </p>
                    <template x-if="coveredCountries.length > 0">
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="country in coveredCountries" :key="country">
                                <button type="button" @click="selectCountry(country)"
                                        class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-white dark:bg-slate-800 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 transition flex items-center gap-1.5 shadow-xs">
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
                <div x-show="rateType === 'country'" class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Target Country * <span class="text-[10px] font-normal text-slate-400">(Type or click from hub above)</span>
                    </label>
                    <input type="text" name="country" id="target_country_input" list="hub_countries_list"
                           value="{{ old('country', $rate->country) }}"
                           placeholder="e.g. Australia, United Kingdom, United Arab Emirates, United States"
                           class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none font-medium">
                    <datalist id="hub_countries_list">
                        <template x-for="country in coveredCountries" :key="country">
                            <option :value="country"></option>
                        </template>
                    </datalist>
                </div>

                <div x-show="rateType === 'country'">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Country ISO Code (Optional)</label>
                    <input type="text" name="country_code" value="{{ old('country_code', $rate->country_code) }}"
                           placeholder="e.g. AU, GB, AE, US"
                           class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none uppercase font-mono">
                </div>

                <div x-show="rateType === 'zone'" class="md:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Target Zone *</label>
                    <select name="zone_id" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none bg-white">
                        <option value="">Select an International Zone</option>
                        @foreach($zones as $z)
                            <option value="{{ $z->id }}" {{ old('zone_id', $rate->zone_id) == $z->id ? 'selected' : '' }}>
                                {{ $z->name }} ({{ count($z->countries ?? []) }} countries)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2 md:col-span-3 sm:w-80">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Min SLA (Days)</label>
                        <input type="number" name="transit_days_min" value="{{ old('transit_days_min', $rate->transit_days_min) }}" min="1" required
                               class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Max SLA (Days)</label>
                        <input type="number" name="transit_days_max" value="{{ old('transit_days_max', $rate->transit_days_max) }}" min="1" required
                               class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none font-mono">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: 0.5 KG TO 10.0 KG FIXED SLABS -->
        <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200/80 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-layer-group text-teal-600"></i>
                        <span>2. Weight Slabs from 0.5 KG till 10.0 KG (0.5kg Increments)</span>
                    </h2>
                    <p class="text-[11px] text-slate-500">20 distinct air freight slabs. Enter flat tariff for each 0.5kg bracket.</p>
                </div>

                <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-200">
                    <span class="text-[10px] uppercase font-bold text-slate-500">Generator:</span>
                    <input type="number" x-model.number="baseHalfKg" placeholder="Base 0.5k"
                           class="w-20 text-[11px] px-2 py-1 bg-white border border-slate-200 rounded font-mono">
                    <input type="number" x-model.number="incrementHalfKg" placeholder="+0.5k"
                           class="w-20 text-[11px] px-2 py-1 bg-white border border-slate-200 rounded font-mono">
                    <button type="button" @click="autoFillSlabs()" 
                            class="px-2.5 py-1 bg-teal-600 hover:bg-teal-700 text-white text-[10px] font-bold uppercase rounded shadow-xs transition">
                        Fill 20 Slabs
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3">
                @foreach(['0.5','1.0','1.5','2.0','2.5','3.0','3.5','4.0','4.5','5.0','5.5','6.0','6.5','7.0','7.5','8.0','8.5','9.0','9.5','10.0'] as $weight)
                    <div class="p-2.5 bg-slate-50/80 rounded-xl border border-slate-200/90">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                            {{ $weight }} KG Slab
                        </label>
                        <div class="relative">
                            <span class="absolute left-2.5 top-1.5 text-[10px] text-slate-400 font-bold">Rs.</span>
                            <input type="number" step="10" min="0" 
                                   name="weight_tiers[{{ $weight }}]" 
                                   id="tier_{{ str_replace('.', '_', $weight) }}"
                                   value="{{ old("weight_tiers.$weight", $rate->weight_tiers[$weight] ?? '') }}"
                                   placeholder="0"
                                   class="w-full pl-7 pr-2 py-1.5 text-xs font-mono font-bold bg-white border border-slate-300 rounded-lg outline-none focus:ring-1 focus:ring-teal-500">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- SECTION 3: DYNAMIC PER-KG RANGES ABOVE 10.0 KG -->
        <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200/80 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-chart-line text-teal-600"></i>
                        <span>3. Dynamic Per-Kilo Rates Above 10.0 KG</span>
                    </h2>
                    <p class="text-[11px] text-slate-500">Configurable weight break ranges beyond 10kg with per-kg rate calculation.</p>
                </div>

                <button type="button" @click="addRange()"
                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition flex items-center gap-1.5 border border-slate-200">
                    <i class="fas fa-plus text-teal-600"></i>
                    <span>Add Weight Range</span>
                </button>
            </div>

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
        <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200/80 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2.5 flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar text-teal-600"></i>
                <span>4. Default Operational Charges & Surcharges</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Customs Clearance (NPR) *</label>
                    <input type="number" step="10" name="customs_clearance_charge" value="{{ old('customs_clearance_charge', $rate->customs_clearance_charge) }}" required
                           class="w-full text-xs font-mono font-bold px-3 py-2 border border-slate-200 rounded-lg outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Godown / Terminal Handling (NPR / KG) *</label>
                    <input type="number" step="any" min="0" name="godown_charge" value="{{ old('godown_charge', $rate->godown_charge) }}" placeholder="Enter rate per kg" required
                           class="w-full text-xs font-mono font-bold px-3 py-2 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-teal-500">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Manually entered per-kilo handling fee</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Fuel Surcharge (%)</label>
                    <input type="number" step="0.1" name="fuel_surcharge_percent" value="{{ old('fuel_surcharge_percent', $rate->fuel_surcharge_percent) }}"
                           class="w-full text-xs font-mono px-3 py-2 border border-slate-200 rounded-lg outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Documentation Fee (NPR)</label>
                    <input type="number" step="10" name="doc_fee" value="{{ old('doc_fee', $rate->doc_fee) }}"
                           class="w-full text-xs font-mono px-3 py-2 border border-slate-200 rounded-lg outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Operational Notes / Routing Specifications</label>
                <textarea name="notes" rows="2" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg outline-none">{{ old('notes', $rate->notes) }}</textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ $rate->is_active ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500">
                <label for="is_active" class="text-xs font-semibold text-slate-700">Publish to Client Rate Inquiry Desk</label>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.international-rates.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md transition flex items-center gap-2">
                <i class="fas fa-save"></i>
                <span>Update Rate Matrix</span>
            </button>
        </div>
    </form>
</div>
@endsection
