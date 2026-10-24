@extends('layouts.app')

@section('title', 'Edit Delivery Zone')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Edit Delivery Zone</h1>
            <p class="text-sm text-gray-500 mt-1">Update delivery zone</p>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('domestic.zones.update', $zone->id) }}">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Zone Name *</label>
                        <input type="text" name="zone_name" value="{{ old('zone_name', $zone->zone_name) }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Partner *</label>
                        <select name="partner_id" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" {{ old('partner_id', $zone->partner_id) == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Zone Type *</label>
                        <select name="zone_type" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="urban" {{ old('zone_type', $zone->zone_type) === 'urban' ? 'selected' : '' }}>Urban</option>
                            <option value="semi_urban" {{ old('zone_type', $zone->zone_type) === 'semi_urban' ? 'selected' : '' }}>Semi-Urban</option>
                            <option value="rural" {{ old('zone_type', $zone->zone_type) === 'rural' ? 'selected' : '' }}>Rural</option>
                            <option value="hilly" {{ old('zone_type', $zone->zone_type) === 'hilly' ? 'selected' : '' }}>Hilly</option>
                            <option value="himalayan" {{ old('zone_type', $zone->zone_type) === 'himalayan' ? 'selected' : '' }}>Himalayan</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        @php
                            $selectedDistricts = is_array($zone->districts) ? $zone->districts : (is_string($zone->districts) ? explode(',', $zone->districts) : []);
                            $selectedDistricts = array_values(array_filter(array_map('trim', $selectedDistricts)));
                            $detectedProvince = null;
                            if (!empty($selectedDistricts)) {
                                $detectedProvince = \App\Services\NepalGeographicalService::getProvinceForDistrict($selectedDistricts[0]);
                            }
                        @endphp
                        <x-nepal-territory-picker 
                            :isMultiple="true"
                            provinceName="province"
                            districtName="districts[]"
                            :selectedProvince="old('province', $detectedProvince)"
                            :selectedDistrict="old('districts', $selectedDistricts)"
                            provinceLabel="Operating Province / Sector"
                            districtLabel="Districts Under Province"
                            helperText="Choose a province to display strictly its affiliated districts. Checkboxes allow selecting multiple coverage districts with type-to-search."
                            :required="false"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Municipalities</label>
                        <input type="text" name="municipalities" value="{{ old('municipalities', is_array($zone->municipalities) ? implode(', ', $zone->municipalities) : $zone->municipalities) }}" 
                               placeholder="e.g., Kathmandu Metro, Lalitpur Metro" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <p class="text-xs text-gray-500 mt-1">Comma separated list</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Wards</label>
                        <input type="text" name="wards" value="{{ old('wards', is_array($zone->wards) ? implode(', ', $zone->wards) : $zone->wards) }}" 
                               placeholder="e.g., 1,2,3,4,5" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <p class="text-xs text-gray-500 mt-1">Comma separated list of ward numbers</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea name="description" rows="2" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('description', $zone->description) }}</textarea>
                    </div>

                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $zone->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            <span class="text-sm font-medium">Active</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-save mr-2"></i> Update Zone
                    </button>
                    <a href="{{ route('domestic.zones') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection