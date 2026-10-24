@extends('layouts.app')

@section('title', 'Add Domestic Rate')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Add Domestic Rate</h1>
            <p class="text-sm text-gray-500 mt-1">Create a new domestic delivery rate</p>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('domestic.rates.store') }}">
                @csrf

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
                        <label class="block text-sm font-medium mb-1">Partner *</label>
                        <select name="partner_id" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">Select Partner</option>
                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Service Type *</label>
                        <select name="service_type" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="flash" {{ old('service_type') === 'flash' ? 'selected' : '' }}>FLASH</option>
                            <option value="same_day" {{ old('service_type') === 'same_day' ? 'selected' : '' }}>SAME DAY</option>
                            <option value="standard" {{ old('service_type') === 'standard' ? 'selected' : '' }}>STANDARD</option>
                            <option value="himalayan" {{ old('service_type') === 'himalayan' ? 'selected' : '' }}>HIMALAYAN</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Origin Zone *</label>
                        <select name="origin_zone_id" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">Select Origin Zone</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ old('origin_zone_id') == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->zone_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Destination Zone *</label>
                        <select name="destination_zone_id" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">Select Destination Zone</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ old('destination_zone_id') == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->zone_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Weight From (kg) *</label>
                        <input type="number" name="weight_from" step="0.01" value="{{ old('weight_from', 0) }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Weight To (kg) *</label>
                        <input type="number" name="weight_to" step="0.01" value="{{ old('weight_to', 10) }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Base Rate (Rs.) *</label>
                        <input type="number" name="base_rate" step="0.01" value="{{ old('base_rate', 0) }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Per KG Rate (Rs.) *</label>
                        <input type="number" name="per_kg_rate" step="0.01" value="{{ old('per_kg_rate', 0) }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Estimated Hours</label>
                        <input type="number" name="estimated_hours" value="{{ old('estimated_hours') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Estimated Days</label>
                        <input type="number" name="estimated_days" value="{{ old('estimated_days') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Effective From *</label>
                        <input type="date" name="effective_from" value="{{ old('effective_from', date('Y-m-d')) }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Effective To</label>
                        <input type="date" name="effective_to" value="{{ old('effective_to') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <div class="mt-6 flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-save mr-2"></i> Create Rate
                    </button>
                    <a href="{{ route('domestic.rates') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection