@extends('layouts.app')

@section('title', 'International Rate Inquiry & Tariff Calculator - NETPACK')
@section('page-title', 'International Rate Inquiry')

@section('content')
<script>
function rateInquiryDesk() {
    return {
        country: @json($initialCountry),
        countryInput: @json($initialCountry),
        showCountryDropdown: false,
        allCountries: @json($countryList),
        highlightedIndex: 0,
        weight: {{ (float)$initialWeight }},
        useDimensions: false,
        length: '',
        width: '',
        height: '',
        packaging: @json($initialPackaging),
        packagingCatalog: @json($packagingCatalog),
        serviceType: 'all',
        manualGodownRate: '',
        pickupLocationType: @json($initialPickupType ?? 'inside_ktm'),
        pickupCity: @json($initialPickupCity ?? 'Pokhara'),
        customPickupCity: '',
        regionalCities: @json($regionalCities),
        loading: false,
        quoteData: @json($initialQuote),
        errorMessage: '',

        get filteredCountries() {
            if (!this.countryInput || this.countryInput.trim() === '') {
                return this.allCountries;
            }
            const q = this.countryInput.toLowerCase().trim();
            return this.allCountries.filter(function(c) {
                return c.toLowerCase().includes(q);
            });
        },

        get currentPackaging() {
            return this.packagingCatalog[this.packaging] || this.packagingCatalog['none'] || null;
        },

        onCountryType() {
            this.showCountryDropdown = true;
            this.highlightedIndex = 0;
            this.country = this.countryInput.trim();
            if (this.country) {
                this.fetchRates();
            }
        },

        selectCountry(c) {
            this.countryInput = c;
            this.country = c;
            this.showCountryDropdown = false;
            this.fetchRates();
        },

        navigateDown() {
            if (!this.showCountryDropdown) {
                this.showCountryDropdown = true;
                return;
            }
            if (this.highlightedIndex < this.filteredCountries.length - 1) {
                this.highlightedIndex++;
                this.scrollToHighlighted();
            }
        },

        navigateUp() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
                this.scrollToHighlighted();
            }
        },

        selectHighlighted() {
            if (this.filteredCountries.length > 0 && this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredCountries.length) {
                this.selectCountry(this.filteredCountries[this.highlightedIndex]);
            } else if (this.countryInput.trim()) {
                this.selectCountry(this.countryInput.trim());
            }
        },

        scrollToHighlighted() {
            this.$nextTick(() => {
                const el = document.getElementById('country-opt-' + this.highlightedIndex);
                if (el) el.scrollIntoView({ block: 'nearest' });
            });
        },

        clearCountry() {
            this.countryInput = '';
            this.country = '';
            this.showCountryDropdown = true;
            this.highlightedIndex = 0;
            this.$refs.countryInputRef?.focus();
        },
        
        async fetchRates() {
            if (!this.country || this.weight <= 0) return;
            this.loading = true;
            this.errorMessage = '';

            const effectivePickupCity = this.pickupLocationType === 'outside_ktm'
                ? (this.pickupCity === 'other' ? this.customPickupCity.trim() : this.pickupCity)
                : null;
            
            try {
                const response = await fetch('{{ route('rates.calculate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        country: this.country,
                        weight: parseFloat(this.weight) || 1.0,
                        length: this.useDimensions ? (parseFloat(this.length) || null) : null,
                        width: this.useDimensions ? (parseFloat(this.width) || null) : null,
                        height: this.useDimensions ? (parseFloat(this.height) || null) : null,
                        packaging: this.packaging,
                        service_type: this.serviceType === 'all' ? null : this.serviceType,
                        godown_rate_per_kg: (this.manualGodownRate !== '' && this.manualGodownRate !== null && !isNaN(this.manualGodownRate)) ? parseFloat(this.manualGodownRate) : null,
                        pickup_location_type: this.pickupLocationType,
                        pickup_city: effectivePickupCity
                    })
                });

                const result = await response.json();
                if (result.success) {
                    this.quoteData = result.data;
                } else {
                    this.errorMessage = result.message || 'Unable to retrieve rate quote.';
                }
            } catch (err) {
                console.error(err);
                this.errorMessage = 'Network error while calculating rate.';
            } finally {
                this.loading = false;
            }
        },

        bookShipment(quote) {
            const effectivePickupCity = this.pickupLocationType === 'outside_ktm'
                ? (this.pickupCity === 'other' ? (this.customPickupCity.trim() || 'Regional Hub') : this.pickupCity)
                : 'Kathmandu Valley';
            const feederCharge = quote.itemized?.domestic_feeder_charge || 0;

            const params = new URLSearchParams({
                shipment_type: 'international',
                receiver_country: this.country,
                weight: this.weight,
                chargeable_weight: this.quoteData?.weight_info?.chargeable_weight || this.weight,
                service_type: quote.service_type,
                packaging: this.packaging,
                quoted_rate: quote.itemized.total_cost,
                pickup_location_type: this.pickupLocationType,
                pickup_city: effectivePickupCity,
                domestic_feeder_charge: feederCharge
            });
            window.location.href = '{{ route('shipments.create') }}?' + params.toString();
        }
    };
}
</script>

<div class="max-w-7xl mx-auto space-y-6" x-data="rateInquiryDesk()">

    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-slate-950 via-teal-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-teal-900/50 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 text-teal-300 pointer-events-none text-9xl">
            <i class="fas fa-plane-departure"></i>
        </div>
        
        <div class="relative z-10 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                    ✈️ Global Tariff Matrix
                </span>
                <span class="text-xs text-slate-400">&bull; 0.5 KG Slabs to 10 KG &bull; Dynamic Per-KG Above 10 KG</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                International Air Cargo & Courier Rate Inquiry
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed">
                Check exact country-wise and zone-wise air freight tariffs with transparent breakdowns: Base Freight, Customs Clearance, Godown Charges, and Packaging Options.
            </p>
        </div>
    </div>

    <!-- Main Inquiry Layout (Left: Minimal Form, Right: Live Breakdown & Quotes) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT COLUMN: SHIPMENT SPECIFICATIONS PANEL -->
        <div class="lg:col-span-5 bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 sm:p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-sm font-bold">
                        <i class="fas fa-sliders"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Shipment Specifications</h2>
                        <p class="text-[11px] text-slate-500">Minimal required information for instant quote</p>
                    </div>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 px-2 py-0.5 rounded">
                    Nepal Origin
                </span>
            </div>

            <!-- Form Controls -->
            <div class="space-y-4">

                <!-- 0. Pickup Location & Regional Origin in Nepal -->
                <div class="p-3.5 bg-slate-50/90 rounded-xl border border-slate-200/90 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                            <i class="fas fa-location-dot text-teal-600 mr-1"></i> Pickup Origin (Nepal)
                        </label>
                        <span class="text-[10px] font-semibold text-teal-700 bg-teal-100/70 px-2 py-0.5 rounded-full border border-teal-200"
                              x-text="pickupLocationType === 'inside_ktm' ? 'Direct TIA Air Gateway' : 'Feeder Linehaul Added'">
                        </span>
                    </div>

                    <!-- Origin Segmented Toggle -->
                    <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-200/70 rounded-xl">
                        <button type="button" 
                                @click="pickupLocationType = 'inside_ktm'; fetchRates()"
                                :class="pickupLocationType === 'inside_ktm' ? 'bg-white text-teal-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                class="py-2 px-2.5 rounded-lg text-xs transition text-center flex items-center justify-center gap-1.5">
                            <i class="fas fa-city text-[11px]" :class="pickupLocationType === 'inside_ktm' ? 'text-teal-600' : 'text-slate-400'"></i>
                            <span>Inside Valley (KTM)</span>
                        </button>
                        <button type="button" 
                                @click="pickupLocationType = 'outside_ktm'; fetchRates()"
                                :class="pickupLocationType === 'outside_ktm' ? 'bg-white text-teal-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                class="py-2 px-2.5 rounded-lg text-xs transition text-center flex items-center justify-center gap-1.5">
                            <i class="fas fa-truck-moving text-[11px]" :class="pickupLocationType === 'outside_ktm' ? 'text-amber-600' : 'text-slate-400'"></i>
                            <span>Outside Kathmandu</span>
                        </button>
                    </div>

                    <!-- Outside Kathmandu: Regional Hub & Partner Feeder Select -->
                    <div x-show="pickupLocationType === 'outside_ktm'" x-transition class="space-y-2 pt-1 border-t border-slate-200/60">
                        <div class="flex items-center justify-between">
                            <label class="block text-[11px] font-bold text-slate-700">
                                Select Regional Pickup Hub:
                            </label>
                            <span class="text-[9px] uppercase tracking-wider text-amber-700 bg-amber-100 font-bold px-1.5 py-0.5 rounded border border-amber-200">
                                Domestic Linehaul
                            </span>
                        </div>

                        <select x-model="pickupCity" @change="fetchRates()"
                                class="w-full text-xs font-semibold px-3 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-slate-800 transition">
                            @foreach($regionalCities as $cKey => $cLabel)
                                <option value="{{ $cKey }}">{{ $cLabel }}</option>
                            @endforeach
                            <option value="other">Other District / Custom City...</option>
                        </select>

                        <!-- Custom City Input if 'other' is selected -->
                        <div x-show="pickupCity === 'other'" class="mt-2">
                            <input type="text" 
                                   x-model="customPickupCity" 
                                   @input.debounce.350ms="fetchRates()"
                                   placeholder="Enter district or city name (e.g. Gorkha, Ilam, Tansen)..."
                                   class="w-full text-xs px-3 py-2 bg-white border border-amber-300 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none text-slate-900">
                            <p class="text-[10px] text-slate-500 mt-1">Our national partner network tariff will automatically calculate the domestic connection fee to TIA Kathmandu.</p>
                        </div>

                        <!-- Active Feeder Routing Notice -->
                        <template x-if="quoteData && quoteData.domestic_feeder && quoteData.domestic_feeder.is_applicable">
                            <div class="p-2 bg-amber-50 border border-amber-200/70 rounded-lg text-[11px] text-amber-900 flex items-start gap-2">
                                <i class="fas fa-route text-amber-600 mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <span class="font-bold" x-text="quoteData.domestic_feeder.pickup_city + ' Depot ➔ KTM TIA Hub'"></span>
                                    <span class="text-slate-600 block text-[10px]" x-text="quoteData.domestic_feeder.notice"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <p x-show="pickupLocationType === 'inside_ktm'" class="text-[10px] text-slate-500">
                        Shipment picked up directly within Kathmandu, Lalitpur, or Bhaktapur for immediate TIA export clearance.
                    </p>
                </div>
                
                <!-- 1. Destination Country (Searchable Dynamic Combobox) -->
                <div class="relative" @click.outside="showCountryDropdown = false">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            <i class="fas fa-earth-americas text-teal-600 mr-1"></i> Destination Country <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[10px] text-teal-600 font-semibold uppercase tracking-wider flex items-center gap-1">
                            <i class="fas fa-keyboard"></i> Type to Search
                        </span>
                    </div>

                    <!-- Input with live search and clear button -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-search text-xs" :class="{'text-teal-600': showCountryDropdown}"></i>
                        </div>

                        <input type="text"
                               x-ref="countryInputRef"
                               x-model="countryInput"
                               @focus="showCountryDropdown = true"
                               @input.debounce.250ms="onCountryType()"
                               @keydown.down.prevent="navigateDown()"
                               @keydown.up.prevent="navigateUp()"
                               @keydown.enter.prevent="selectHighlighted()"
                               @keydown.escape="showCountryDropdown = false"
                               placeholder="Type country name (e.g. Australia, USA, Japan)..."
                               autocomplete="off"
                               class="w-full text-xs sm:text-sm pl-9 pr-16 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:bg-white outline-none font-bold text-slate-900 transition">

                        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center gap-1">
                            <!-- Clear Button -->
                            <button type="button" 
                                    x-show="countryInput" 
                                    @click="clearCountry()"
                                    class="w-5 h-5 rounded-full bg-slate-200 hover:bg-slate-300 text-slate-600 flex items-center justify-center text-[10px] transition"
                                    title="Clear destination">
                                <i class="fas fa-times"></i>
                            </button>
                            
                            <!-- Dropdown Toggle Button -->
                            <button type="button" 
                                    @click="showCountryDropdown = !showCountryDropdown; if(showCountryDropdown) $refs.countryInputRef.focus();"
                                    class="text-slate-400 hover:text-slate-600 px-1 py-1 text-xs transition">
                                <i class="fas" :class="showCountryDropdown ? 'fa-chevron-up text-teal-600' : 'fa-chevron-down'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Autocomplete Results Dropdown -->
                    <div x-show="showCountryDropdown" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                         class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-slate-100">
                        
                        <!-- Dropdown Header info -->
                        <div class="px-3 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between sticky top-0 border-b border-slate-100">
                            <span x-text="filteredCountries.length + ' destinations match'"></span>
                            <span class="text-teal-600 font-mono">↑↓ keys &bull; Enter</span>
                        </div>

                        <!-- Filtered List items -->
                        <template x-for="(c, idx) in filteredCountries" :key="c">
                            <button type="button" 
                                    :id="'country-opt-' + idx"
                                    @click="selectCountry(c)"
                                    @mouseenter="highlightedIndex = idx"
                                    :class="{
                                        'bg-teal-50 text-teal-900 font-bold': highlightedIndex === idx || country === c,
                                        'text-slate-700 hover:bg-slate-50': highlightedIndex !== idx && country !== c
                                    }"
                                    class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-location-dot text-[11px]" :class="country === c ? 'text-teal-600' : 'text-slate-400'"></i>
                                    <span x-text="c"></span>
                                </span>
                                <span x-show="country === c" class="text-[10px] font-bold text-teal-600 uppercase tracking-wider">
                                    Selected <i class="fas fa-check ml-0.5"></i>
                                </span>
                            </button>
                        </template>

                        <!-- Dynamic Typing Option: if user types a country not in list -->
                        <template x-if="countryInput.trim() && !filteredCountries.includes(countryInput.trim())">
                            <button type="button" 
                                    @click="selectCountry(countryInput.trim())"
                                    class="w-full text-left px-3.5 py-2.5 bg-teal-50/70 hover:bg-teal-100/70 text-teal-900 text-xs flex items-center justify-between font-bold border-t border-teal-100 transition">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-paper-plane text-teal-600"></i>
                                    <span>Calculate for typed country: "<span class="underline" x-text="countryInput.trim()"></span>"</span>
                                </span>
                                <span class="text-[10px] uppercase tracking-wider bg-teal-600 text-white px-2 py-0.5 rounded font-mono">
                                    Apply
                                </span>
                            </button>
                        </template>

                        <!-- Empty query prompt -->
                        <div x-show="filteredCountries.length === 0 && !countryInput.trim()" class="p-4 text-center text-xs text-slate-400">
                            Start typing to search global destinations...
                        </div>
                    </div>
                </div>

                <!-- 2. Gross Weight with Quick Presets -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            <i class="fas fa-scale-balanced text-teal-600 mr-1"></i> Gross Weight (KG) <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[11px] text-teal-600 font-semibold" x-text="weight > 0 ? weight + ' kg' : ''"></span>
                    </div>

                    <div class="relative">
                        <input type="number" step="0.1" min="0.1" max="5000" x-model.number="weight" @input.debounce.300ms="fetchRates()"
                               class="w-full text-sm font-mono font-bold px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:bg-white outline-none text-slate-900 transition">
                        <span class="absolute right-3.5 top-2.5 text-xs text-slate-400 font-bold uppercase pointer-events-none">KG</span>
                    </div>

                    <!-- Quick Preset Badges -->
                    <div class="flex flex-wrap items-center gap-1.5 mt-2">
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold mr-1">Quick:</span>
                        <template x-for="p in [0.5, 1.0, 1.5, 2.0, 5.0, 10.0, 15.0, 25.0]" :key="p">
                            <button type="button" @click="weight = p; fetchRates()"
                                    :class="weight === p ? 'bg-teal-600 text-white font-bold border-teal-600' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200'"
                                    class="px-2 py-0.5 rounded text-[11px] font-mono border transition"
                                    x-text="p + 'k'">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- 3. Optional Volumetric Dimensions Toggle -->
                <div class="pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <button type="button" @click="useDimensions = !useDimensions; if (!useDimensions) { length=''; width=''; height=''; fetchRates(); }"
                                class="text-xs font-semibold text-slate-600 hover:text-teal-700 flex items-center gap-1.5 transition">
                            <i class="fas fa-cube text-slate-400"></i>
                            <span>Add Box Dimensions (Volumetric Check)</span>
                            <i class="fas text-[10px]" :class="useDimensions ? 'fa-chevron-up text-teal-600' : 'fa-chevron-down'"></i>
                        </button>
                        <span class="text-[10px] text-slate-400 font-mono">/ 5000 rule</span>
                    </div>

                    <div x-show="useDimensions" x-transition class="grid grid-cols-3 gap-2.5 mt-2.5 p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Length (cm)</label>
                            <input type="number" step="1" min="1" placeholder="L" x-model.number="length" @input.debounce.300ms="fetchRates()"
                                   class="w-full text-xs font-mono px-2 py-1.5 bg-white border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Width (cm)</label>
                            <input type="number" step="1" min="1" placeholder="W" x-model.number="width" @input.debounce.300ms="fetchRates()"
                                   class="w-full text-xs font-mono px-2 py-1.5 bg-white border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Height (cm)</label>
                            <input type="number" step="1" min="1" placeholder="H" x-model.number="height" @input.debounce.300ms="fetchRates()"
                                   class="w-full text-xs font-mono px-2 py-1.5 bg-white border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-teal-500">
                        </div>
                    </div>
                </div>

                <!-- 4. Dynamic Packaging Materials Selection -->
                <div class="pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            <i class="fas fa-box-open text-teal-600 mr-1"></i> Packaging Materials (Optional)
                        </label>
                        <span class="text-[10px] text-slate-400 font-medium">Export Standard</span>
                    </div>

                    <select x-model="packaging" @change="fetchRates()"
                            class="w-full text-xs px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:bg-white outline-none text-slate-800 transition font-medium">
                        @foreach($packagingCatalog as $key => $pack)
                            <option value="{{ $key }}">
                                {{ $pack['name'] }} — {{ $pack['price'] > 0 ? '+ Rs. ' . number_format($pack['price']) : 'FREE / None' }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Selected Packaging Preview Badge -->
                    <template x-if="currentPackaging && currentPackaging.id !== 'none'">
                        <div class="mt-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-xs">
                                    <i class="fas" :class="'fa-' + (currentPackaging.icon || 'box')"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-[11px]" x-text="currentPackaging.name"></p>
                                    <p class="text-[10px] text-slate-500" x-text="currentPackaging.description"></p>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-teal-700 text-xs" x-text="currentPackaging.price > 0 ? '+ Rs. ' + currentPackaging.price.toLocaleString() : 'FREE'"></span>
                        </div>
                    </template>
                </div>

                <!-- 5. Manual Godown / Terminal Handling Rate (NPR / KG) -->
                <div class="pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            <i class="fas fa-warehouse text-teal-600 mr-1"></i> Godown / Terminal Handling (NPR / KG)
                        </label>
                        <span class="text-[10px] text-teal-600 font-semibold uppercase">Manual Entry</span>
                    </div>

                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-xs text-slate-400 font-bold">Rs.</span>
                        <input type="number" step="any" min="0" 
                               x-model="manualGodownRate" 
                               @input.debounce.300ms="fetchRates()"
                               placeholder="Auto (or enter custom rate per kg, e.g. 50)"
                               class="w-full text-xs sm:text-sm pl-10 pr-14 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:bg-white outline-none font-bold text-slate-900 transition">
                        <span class="absolute right-3.5 top-2.5 text-[11px] text-slate-400 font-bold uppercase pointer-events-none">/ KG</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Calculated as Chargeable Weight &times; Rate per KG. Leave blank for hub default.</p>
                </div>
            </div>

            <!-- Instant Calculate Button / Auto-live status -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400 flex items-center gap-1.5">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                    </span>
                    <span>Live Reactive Engine</span>
                </span>

                <button type="button" @click="fetchRates()" 
                        class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl shadow-xs transition flex items-center gap-2">
                    <i class="fas fa-rotate" :class="{ 'animate-spin': loading }"></i>
                    <span>Refresh Quote</span>
                </button>
            </div>
        </div>

        <!-- RIGHT COLUMN: DYNAMIC RESULTS, INCLUSIONS BANNER & QUOTES -->
        <div class="lg:col-span-7 space-y-5">
            
            <!-- 1. CHARGEABLE WEIGHT & ROUNDING TELEMETRY CARD -->
            <template x-if="quoteData && quoteData.weight_info">
                <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-teal-950 text-white rounded-2xl p-5 shadow-sm border border-teal-800/40">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30 font-mono">
                                    IATA Rounding Applied
                                </span>
                                <span class="text-xs text-slate-300 font-semibold" x-text="quoteData.country"></span>
                            </div>
                            <h3 class="text-xl font-black text-white flex items-center gap-2">
                                <span>Chargeable Weight:</span>
                                <span class="text-teal-300 font-mono" x-text="quoteData.weight_info.chargeable_weight + ' KG'"></span>
                            </h3>
                            <p class="text-xs text-slate-300 mt-0.5" x-text="quoteData.weight_info.explanation"></p>
                        </div>

                        <div class="flex items-center gap-3 bg-white/5 px-3.5 py-2.5 rounded-xl border border-white/10 flex-shrink-0">
                            <div class="text-center">
                                <p class="text-[10px] text-slate-400 uppercase font-bold">Gross</p>
                                <p class="text-xs font-mono font-bold text-white" x-text="quoteData.weight_info.gross_weight + ' kg'"></p>
                            </div>
                            <div class="h-6 w-px bg-white/10"></div>
                            <div class="text-center" x-show="quoteData.weight_info.is_volumetric">
                                <p class="text-[10px] text-amber-300 uppercase font-bold">Volumetric</p>
                                <p class="text-xs font-mono font-bold text-amber-300" x-text="quoteData.weight_info.volumetric_weight + ' kg'"></p>
                            </div>
                            <div class="h-6 w-px bg-white/10" x-show="quoteData.weight_info.is_volumetric"></div>
                            <div class="text-center">
                                <p class="text-[10px] text-teal-300 uppercase font-bold">Slab Rule</p>
                                <p class="text-xs font-mono font-bold text-teal-200" x-text="quoteData.weight_info.chargeable_weight <= 10 ? '0.5 KG Step' : '1.0 KG Ceil'"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- 2. TRANSPARENT STATUTORY CUSTOMS CLEARANCE & GODOWN CHARGES CALLOUT (Client Display) -->
            <div class="bg-gradient-to-r from-teal-900/10 via-slate-900/5 to-teal-950/10 border border-teal-500/30 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-teal-600 text-white flex items-center justify-center text-sm flex-shrink-0 mt-0.5 shadow-xs">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs font-black uppercase tracking-wider text-teal-950">Statutory Airport & Port Inclusions</h4>
                            <span class="text-[9px] bg-teal-100 text-teal-800 font-extrabold px-2 py-0.5 rounded-full border border-teal-200">100% Transparent</span>
                        </div>
                        <p class="text-[11px] text-slate-600 mt-0.5 leading-relaxed">
                            All NETPACK international quotations transparently include mandatory Tribhuvan International Airport (TIA) customs clearance and cargo godown terminal handling fees.
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 flex-shrink-0 self-stretch sm:self-auto justify-between sm:justify-end">
                    <!-- Dynamic Customs Clearance Badge -->
                    <div class="bg-white px-3 py-2 rounded-xl border border-teal-200/90 shadow-xs text-center flex-1 sm:flex-initial">
                        <div class="flex items-center justify-center gap-1 text-[9px] uppercase font-extrabold text-slate-400 tracking-wider">
                            <i class="fas fa-file-invoice text-teal-600"></i>
                            <span>Customs Clearance</span>
                        </div>
                        <div class="text-xs sm:text-sm font-black font-mono text-teal-800 mt-0.5">
                            Rs. <span x-text="quoteData?.global_tariff_inclusions?.customs_clearance?.toLocaleString() || '{{ number_format($defaultCustomsCharge) }}'"></span>
                        </div>
                        <span class="text-[9px] text-emerald-600 font-bold block">Included</span>
                    </div>

                    <!-- Dynamic Godown Handling Badge -->
                    <div class="bg-white px-3 py-2 rounded-xl border border-teal-200/90 shadow-xs text-center flex-1 sm:flex-initial">
                        <div class="flex items-center justify-center gap-1 text-[9px] uppercase font-extrabold text-slate-400 tracking-wider">
                            <i class="fas fa-warehouse text-teal-600"></i>
                            <span>Airport Godown</span>
                        </div>
                        <div class="text-xs sm:text-sm font-black font-mono text-teal-800 mt-0.5">
                            Rs. <span x-text="quoteData?.global_tariff_inclusions?.godown_charge ? quoteData.global_tariff_inclusions.godown_charge.toLocaleString() + ' / kg' : '{{ number_format($defaultGodownCharge) }} / kg'"></span>
                        </div>
                        <span class="text-[9px] text-emerald-600 font-bold block">Included</span>
                    </div>

                    <!-- Dynamic Feeder Linehaul Badge (if outside Kathmandu) -->
                    <template x-if="quoteData?.domestic_feeder && quoteData.domestic_feeder.is_applicable">
                        <div class="bg-amber-50 px-3 py-2 rounded-xl border border-amber-300 shadow-xs text-center flex-1 sm:flex-initial">
                            <div class="flex items-center justify-center gap-1 text-[9px] uppercase font-extrabold text-amber-700 tracking-wider">
                                <i class="fas fa-truck-ramp-box text-amber-600"></i>
                                <span>Regional Feeder</span>
                            </div>
                            <div class="text-xs sm:text-sm font-black font-mono text-amber-900 mt-0.5">
                                + Rs. <span x-text="quoteData.domestic_feeder.total_feeder_charge?.toLocaleString()"></span>
                            </div>
                            <span class="text-[9px] text-amber-700 font-bold block" x-text="quoteData.domestic_feeder.pickup_city + ' ➔ TIA'"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Loading Spinner State -->
            <div x-show="loading" class="bg-white rounded-2xl p-12 text-center border border-slate-200">
                <i class="fas fa-circle-notch animate-spin text-3xl text-teal-600 mb-3 block"></i>
                <p class="text-sm font-bold text-slate-800">Calculating exact air cargo tariffs...</p>
                <p class="text-xs text-slate-500 mt-1">Applying customs clearance, terminal godown handling, and statutory tariffs.</p>
            </div>

            <!-- Error Notification -->
            <div x-show="errorMessage && !loading" class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-xs">
                <i class="fas fa-triangle-exclamation mr-1.5"></i>
                <span x-text="errorMessage"></span>
            </div>

            <!-- 3. SERVICE QUOTES CARDS LIST -->
            <div x-show="!loading && quoteData && quoteData.quotes && quoteData.quotes.length > 0" class="space-y-4">
                <template x-for="(q, idx) in quoteData.quotes" :key="q.rate_id">
                    <div class="bg-white rounded-2xl shadow-xs border transition overflow-hidden"
                         :class="idx === 0 ? 'border-teal-500 ring-1 ring-teal-500/20' : 'border-slate-200/90 hover:border-slate-300'">
                        
                        <!-- Card Header -->
                        <div class="px-5 py-3.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2"
                             :class="idx === 0 ? 'bg-teal-50/50' : 'bg-slate-50/60'">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl flex items-center justify-center text-sm"
                                      :class="q.service_type === 'express' ? 'bg-amber-100 text-amber-700 font-bold' : 'bg-blue-100 text-blue-700 font-bold'">
                                    <i :class="q.service_type === 'express' ? 'fas fa-bolt' : 'fas fa-plane'"></i>
                                </span>
                                <div>
                                    <h4 class="text-sm font-black text-slate-900" x-text="q.service_label"></h4>
                                    <p class="text-[11px] text-slate-500">
                                        <span class="font-medium text-slate-700"><i class="far fa-clock mr-1 text-slate-400"></i>Estimated Transit: <span x-text="q.transit_days"></span></span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span x-show="idx === 0" class="px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-wider bg-teal-600 text-white">
                                    RECOMMENDED
                                </span>
                            </div>
                        </div>

                        <!-- Itemized Breakdown Table & Booking Action -->
                        <div class="p-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-center">
                                
                                <!-- Line items breakdown with transparent customs and godown charges -->
                                <div class="space-y-2 text-xs border-b md:border-b-0 md:border-r border-slate-100 pb-4 md:pb-0 md:pr-4">
                                    
                                    <!-- Base Freight -->
                                    <div class="flex justify-between items-center text-slate-600">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fas fa-plane-departure text-slate-400 text-xs"></i>
                                            <span>Base Freight (<span x-text="quoteData.weight_info.chargeable_weight + ' kg'"></span>):</span>
                                        </span>
                                        <span class="font-mono font-bold text-slate-900" x-text="'Rs. ' + q.itemized.base_freight.toLocaleString()"></span>
                                    </div>

                                    <!-- Customs Clearance Highlight -->
                                    <div class="flex justify-between items-center text-slate-700 bg-slate-50/70 px-2 py-1 rounded-lg border border-slate-100">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fas fa-file-invoice text-teal-600 text-xs"></i>
                                            <span class="font-medium">Origin Customs Clearance:</span>
                                        </span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[9px] uppercase font-bold bg-teal-100 text-teal-800 px-1.5 py-0.5 rounded">Included</span>
                                            <span class="font-mono font-bold text-slate-900" x-text="'Rs. ' + q.itemized.customs_clearance.toLocaleString()"></span>
                                        </div>
                                    </div>

                                    <!-- Godown Handling Highlight -->
                                    <div class="flex justify-between items-center text-slate-700 bg-slate-50/70 px-2 py-1 rounded-lg border border-slate-100">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fas fa-warehouse text-teal-600 text-xs"></i>
                                            <span class="font-medium">Airport Godown / Terminal:</span>
                                            <span class="text-[10px] text-slate-400 font-mono" x-show="q.itemized.godown_rate_per_kg" x-text="'(Rs. ' + q.itemized.godown_rate_per_kg + ' / kg)'"></span>
                                        </span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[9px] uppercase font-bold bg-teal-100 text-teal-800 px-1.5 py-0.5 rounded">Included</span>
                                            <span class="font-mono font-bold text-slate-900" x-text="'Rs. ' + q.itemized.godown_charge.toLocaleString()"></span>
                                        </div>
                                    </div>

                                    <!-- Domestic Feeder Linehaul (Outside Kathmandu Pickups) -->
                                    <template x-if="q.itemized.domestic_feeder && q.itemized.domestic_feeder.is_applicable">
                                        <div class="bg-amber-50/90 border border-amber-200/90 rounded-xl p-2.5 space-y-1.5 shadow-2xs">
                                            <div class="flex justify-between items-center text-amber-900">
                                                <span class="flex items-center gap-1.5 font-bold">
                                                    <i class="fas fa-truck-moving text-amber-600 text-xs"></i>
                                                    <span>Domestic Feeder Linehaul:</span>
                                                </span>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[9px] uppercase font-extrabold bg-amber-200 text-amber-900 px-1.5 py-0.5 rounded">Outside Valley</span>
                                                    <span class="font-mono font-bold text-amber-900" x-text="'+ Rs. ' + q.itemized.domestic_feeder_charge.toLocaleString()"></span>
                                                </div>
                                            </div>
                                            <div class="text-[10px] text-amber-800 flex flex-wrap items-center justify-between gap-1 border-t border-amber-200/70 pt-1.5">
                                                <span class="flex items-center gap-1">
                                                    <span class="font-bold" x-text="q.itemized.domestic_feeder.pickup_city"></span>
                                                    <span>➔</span>
                                                    <span class="font-bold">Kathmandu TIA Gateway</span>
                                                </span>
                                                <span class="text-amber-700 font-medium" x-text="'via ' + q.itemized.domestic_feeder.partner_name"></span>
                                            </div>
                                            <div class="text-[9px] font-mono text-amber-700/90 flex flex-wrap gap-x-2 pt-0.5">
                                                <span>Base: Rs. <span x-text="q.itemized.domestic_feeder.base_rate"></span></span>
                                                <span>&bull;</span>
                                                <span>Weight (<span x-text="quoteData.weight_info.chargeable_weight + 'kg'"></span>): Rs. <span x-text="q.itemized.domestic_feeder.weight_charge"></span></span>
                                                <template x-if="q.itemized.domestic_feeder.logistical_charge > 0">
                                                    <span>&bull; Handling: Rs. <span x-text="q.itemized.domestic_feeder.logistical_charge"></span></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Dynamic Packaging Material Fee -->
                                    <div class="flex justify-between items-center text-slate-600" x-show="q.itemized.packaging_fee > 0">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fas fa-box text-slate-400 text-xs"></i>
                                            <span>Packaging Material:</span>
                                        </span>
                                        <span class="font-mono text-slate-800" x-text="'Rs. ' + q.itemized.packaging_fee.toLocaleString()"></span>
                                    </div>

                                    <!-- Fuel Surcharge -->
                                    <div class="flex justify-between items-center text-slate-600" x-show="q.itemized.fuel_surcharge > 0">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fas fa-gas-pump text-slate-400 text-xs"></i>
                                            <span>Fuel Surcharge:</span>
                                        </span>
                                        <span class="font-mono text-slate-800" x-text="'Rs. ' + q.itemized.fuel_surcharge.toLocaleString()"></span>
                                    </div>
                                </div>

                                <!-- Total Price & Booking Action -->
                                <div class="flex flex-col items-start md:items-end justify-between space-y-3">
                                    <div class="text-left md:text-right">
                                        <span class="text-[10px] uppercase font-extrabold tracking-wider text-slate-400">Total All-Inclusive Estimated Tariff</span>
                                        <p class="text-2xl sm:text-3xl font-black text-teal-700 font-mono mt-0.5">
                                            Rs. <span x-text="q.itemized.total_cost.toLocaleString()"></span>
                                        </p>
                                        <span class="text-[10px] text-slate-400" x-text="quoteData?.domestic_feeder?.is_applicable ? 'Regional feeder pickup, TIA customs & terminal handling included' : 'Doorstep pickup, customs & terminal handling included'"></span>
                                    </div>

                                    <!-- 1-CLICK ACCEPT & BOOK SHIPMENT ACTION -->
                                    <button type="button" @click="bookShipment(q)"
                                            class="w-full sm:w-auto px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 group">
                                        <span>Accept Rate & Book Shipment</span>
                                        <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State when no quotes match -->
            <div x-show="!loading && (!quoteData || !quoteData.quotes || quoteData.quotes.length === 0)" 
                 class="bg-white rounded-2xl p-10 text-center border border-slate-200">
                <i class="fas fa-earth-americas text-4xl text-slate-300 mb-3 block"></i>
                <h4 class="text-sm font-bold text-slate-800">No Direct Published Tariffs Found</h4>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    Please select or type a destination country, or contact our Air Cargo operations desk for a custom charter or consolidated freight quote.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
