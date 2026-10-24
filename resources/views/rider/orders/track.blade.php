@extends('layouts.app')

@section('title', 'Track Delivery')
@section('page-title', '📍 Live Delivery Tracking')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet Routing Machine CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<style>
    #delivery-map {
        height: 500px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }
    .delivery-info-card {
        background: white;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-badge.pending { background: #fef3c7; color: #92400e; }
    .status-badge.assigned { background: #dbeafe; color: #1e40af; }
    .status-badge.picked_up { background: #e9d5ff; color: #6b21a8; }
    .status-badge.in_transit { background: #c7d2fe; color: #3730a3; }
    .status-badge.out_for_delivery { background: #fed7aa; color: #9a3412; }
    .status-badge.delivered { background: #d1fae5; color: #065f46; }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6b7280; font-size: 13px; }
    .info-value { font-weight: 600; font-size: 14px; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Order Header -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">
                    Order #{{ $order->order_number }}
                </h2>
                <p class="text-sm text-gray-500">
                    <i class="fas fa-calendar-alt mr-1"></i> 
                    {{ $order->created_at->format('M d, Y H:i') }}
                </p>
            </div>
            <div>
                <span class="status-badge {{ $order->status }}">
                    <i class="fas fa-circle text-xs mr-1"></i>
                    {{ $order->status_label ?? ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Map Section (Takes 2/3 of the space) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-gray-700">
                        <i class="fas fa-map text-teal-600 mr-2"></i> Live Tracking
                    </h3>
                    <div class="flex gap-2">
                        <button onclick="centerMap()" class="text-sm text-teal-600 hover:text-teal-800">
                            <i class="fas fa-crosshairs mr-1"></i> Center
                        </button>
                        <button onclick="refreshRoute()" class="text-sm text-teal-600 hover:text-teal-800">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh
                        </button>
                    </div>
                </div>
                <div id="delivery-map"></div>
                
                <!-- Map Legend -->
                <div class="flex flex-wrap gap-4 mt-3 text-xs">
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-green-500 rounded-full inline-block"></span>
                        <span class="text-gray-600">Pickup (Sender)</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-red-500 rounded-full inline-block"></span>
                        <span class="text-gray-600">Delivery (Receiver)</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-blue-500 rounded-full inline-block"></span>
                        <span class="text-gray-600">Rider Location</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-teal-500 rounded-full inline-block"></span>
                        <span class="text-gray-600">Route</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Section (Takes 1/3 of the space) -->
        <div class="lg:col-span-1">
            <!-- Delivery Info -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                <h4 class="font-semibold text-gray-700 mb-3">
                    <i class="fas fa-info-circle text-teal-600 mr-2"></i> Delivery Details
                </h4>
                
                <!-- Pickup Info -->
                <div class="bg-green-50 rounded-lg p-3 mb-3">
                    <p class="text-xs text-green-700 font-semibold uppercase">📍 Pickup Location</p>
                    <p class="font-medium text-gray-800">{{ $order->shipping_address ?? 'N/A' }}</p>
                    @if($order->delivery_latitude && $order->delivery_longitude)
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-map-pin mr-1"></i> 
                            {{ number_format($order->delivery_latitude, 6) }}, {{ number_format($order->delivery_longitude, 6) }}
                        </p>
                    @endif
                </div>
                
                <!-- Delivery Info -->
                <div class="bg-red-50 rounded-lg p-3">
                    <p class="text-xs text-red-700 font-semibold uppercase">📍 Delivery Location</p>
                    <p class="font-medium text-gray-800">{{ $order->delivery_address ?? $order->shipping_address ?? 'N/A' }}</p>
                    @if($order->delivery_latitude && $order->delivery_longitude)
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-map-pin mr-1"></i> 
                            {{ number_format($order->delivery_latitude, 6) }}, {{ number_format($order->delivery_longitude, 6) }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Distance & ETA -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                <h4 class="font-semibold text-gray-700 mb-3">
                    <i class="fas fa-route text-teal-600 mr-2"></i> Route Info
                </h4>
                <div class="space-y-2">
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-road mr-1"></i> Distance</span>
                        <span class="info-value" id="distanceDisplay">Calculating...</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-clock mr-1"></i> Estimated Time</span>
                        <span class="info-value" id="etaDisplay">Calculating...</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-user mr-1"></i> Rider</span>
                        <span class="info-value">{{ $order->rider->name ?? 'Not Assigned' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-phone mr-1"></i> Rider Contact</span>
                        <span class="info-value">{{ $order->rider->phone ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h4 class="font-semibold text-gray-700 mb-3">
                    <i class="fas fa-cog text-teal-600 mr-2"></i> Actions
                </h4>
                <div class="space-y-2">
                    <a href="{{ route('rider.orders.my') }}" class="block bg-gray-100 text-gray-700 text-center px-4 py-2 rounded-lg hover:bg-gray-200 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Back to My Orders
                    </a>
                    <a href="https://www.google.com/maps/dir/{{ $order->delivery_latitude ?? '' }},{{ $order->delivery_longitude ?? '' }}" 
                       target="_blank" 
                       class="block bg-teal-600 text-white text-center px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-external-link-alt mr-2"></i> Open in Google Maps
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script>
let map;
let routingControl;
let riderMarker;
let pickupMarker;
let deliveryMarker;
let updateInterval;

// Default coordinates (Kathmandu)
const defaultLat = 27.7172;
const defaultLng = 85.3240;

// Delivery coordinates (use order data or default)
const pickupLat = {{ $order->pickup_latitude ?? $order->delivery_latitude ?? defaultLat }};
const pickupLng = {{ $order->pickup_longitude ?? $order->delivery_longitude ?? defaultLng }};
const deliveryLat = {{ $order->delivery_latitude ?? defaultLat }};
const deliveryLng = {{ $order->delivery_longitude ?? defaultLng }};

// Rider location (if available)
const riderLat = {{ $order->rider->current_latitude ?? $order->delivery_latitude ?? defaultLat }};
const riderLng = {{ $order->rider->current_longitude ?? $order->delivery_longitude ?? defaultLng }};

document.addEventListener('DOMContentLoaded', function() {
    initializeMap();
    startLiveTracking();
});

function initializeMap() {
    // Initialize map
    map = L.map('delivery-map').setView([pickupLat, pickupLng], 14);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add pickup marker (green)
    pickupMarker = L.marker([pickupLat, pickupLng], {
        icon: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #10b981; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        })
    }).addTo(map)
    .bindPopup('<strong>📍 Pickup Location</strong><br>' + ({{ $order->shipping_address ? '"' + addslashes($order->shipping_address) + '"' : "'N/A'" }}));
    
    // Add delivery marker (red)
    deliveryMarker = L.marker([deliveryLat, deliveryLng], {
        icon: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #ef4444; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        })
    }).addTo(map)
    .bindPopup('<strong>📍 Delivery Location</strong><br>' + ({{ $order->delivery_address ? '"' + addslashes($order->delivery_address) + '"' : "'N/A'" }}));
    
    // Add rider marker (blue)
    riderMarker = L.marker([riderLat, riderLng], {
        icon: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #3b82f6; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        })
    }).addTo(map)
    .bindPopup('<strong>🛵 Rider Location</strong><br>Last updated: ' + new Date().toLocaleTimeString());
    
    // Draw route between pickup and delivery
    drawRoute();
}

function drawRoute() {
    // Remove existing route
    if (routingControl) {
        map.removeControl(routingControl);
    }
    
    // Create route from pickup to delivery
    routingControl = L.Routing.control({
        waypoints: [
            L.latLng(pickupLat, pickupLng),
            L.latLng(deliveryLat, deliveryLng)
        ],
        routeWhileDragging: false,
        showAlternatives: false,
        fitSelectedRoutes: true,
        show: true,
        lineOptions: {
            styles: [
                { color: '#0d9488', weight: 4, opacity: 0.8 },
                { color: '#0d9488', weight: 2, opacity: 0.4 }
            ]
        },
        createMarker: function() { return null; }, // Don't create markers
        addWaypoints: false,
        draggableWaypoints: false,
    }).addTo(map);
    
    // Update distance and ETA when route is calculated
    routingControl.on('routesfound', function(e) {
        const routes = e.routes;
        if (routes.length > 0) {
            const route = routes[0];
            const distance = route.summary.totalDistance / 1000; // in km
            const duration = route.summary.totalTime / 60; // in minutes
            
            document.getElementById('distanceDisplay').textContent = distance.toFixed(1) + ' km';
            
            if (duration < 60) {
                document.getElementById('etaDisplay').textContent = Math.round(duration) + ' minutes';
            } else {
                const hours = Math.floor(duration / 60);
                const mins = Math.round(duration % 60);
                document.getElementById('etaDisplay').textContent = hours + 'h ' + mins + 'm';
            }
        }
    });
}

function updateRiderLocation() {
    // Simulate rider moving (in production, fetch from API)
    // For demo, move rider slightly towards delivery
    const step = 0.001;
    const latDiff = (deliveryLat - pickupLat) * 0.1;
    const lngDiff = (deliveryLng - pickupLng) * 0.1;
    
    const newLat = riderMarker.getLatLng().lat + latDiff;
    const newLng = riderMarker.getLatLng().lng + lngDiff;
    
    riderMarker.setLatLng([newLat, newLng]);
    riderMarker.getPopup().setContent('<strong>🛵 Rider Location</strong><br>Last updated: ' + new Date().toLocaleTimeString());
    
    // Update map center to follow rider
    map.panTo([newLat, newLng]);
}

function startLiveTracking() {
    // Update rider location every 10 seconds
    updateInterval = setInterval(updateRiderLocation, 10000);
}

function centerMap() {
    // Center map to show all markers
    const bounds = L.latLngBounds([
        [pickupLat, pickupLng],
        [deliveryLat, deliveryLng],
        [riderLat, riderLng]
    ]);
    map.fitBounds(bounds, { padding: [50, 50] });
}

function refreshRoute() {
    drawRoute();
    centerMap();
}

// Cleanup interval when page is unloaded
window.addEventListener('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});

// Helper function to escape strings for JavaScript
function addslashes(str) {
    return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}
</script>
@endpush
@endsection