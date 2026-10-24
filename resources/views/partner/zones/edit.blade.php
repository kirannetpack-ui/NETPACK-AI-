@extends('layouts.partner')

@section('title', 'Edit Zone')
@section('page-title', 'Edit Delivery Zone')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--multiple {
        min-height: 45px !important;
        border-radius: 8px !important;
        border-color: #d1d5db !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #0d9488 !important;
        color: white !important;
        border: none !important;
        border-radius: 20px !important;
        padding: 4px 12px !important;
        font-size: 13px !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white !important;
        margin-right: 6px !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #f87171 !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Edit Delivery Zone</h1>
                <p class="text-sm text-gray-500 mt-1">Update zone details and service rates</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('partner.zones.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Info Alert -->
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-4 flex items-start gap-3">
                <i class="fas fa-info-circle mt-1"></i>
                <div>
                    <p class="font-medium">Rate Changes will be Notified to Admins</p>
                    <p class="text-sm">Any changes you make to rates will be automatically notified to the administrators for review.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('partner.zones.update', $zone->id) }}" id="zoneForm">
                @csrf
                @method('PUT')

                <!-- Zone Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Zone Name <span class="text-red-500">*</span></label>
                        <input type="text" name="zone_name" value="{{ old('zone_name', $zone->zone_name) }}" required 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                               placeholder="e.g., Kathmandu Valley, Pokhara Region">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Zone Type <span class="text-red-500">*</span></label>
                        <select name="zone_type" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">Select Type</option>
                            <option value="urban" {{ old('zone_type', $zone->zone_type) === 'urban' ? 'selected' : '' }}>🏙️ Urban (Major Cities)</option>
                            <option value="semi_urban" {{ old('zone_type', $zone->zone_type) === 'semi_urban' ? 'selected' : '' }}>🏘️ Semi-Urban (Towns)</option>
                            <option value="rural" {{ old('zone_type', $zone->zone_type) === 'rural' ? 'selected' : '' }}>🌾 Rural (Villages)</option>
                            <option value="hilly" {{ old('zone_type', $zone->zone_type) === 'hilly' ? 'selected' : '' }}>⛰️ Hilly Region</option>
                            <option value="himalayan" {{ old('zone_type', $zone->zone_type) === 'himalayan' ? 'selected' : '' }}>🏔️ Himalayan Region</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <x-nepal-territory-picker 
                            :isMultiple="true" 
                            provinceName="province" 
                            districtName="districts[]" 
                            :selectedDistrict="$zone->districts ?? []" 
                            provinceLabel="Select Province / Sector" 
                            districtLabel="Districts Under Selected Province" 
                            idPrefix="partner_edit_geo" 
                            helperText="Choose a province to display its districts. Checkboxes allow selecting multiple coverage districts with type-to-search."
                            :required="true" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Municipalities (Optional)</label>
                        <input type="text" name="municipalities" value="{{ old('municipalities', is_array($zone->municipalities) ? implode(', ', $zone->municipalities) : $zone->municipalities) }}" 
                               placeholder="e.g., Kathmandu Metro, Lalitpur Metro" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Wards (Optional)</label>
                        <input type="text" name="wards" value="{{ old('wards', is_array($zone->wards) ? implode(', ', $zone->wards) : $zone->wards) }}" 
                               placeholder="e.g., 1,2,3,4,5" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2">Description (Optional)</label>
                        <textarea name="description" rows="2" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('description', $zone->description) }}</textarea>
                    </div>
                </div>

                <!-- Service Rates Section -->
                <div class="border-t pt-6 mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Service Rates</h3>
                    <p class="text-sm text-gray-500 mb-4">Update rates for each service. Changes will be notified to admins.</p>

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
                                <input type="number" name="flash_base_rate" step="0.01" value="{{ old('flash_base_rate', $zone->flash_base_rate ?? 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       {{ !$services['flash'] ? 'disabled' : '' }}>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Per KG Rate (NPR)</label>
                                <input type="number" name="flash_per_kg_rate" step="0.01" value="{{ old('flash_per_kg_rate', $zone->flash_per_kg_rate ?? 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       {{ !$services['flash'] ? 'disabled' : '' }}>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Estimated Hours</label>
                                <input type="number" name="flash_estimated_hours" value="{{ old('flash_estimated_hours', $zone->flash_estimated_hours ?? 2) }}" 
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
                                <input type="number" name="same_day_base_rate" step="0.01" value="{{ old('same_day_base_rate', $zone->same_day_base_rate ?? 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                       {{ !$services['same_day'] ? 'disabled' : '' }}>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Per KG Rate (NPR)</label>
                                <input type="number" name="same_day_per_kg_rate" step="0.01" value="{{ old('same_day_per_kg_rate', $zone->same_day_per_kg_rate ?? 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                       {{ !$services['same_day'] ? 'disabled' : '' }}>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Estimated Hours</label>
                                <input type="number" name="same_day_estimated_hours" value="{{ old('same_day_estimated_hours', $zone->same_day_estimated_hours ?? 6) }}" 
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
                                <input type="number" name="standard_base_rate" step="0.01" value="{{ old('standard_base_rate', $zone->standard_base_rate ?? 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Per KG Rate (NPR)</label>
                                <input type="number" name="standard_per_kg_rate" step="0.01" value="{{ old('standard_per_kg_rate', $zone->standard_per_kg_rate ?? 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Estimated Days</label>
                                <input type="number" name="standard_estimated_hours" value="{{ old('standard_estimated_hours', $zone->standard_estimated_hours ?? 48) }}" 
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
                                <input type="number" name="himalayan_base_rate" step="0.01" value="{{ old('himalayan_base_rate', $zone->himalayan_base_rate ?? 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                       {{ !$services['himalayan'] ? 'disabled' : '' }}>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Per KG Rate (NPR)</label>
                                <input type="number" name="himalayan_per_kg_rate" step="0.01" value="{{ old('himalayan_per_kg_rate', $zone->himalayan_per_kg_rate ?? 0) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                       {{ !$services['himalayan'] ? 'disabled' : '' }}>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Estimated Days</label>
                                <input type="number" name="himalayan_estimated_hours" value="{{ old('himalayan_estimated_hours', $zone->himalayan_estimated_hours ?? 96) }}" 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                       {{ !$services['himalayan'] ? 'disabled' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t mt-6">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-save mr-2"></i> Update Zone
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
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('zoneForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const checked = form.querySelectorAll('input[name="districts[]"]:checked');
                if (checked.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one district under your chosen province.');
                    return false;
                }
                return true;
            });
        }
    });
</script>
@endpush