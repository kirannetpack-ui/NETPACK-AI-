@props([
    'provinceName' => 'province',
    'districtName' => 'district',
    'selectedProvince' => null,
    'selectedDistrict' => null,
    'isMultiple' => false,
    'required' => false,
    'provinceLabel' => 'Province / Territory',
    'districtLabel' => 'District',
    'idPrefix' => 'nepal_geo_' . uniqid(),
    'showSearch' => true,
    'helperText' => null,
])

@php
    $map = \App\Services\NepalGeographicalService::getProvinceDistrictMap();
    $provinceNames = \App\Services\NepalGeographicalService::getProvinceNames();
    
    // Auto-resolve province if only district was provided
    $initialDistrict = old($districtName, $selectedDistrict);
    $initialProvince = old($provinceName, $selectedProvince);
    
    if (empty($initialProvince) && !empty($initialDistrict) && is_string($initialDistrict)) {
        $initialProvince = \App\Services\NepalGeographicalService::getProvinceForDistrict($initialDistrict);
    }
    
    $initialDistrictsArray = is_array($initialDistrict) ? $initialDistrict : ($initialDistrict ? [$initialDistrict] : []);
@endphp

<div class="nepal-territory-picker w-full" id="{{ $idPrefix }}_container" data-picker-id="{{ $idPrefix }}">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Province Selection -->
        <div>
            <label for="{{ $idPrefix }}_province" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5 flex items-center justify-between">
                <span>{{ $provinceLabel }} @if($required)<span class="text-red-500">*</span>@endif</span>
                <span class="text-[10px] font-medium text-slate-400 font-mono">7 Provinces</span>
            </label>
            <div class="relative">
                <select name="{{ $provinceName }}" 
                        id="{{ $idPrefix }}_province" 
                        @if($required) required @endif
                        class="w-full text-sm font-medium border border-slate-300 rounded-xl px-3.5 py-2.5 bg-white text-slate-800 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 shadow-sm transition appearance-none pr-8">
                    <option value="">-- Choose Province --</option>
                    @foreach($provinceNames as $pName)
                        <option value="{{ $pName }}" @selected(strcasecmp($initialProvince ?? '', $pName) === 0)>
                            {{ $pName }} ({{ count($map[$pName]) }} Districts)
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>
            @error($provinceName)
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- District Selection -->
        <div>
            <label for="{{ $idPrefix }}_district" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5 flex items-center justify-between">
                <span>{{ $districtLabel }} @if($required)<span class="text-red-500">*</span>@endif</span>
                <span id="{{ $idPrefix }}_district_count" class="text-[10px] font-semibold text-teal-600 bg-teal-50 px-2 py-0.5 rounded-full border border-teal-200/60">
                    Select Province
                </span>
            </label>

            @if($showSearch)
                <!-- Quick Search Input -->
                <div class="relative mb-2">
                    <input type="text" 
                           id="{{ $idPrefix }}_search" 
                           placeholder="Type to search district..." 
                           class="w-full text-xs border border-slate-200 bg-slate-50/70 focus:bg-white rounded-lg pl-8 pr-3 py-1.5 text-slate-700 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 outline-none transition placeholder-slate-400">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400">
                        <i class="fas fa-search text-[10px]"></i>
                    </div>
                </div>
            @endif

            @if(!$isMultiple)
                <!-- Single Select Dropdown -->
                <div class="relative">
                    <select name="{{ $districtName }}" 
                            id="{{ $idPrefix }}_district" 
                            @if($required) required @endif
                            class="w-full text-sm font-medium border border-slate-300 rounded-xl px-3.5 py-2.5 bg-white text-slate-800 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 shadow-sm transition appearance-none pr-8">
                        <option value="">-- Choose District --</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            @else
                <!-- Multiple Select Checkbox Grid -->
                <div id="{{ $idPrefix }}_district_container" class="max-h-48 overflow-y-auto border border-slate-300 rounded-xl p-3 bg-white space-y-1 text-sm shadow-inner">
                    <p class="text-xs text-slate-400 italic py-2 text-center" id="{{ $idPrefix }}_empty_note">
                        Please select a province above to view districts.
                    </p>
                </div>
                <div class="flex items-center justify-between mt-1.5 px-1">
                    <button type="button" id="{{ $idPrefix }}_select_all" class="text-[11px] font-semibold text-teal-600 hover:text-teal-800 transition">
                        Select All in Province
                    </button>
                    <button type="button" id="{{ $idPrefix }}_clear_all" class="text-[11px] font-medium text-slate-500 hover:text-slate-700 transition">
                        Clear Selection
                    </button>
                </div>
            @endif

            @error($districtName)
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
            
            @if($helperText)
                <p class="text-[11px] text-slate-400 mt-1">{{ $helperText }}</p>
            @endif
        </div>
    </div>
</div>

<script>
(function() {
    const pickerId = '{{ $idPrefix }}';
    const isMultiple = {{ $isMultiple ? 'true' : 'false' }};
    const provinceDistrictMap = {!! \App\Services\NepalGeographicalService::getProvincesJson() !!};
    const districtCapitalsMap = {!! json_encode(\App\Services\NepalGeographicalService::DISTRICT_CAPITALS) !!};
    const initialSelectedDistricts = {!! json_encode($initialDistrictsArray) !!};
    const initialProvince = '{{ $initialProvince ?? '' }}';

    function initTerritoryPicker() {
        const provinceSelect = document.getElementById(pickerId + '_province');
        const searchInput = document.getElementById(pickerId + '_search');
        const countBadge = document.getElementById(pickerId + '_district_count');
        
        if (!provinceSelect) return;

        let districtSelect = isMultiple ? null : document.getElementById(pickerId + '_district');
        let multipleContainer = isMultiple ? document.getElementById(pickerId + '_district_container') : null;
        let selectAllBtn = isMultiple ? document.getElementById(pickerId + '_select_all') : null;
        let clearAllBtn = isMultiple ? document.getElementById(pickerId + '_clear_all') : null;

        function renderDistricts(selectedProvince, query = '') {
            const districts = provinceDistrictMap[selectedProvince] || [];
            query = query.toLowerCase();

            if (!isMultiple && districtSelect) {
                const currentVal = districtSelect.value;
                districtSelect.innerHTML = '';
                
                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = districts.length > 0 ? '-- Choose District --' : '-- Select Province First --';
                districtSelect.appendChild(defaultOpt);

                let visibleCount = 0;
                districts.forEach(d => {
                    const cap = districtCapitalsMap[d] || '';
                    if (!query || d.toLowerCase().includes(query) || (cap && cap.toLowerCase().includes(query))) {
                        const opt = document.createElement('option');
                        opt.value = d;
                        opt.textContent = cap ? `${d} (${cap} Depot)` : d;
                        if (initialSelectedDistricts.includes(d) || currentVal === d) {
                            opt.selected = true;
                        }
                        districtSelect.appendChild(opt);
                        visibleCount++;
                    }
                });

                if (countBadge) {
                    countBadge.textContent = districts.length > 0 ? `${visibleCount} of ${districts.length} Districts` : 'Select Province';
                }
            } else if (isMultiple && multipleContainer) {
                multipleContainer.innerHTML = '';
                
                if (districts.length === 0) {
                    multipleContainer.innerHTML = '<p class="text-xs text-slate-400 italic py-2 text-center">Please select a province above to view districts.</p>';
                    if (countBadge) countBadge.textContent = 'Select Province';
                    return;
                }

                let visibleCount = 0;
                districts.forEach(d => {
                    const cap = districtCapitalsMap[d] || '';
                    if (!query || d.toLowerCase().includes(query) || (cap && cap.toLowerCase().includes(query))) {
                        const isChecked = initialSelectedDistricts.includes(d);
                        const label = document.createElement('label');
                        label.className = 'flex items-center justify-between gap-2 px-2.5 py-1.5 hover:bg-teal-50/60 rounded-lg cursor-pointer transition text-slate-700';
                        label.innerHTML = `
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="{{ $districtName }}" value="${d}" ${isChecked ? 'checked' : ''} 
                                       class="district-checkbox rounded text-teal-600 focus:ring-teal-500 border-slate-300">
                                <span class="text-xs font-semibold text-slate-900">${d}</span>
                            </div>
                            ${cap ? `<span class="text-[10px] text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded font-medium border border-slate-200/80">${cap} Depot</span>` : ''}
                        `;
                        multipleContainer.appendChild(label);
                        visibleCount++;
                    }
                });

                if (countBadge) {
                    countBadge.textContent = `${visibleCount} of ${districts.length} Districts`;
                }
            }
        }

        // Event: Province Changed
        provinceSelect.addEventListener('change', function() {
            if (searchInput) searchInput.value = '';
            renderDistricts(this.value, '');
        });

        // Event: Type to Quick Search
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const q = this.value.trim().toLowerCase();
                
                // If no province is selected and user typed something, attempt to auto-select province
                if (!provinceSelect.value && q.length >= 2) {
                    for (const pName in provinceDistrictMap) {
                        const matched = provinceDistrictMap[pName].some(d => d.toLowerCase().includes(q));
                        if (matched) {
                            provinceSelect.value = pName;
                            break;
                        }
                    }
                }
                
                renderDistricts(provinceSelect.value, q);
            });
        }

        // Multi-select actions
        if (isMultiple && multipleContainer) {
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    multipleContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
                });
            }
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function() {
                    multipleContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                });
            }
        }

        // Initial render
        if (provinceSelect.value) {
            renderDistricts(provinceSelect.value, '');
        } else if (initialProvince) {
            provinceSelect.value = initialProvince;
            renderDistricts(initialProvince, '');
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTerritoryPicker);
    } else {
        initTerritoryPicker();
    }
})();
</script>
