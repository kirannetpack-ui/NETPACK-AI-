@extends('layouts.app')

@section('title', 'Track Delivery')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<style>
    #delivery-map { height: 500px; border-radius: 12px; border: 1px solid #e5e7eb; }
    .info-card { background: white; border-radius: 12px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">📍 Track Delivery #{{ $delivery->id }}</h1>
                <p class="text-sm text-gray-500">Order #{{ $delivery->order->order_number ?? 'N/A' }}</p>
            </div>
            <a href="{{ route('admin.riders.dashboard') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div id="delivery-map"></div>
                <div class="flex gap-4 mt-3 text-xs">
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-green-500 rounded-full inline-block"></span>
                        <span class="text-gray-600">Pickup</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-red-500 rounded-full inline-block"></span>
                        <span class="text-gray-600">Delivery</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-blue-500 rounded-full inline-block"></span>
                        <span class="text-gray-600">Rider Location</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                <h4 class="font-semibold text-gray-700 mb-3">📋 Delivery Details</h4>
                <div class="space-y-2">
                    <div class="flex justify-between border-b py-1">
                        <span class="text-gray-500">Rider</span>
                        <span class="font-medium">{{ $delivery->rider->name ?? 'Not Assigned' }}</span>
                    </div>
                    <div class="flex justify-between border-b py-1">
                        <span class="text-gray-500">Status</span>
                        <span class="font-medium">{{ $delivery->status_label }}</span>
                    </div>
                    <div class="flex justify-between border-b py-1">
                        <span class="text-gray-500">Customer</span>
                        <span class="font-medium">{{ $delivery->recipient_name }}</span>
                    </div>
                    <div class="flex justify-between border-b py-1">
                        <span class="text-gray-500">Phone</span>
                        <span class="font-medium">{{ $delivery->recipient_phone }}</span>
                    </div>
                    <div class="flex justify-between border-b py-1">
                        <span class="text-gray-500">Delivery Fee</span>
                        <span class="font-medium text-teal-600">Rs. {{ number_format($delivery->delivery_fee, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-b py-1">
                        <span class="text-gray-500">Address</span>
                        <span class="font-medium text-sm">{{ $delivery->address }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pickupLat = {{ $delivery->order->pickup_latitude ?? 27.7172 }};
    const pickupLng = {{ $delivery->order->pickup_longitude ?? 85.3240 }};
    const deliveryLat = {{ $delivery->latitude ?? 27.7172 }};
    const deliveryLng = {{ $delivery->longitude ?? 85.3240 }};
    const riderLat = {{ $delivery->rider->current_latitude ?? $delivery->latitude ?? 27.7172 }};
    const riderLng = {{ $delivery->rider->current_longitude ?? $delivery->longitude ?? 85.3240 }};

    const map = L.map('delivery-map').setView([deliveryLat, deliveryLng], 14);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Pickup marker (green)
    L.marker([pickupLat, pickupLng], {
        icon: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #22c55e; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        })
    }).addTo(map).bindPopup('<strong>📍 Pickup Location</strong>');

    // Delivery marker (red)
    L.marker([deliveryLat, deliveryLng], {
        icon: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #ef4444; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        })
    }).addTo(map).bindPopup('<strong>📍 Delivery Location</strong>');

    // Rider marker (blue)
    L.marker([riderLat, riderLng], {
        icon: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #3b82f6; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        })
    }).addTo(map).bindPopup('<strong>🛵 Rider Location</strong>');

    // Draw route
    L.Routing.control({
        waypoints: [
            L.latLng(pickupLat, pickupLng),
            L.latLng(deliveryLat, deliveryLng)
        ],
        routeWhileDragging: false,
        showAlternatives: false,
        fitSelectedRoutes: true,
        lineOptions: {
            styles: [{ color: '#0d9488', weight: 4, opacity: 0.8 }]
        },
        createMarker: function() { return null; },
        addWaypoints: false,
        draggableWaypoints: false,
    }).addTo(map);
});
</script>
@endpush
@endsection