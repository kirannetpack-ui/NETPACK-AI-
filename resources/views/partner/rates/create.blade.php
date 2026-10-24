{{-- resources/views/partner/rates/create.blade.php --}}
@extends('layouts.partner')

@section('title', 'Add Pricing Rate')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h1 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-plus-circle text-teal-600"></i>
                <span>Add Pricing Rate</span>
            </h1>
        </div>
        
        <form method="POST" action="{{ route('partner.rates.store') }}" class="p-6">
            @csrf
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Service Zone *</label>
                    <select name="zone_id" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="">Select Zone</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->zone_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Service Tier *</label>
                    <select name="service_tier" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="">Select Tier</option>
                        @foreach($serviceTiers as $tier)
                            <option value="{{ $tier }}">{{ ucfirst($tier) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Base Rate (NPR) *</label>
                    <input type="number" name="base_rate" step="0.01" required placeholder="0.00"
                           class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Per Kg Rate (NPR) *</label>
                    <input type="number" name="per_kg_rate" step="0.01" required placeholder="0.00"
                           class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Per Km Rate (NPR) *</label>
                    <input type="number" name="per_km_rate" step="0.01" required placeholder="0.00"
                           class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Logistical Charge (NPR)</label>
                    <input type="number" name="logistical_charge" step="0.01" placeholder="0.00"
                           class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Additional Charge (NPR)</label>
                    <input type="number" name="additional_charge" step="0.01" placeholder="0.00"
                           class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Additional Charge Reason</label>
                    <input type="text" name="additional_charge_reason" placeholder="e.g., Remote area surcharge"
                           class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Estimated Hours (Flash/Same Day)</label>
                    <input type="number" name="estimated_hours" placeholder="Hours"
                           class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Estimated Days (Standard/Himalayan)</label>
                    <input type="number" name="estimated_days" placeholder="Days"
                           class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700">
                    Create Rate
                </button>
                <a href="{{ route('partner.rates.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection