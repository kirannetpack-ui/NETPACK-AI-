{{-- resources/views/domestic/pickup/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Request Domestic Pickup')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-teal-600 to-emerald-600 px-6 py-4">
            <h1 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="fas fa-truck-fast"></i>
                <span>Request Domestic Pickup</span>
            </h1>
            <p class="text-teal-100 text-xs mt-1">Fast delivery across Nepal with real-time tracking</p>
        </div>
        
        <form method="POST" action="{{ route('domestic.pickup.store') }}" class="p-6">
            @csrf
            
            <!-- Service Tier Selection (Dynamic) -->
            <div class="mb-6">
                <label class="block text-sm font-medium mb-3">Select Service Tier</label>
                @php
                    $availableServices = \App\Models\LogisticsService::where('is_active', true)
                        ->whereIn('category', ['domestic', 'ecommerce'])
                        ->orderBy('sort_order', 'asc')
                        ->get();
                @endphp
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @forelse($availableServices as $svc)
                        <label class="border rounded-xl p-3 cursor-pointer hover:bg-teal-50 dark:hover:bg-slate-800 transition block">
                            <input type="radio" name="service_tier" value="{{ $svc->code }}" {{ $loop->first ? 'checked' : '' }} required>
                            <span class="font-medium text-sm ml-1">{{ $svc->name }}</span>
                            <span class="block text-[11px] text-teal-600 dark:text-teal-400 font-mono mt-1">⏱️ {{ $svc->transit_display }}</span>
                        </label>
                    @empty
                        <label class="border rounded-xl p-3 cursor-pointer hover:bg-teal-50 transition">
                            <input type="radio" name="service_tier" value="standard" checked required>
                            <span class="font-medium text-sm ml-1">Standard (1-3 days)</span>
                        </label>
                    @endforelse
                </div>
            </div>
            
            <!-- Pickup Address Section -->
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <h3 class="font-medium text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-green-600"></i>
                    <span>Pickup Address</span>
                </h3>
                <div class="space-y-3">
                    <div class="flex gap-2">
                        <input type="text" name="pickup_address" id="pickup_address" placeholder="Search or enter pickup address" required
                               class="flex-1 px-3 py-2 border rounded-xl text-sm">
                        <button type="button" onclick="if(window.locationMap) window.locationMap.useMyLocation('pickup')" 
                                class="bg-green-600 text-white px-3 py-2 rounded-xl text-sm whitespace-nowrap">
                            <i class="fas fa-location-dot mr-1"></i> Use My Location
                        </button>
                    </div>
                    <div class="mt-3">
                        <x-nepal-territory-picker 
                            provinceName="pickup_province" 
                            districtName="pickup_district" 
                            provinceLabel="Pickup Province / Sector" 
                            districtLabel="Pickup District (Under Province)" 
                            idPrefix="pickup_geo" 
                            :required="true" />
                    </div>
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <input type="text" name="pickup_municipality" placeholder="Municipality / Local Body" class="px-3 py-2 border rounded-xl text-sm" required>
                        <input type="text" name="pickup_ward_no" placeholder="Ward No" class="px-3 py-2 border rounded-xl text-sm" required>
                    </div>
                </div>
            </div>
            
            <!-- Delivery Address Section -->
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <h3 class="font-medium text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-flag-checkered text-red-600"></i>
                    <span>Delivery Address</span>
                </h3>
                <div class="space-y-3">
                    <div class="flex gap-2">
                        <input type="text" name="delivery_address" id="delivery_address" placeholder="Search or enter delivery address" required
                               class="flex-1 px-3 py-2 border rounded-xl text-sm">
                        <button type="button" onclick="if(window.locationMap) window.locationMap.useMyLocation('delivery')" 
                                class="bg-red-600 text-white px-3 py-2 rounded-xl text-sm whitespace-nowrap">
                            <i class="fas fa-location-dot mr-1"></i> Use My Location
                        </button>
                    </div>
                    <div class="mt-3">
                        <x-nepal-territory-picker 
                            provinceName="delivery_province" 
                            districtName="delivery_district" 
                            provinceLabel="Delivery Province / Sector" 
                            districtLabel="Delivery District (Under Province)" 
                            idPrefix="delivery_geo" 
                            :required="true" />
                    </div>
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <input type="text" name="delivery_municipality" placeholder="Municipality / Local Body" class="px-3 py-2 border rounded-xl text-sm" required>
                        <input type="text" name="delivery_ward_no" placeholder="Ward No" class="px-3 py-2 border rounded-xl text-sm" required>
                    </div>
                </div>
            </div>
            
            <!-- Location Map -->
            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Live Location Map</label>
                @include('components.location-map', [
                    'pickupLat' => 27.7172,
                    'pickupLng' => 85.3240,
                    'deliveryLat' => 27.7172,
                    'deliveryLng' => 85.3240,
                    'mapId' => 'pickup-map'
                ])
            </div>
            
            <!-- Package Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Items Description</label>
                    <textarea name="items_description" rows="3" placeholder="Describe the items..." required
                              class="w-full px-3 py-2 border rounded-xl text-sm"></textarea>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Estimated Weight (kg)</label>
                        <input type="number" name="estimated_weight_kg" step="0.1" required
                               class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Scheduled Pickup Time</label>
                        <input type="datetime-local" name="scheduled_pickup_time" required
                               class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="route_distance" id="route_distance">
            <input type="hidden" name="route_duration" id="route_duration">
            
            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-xl hover:bg-teal-700 transition text-sm font-medium">
                    <i class="fas fa-check mr-2"></i> Submit Pickup Request
                </button>
                <a href="{{ route('domestic.pickup.my-requests') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-xl hover:bg-gray-300 transition text-sm font-medium">
                    My Requests
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Store map instance reference
window.locationMap = null;

// Initialize when map component is ready
document.addEventListener('alpine:init', () => {
    // Wait for map component
    setTimeout(() => {
        const mapComponent = document.querySelector('[x-data]');
        if (mapComponent && mapComponent.__x) {
            window.locationMap = mapComponent.__x;
        }
    }, 1000);
});
</script>
@endsection