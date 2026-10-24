@extends('layouts.partner')

@section('title', 'Create Zone')
@section('page-title', 'Create Delivery Zone')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Create Delivery Zone</h1>
                <p class="text-sm text-gray-500 mt-1">Create zones within your operating district</p>
            </div>
            <a href="{{ route('partner.zones.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>

        <div class="p-6">
            <!-- Info Alert -->
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-4 flex items-start gap-3">
                <i class="fas fa-info-circle mt-1"></i>
                <div>
                    <p class="font-medium">Zone Creation Requires Admin Approval</p>
                    <p class="text-sm">Your zone will be submitted for admin review and approval. You'll be notified once approved.</p>
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('partner.zones.store') }}" id="zoneForm">
                @csrf

                <!-- Partner's Operating Territory Selection -->
                <div class="bg-slate-50 rounded-2xl p-5 mb-6 border border-slate-200/80">
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-200/60">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Coverage Territory & District</h3>
                            <p class="text-xs text-slate-500">Specify the province and district covered by this delivery zone.</p>
                        </div>
                        @if(!empty($partner->district))
                            <span class="px-2.5 py-1 bg-teal-100 text-teal-800 rounded-full text-xs font-semibold">
                                <i class="fas fa-check-circle mr-1 text-[10px]"></i> {{ $partner->district }}
                            </span>
                        @endif
                    </div>
                    <x-nepal-territory-picker 
                        :isMultiple="true"
                        provinceName="province" 
                        districtName="districts[]" 
                        :selectedProvince="old('province', $partner->province ?? null)" 
                        :selectedDistrict="old('districts', $partner->getOperatingDistricts())" 
                        provinceLabel="Operating Province / Sector" 
                        districtLabel="Districts Under Selected Province" 
                        idPrefix="partner_zone_geo" 
                        :required="true"
                        helperText="Choose a province to display its districts. Select one, multiple, or all districts for this delivery zone." />
                </div>

                <!-- Zone Name -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Zone Name <span class="text-red-500">*</span></label>
                    <input type="text" name="zone_name" value="{{ old('zone_name') }}" required 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                           placeholder="e.g., North Zone, South Zone, East Zone">
                    <p class="text-xs text-gray-500 mt-1">Give a unique name for your delivery zone within {{ $partner->district ?? 'your district' }}</p>
                </div>

                <!-- Zone Type (Hidden - Partner cannot change) -->
                <input type="hidden" name="zone_type" value="partner">

                <!-- Municipalities -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Municipalities (Optional)</label>
                    <input type="text" name="municipalities" value="{{ old('municipalities') }}" 
                           placeholder="e.g., Kathmandu Metro, Lalitpur Metro" 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <p class="text-xs text-gray-500 mt-1">Comma separated list of municipalities in this zone</p>
                </div>

                <!-- Wards -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Wards Covered (Optional)</label>
                    <input type="text" name="wards" value="{{ old('wards') }}" 
                           placeholder="e.g., 1,2,3,4,5" 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <p class="text-xs text-gray-500 mt-1">Comma separated list of ward numbers</p>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Description (Optional)</label>
                    <textarea name="description" rows="2" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Additional information about this zone">{{ old('description') }}</textarea>
                </div>

                <!-- Service Rates Section -->
                <div class="border-t pt-6 mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Service Rates</h3>
                    <p class="text-sm text-gray-500 mb-4">Define rates for each service in this zone</p>

                    <!-- Flash Service -->
                    <div class="bg-blue-50 rounded-lg p-4 mb-4 border border-blue-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-blue-800">
                                <i class="fas fa-bolt mr-2"></i> FLASH Service 
                                @if(!$services['flash'])
                                    <span class="text-xs text-gray-500 font-normal">(Not Active - Contact Admin)</span>
                                @endif
                            </h4>
                            @if(!$services['flash'])
                                <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full">Inactive</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Base Rate (NPR)</label>
                                <input type="number" name="flash_base_rate" step="0.01" value="{{ old('flash_base_rate', 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                       {{ !$services['flash'] ? 'disabled' : '' }}>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Per KG Rate (NPR)</label>
                                <input type="number" name="flash_per_kg_rate" step="0.01" value="{{ old('flash_per_kg_rate', 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       {{ !$services['flash'] ? 'disabled' : '' }}>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Estimated Hours</label>
                                <input type="number" name="flash_estimated_hours" value="{{ old('flash_estimated_hours', 2) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       {{ !$services['flash'] ? 'disabled' : '' }}>
                            </div>
                        </div>
                    </div>

                    <!-- Same Day Service -->
                    <div class="bg-orange-50 rounded-lg p-4 mb-4 border border-orange-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-orange-800">
                                <i class="fas fa-clock mr-2"></i> SAME DAY Service
                                @if(!$services['same_day'])
                                    <span class="text-xs text-gray-500 font-normal">(Not Active - Contact Admin)</span>
                                @endif
                            </h4>
                            @if(!$services['same_day'])
                                <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full">Inactive</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Base Rate (NPR)</label>
                                <input type="number" name="same_day_base_rate" step="0.01" value="{{ old('same_day_base_rate', 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                       {{ !$services['same_day'] ? 'disabled' : '' }}>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Per KG Rate (NPR)</label>
                                <input type="number" name="same_day_per_kg_rate" step="0.01" value="{{ old('same_day_per_kg_rate', 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                       {{ !$services['same_day'] ? 'disabled' : '' }}>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Estimated Hours</label>
                                <input type="number" name="same_day_estimated_hours" value="{{ old('same_day_estimated_hours', 6) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                       {{ !$services['same_day'] ? 'disabled' : '' }}>
                            </div>
                        </div>
                    </div>

                    <!-- Standard Service -->
                    <div class="bg-green-50 rounded-lg p-4 mb-4 border border-green-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-green-800">
                                <i class="fas fa-truck mr-2"></i> STANDARD Service
                                <span class="text-xs text-gray-500 font-normal">(Always Active)</span>
                            </h4>
                            <span class="text-xs bg-green-200 text-green-700 px-2 py-1 rounded-full">Active</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Base Rate (NPR)</label>
                                <input type="number" name="standard_base_rate" step="0.01" value="{{ old('standard_base_rate', 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Per KG Rate (NPR)</label>
                                <input type="number" name="standard_per_kg_rate" step="0.01" value="{{ old('standard_per_kg_rate', 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Estimated Days</label>
                                <input type="number" name="standard_estimated_hours" value="{{ old('standard_estimated_hours', 48) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                        </div>
                    </div>

                    <!-- Himalayan Service -->
                    <div class="bg-purple-50 rounded-lg p-4 mb-4 border border-purple-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-purple-800">
                                <i class="fas fa-mountain mr-2"></i> HIMALAYAN Service
                                @if(!$services['himalayan'])
                                    <span class="text-xs text-gray-500 font-normal">(Not Active - Contact Admin)</span>
                                @endif
                            </h4>
                            @if(!$services['himalayan'])
                                <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full">Inactive</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Base Rate (NPR)</label>
                                <input type="number" name="himalayan_base_rate" step="0.01" value="{{ old('himalayan_base_rate', 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                       {{ !$services['himalayan'] ? 'disabled' : '' }}>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Per KG Rate (NPR)</label>
                                <input type="number" name="himalayan_per_kg_rate" step="0.01" value="{{ old('himalayan_per_kg_rate', 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                       {{ !$services['himalayan'] ? 'disabled' : '' }}>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Estimated Days</label>
                                <input type="number" name="himalayan_estimated_hours" value="{{ old('himalayan_estimated_hours', 96) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                       {{ !$services['himalayan'] ? 'disabled' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t mt-6">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-save mr-2"></i> Create Zone (Submit for Approval)
                    </button>
                    <a href="{{ route('partner.zones.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Simple validation
    document.getElementById('zoneForm').addEventListener('submit', function(e) {
        var zoneName = document.querySelector('input[name="zone_name"]').value.trim();
        if (!zoneName) {
            e.preventDefault();
            alert('Please enter a zone name.');
            return false;
        }
        var checkedDistricts = document.querySelectorAll('input[name="districts[]"]:checked');
        if (checkedDistricts.length === 0) {
            e.preventDefault();
            alert('Please select at least one district under your chosen province.');
            return false;
        }
        return true;
    });
</script>
@endpush