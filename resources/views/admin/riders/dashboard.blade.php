@extends('layouts.app')

@section('title', 'Rider Monitoring')
@section('page-title', '🚛 Rider Monitoring Dashboard')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #rider-map { height: 450px; border-radius: 12px; border: 1px solid #e5e7eb; }
    .rider-marker { 
        width: 30px; 
        height: 30px; 
        border-radius: 50%; 
        display: flex; 
        align-items: center; 
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 12px;
        border: 2px solid white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    .rider-marker.online { background: #22c55e; }
    .rider-marker.offline { background: #6b7280; }
    .rider-marker.busy { background: #eab308; }
    .status-badge {
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .status-badge.online { background: #dcfce7; color: #166534; }
    .status-badge.offline { background: #f3f4f6; color: #4b5563; }
    .status-badge.busy { background: #fef3c7; color: #92400e; }
    .status-badge.in_transit { background: #dbeafe; color: #1e40af; }
    .status-badge.delivered { background: #d1fae5; color: #065f46; }
    .rider-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .rider-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .pulse-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        animation: pulse 1.5s ease-in-out infinite;
    }
    .pulse-dot.active { background: #22c55e; }
    .pulse-dot.busy { background: #eab308; }
    @keyframes pulse {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.2); }
        100% { opacity: 1; transform: scale(1); }
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Total Riders</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($riders->count()) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">Active Orders</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($activeOrders) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Online Riders</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($onlineRiders) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-sm text-gray-500">Today's Earnings</p>
            <p class="text-2xl font-bold text-purple-600">Rs. {{ number_format($todayEarnings, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-teal-500">
            <p class="text-sm text-gray-500">Total Deliveries</p>
            <p class="text-2xl font-bold text-teal-600">{{ number_format($totalDeliveries) }}</p>
        </div>
    </div>

    <!-- Map Section -->
    <div class="bg-white rounded-xl shadow-sm mb-6">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">📍 Live Rider Locations</h3>
                <p class="text-sm text-gray-500">Real-time tracking of all active riders</p>
            </div>
            <div class="flex gap-3">
                <button onclick="refreshMap()" class="bg-teal-600 text-white px-3 py-1 rounded-lg hover:bg-teal-700 transition text-sm">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                </button>
                <button onclick="centerMap()" class="bg-gray-200 text-gray-700 px-3 py-1 rounded-lg hover:bg-gray-300 transition text-sm">
                    <i class="fas fa-crosshairs mr-1"></i> Center
                </button>
            </div>
        </div>
        <div class="p-4">
            <div id="rider-map"></div>
            <div class="flex flex-wrap gap-4 mt-3 text-xs">
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 bg-green-500 rounded-full inline-block"></span>
                    <span class="text-gray-600">Online Riders</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 bg-yellow-500 rounded-full inline-block"></span>
                    <span class="text-gray-600">Busy (On Delivery)</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 bg-gray-400 rounded-full inline-block"></span>
                    <span class="text-gray-600">Offline</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 bg-blue-500 rounded-full inline-block"></span>
                    <span class="text-gray-600">Delivery Location</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Deliveries List -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">🔄 Active Deliveries</h3>
                <p class="text-sm text-gray-500">All ongoing deliveries with rider information</p>
            </div>
            <a href="{{ route('admin.riders.export') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm">
                <i class="fas fa-file-export mr-2"></i> Export Report
            </a>
        </div>
        <div class="p-4">
            @if($activeDeliveries->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Delivery #</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Order</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Rider</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Location</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">ETA</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeDeliveries as $delivery)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-mono text-sm">#{{ $delivery->id }}</td>
                                    <td class="py-3 px-4">#{{ $delivery->order->order_number ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="pulse-dot {{ $delivery->rider->is_online ? 'active' : '' }}"></span>
                                            {{ $delivery->rider->name ?? 'Not Assigned' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="status-badge {{ $delivery->status }}">
                                            {{ $delivery->status_label ?? ucfirst($delivery->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm">{{ $delivery->address ? Str::limit($delivery->address, 30) : 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm">{{ $delivery->created_at->diffForHumans() }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex gap-2">
                                            <a href="{{ route('admin.riders.track-delivery', $delivery->id) }}" 
                                               class="text-blue-600 hover:text-blue-800" title="Track">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </a>
                                            <a href="{{ route('admin.riders.details', $delivery->rider_id ?? 0) }}" 
                                               class="text-teal-600 hover:text-teal-800" title="Rider Details">
                                                <i class="fas fa-user"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-truck text-4xl block mb-2 text-gray-300"></i>
                    <p>No active deliveries at the moment</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Rider Performance -->
    <div class="mt-6 bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">📊 Rider Performance</h3>
        </div>
        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Rider</th>
                            <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Total Deliveries</th>
                            <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Total Earnings</th>
                            <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Deposit Balance</th>
                            <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Rating</th>
                            <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($riderStats as $rider)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3 font-medium">{{ $rider->name }}</td>
                                <td class="py-2 px-3">{{ $rider->total_deliveries ?? 0 }}</td>
                                <td class="py-2 px-3">Rs. {{ number_format($rider->total_earnings ?? 0, 2) }}</td>
                                <td class="py-2 px-3">Rs. {{ number_format($rider->rider_deposit_balance ?? 0, 2) }}</td>
                                <td class="py-2 px-3">{{ number_format($rider->rating ?? 0, 1) }} ★</td>
                                <td class="py-2 px-3">
                                    <span class="status-badge {{ $rider->is_online ? 'online' : 'offline' }}">
                                        {{ $rider->is_online ? '🟢 Online' : '🔴 Offline' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map;
let riderMarkers = {};
let updateInterval;

document.addEventListener('DOMContentLoaded', function() {
    initializeMap();
    loadRiderLocations();
    // Update every 30 seconds
    updateInterval = setInterval(loadRiderLocations, 30000);
});

function initializeMap() {
    map = L.map('rider-map').setView([27.7172, 85.3240], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
}

function loadRiderLocations() {
    fetch('{{ route("admin.riders.locations") }}')
        .then(response => response.json())
        .then(data => {
            updateMarkers(data);
        })
        .catch(error => console.error('Error loading rider locations:', error));
}

function updateMarkers(riders) {
    const currentIds = Object.keys(riderMarkers);
    const newIds = [];
    
    riders.forEach(rider => {
        newIds.push(rider.id.toString());
        
        const lat = parseFloat(rider.latitude);
        const lng = parseFloat(rider.longitude);
        
        if (isNaN(lat) || isNaN(lng)) return;
        
        if (riderMarkers[rider.id]) {
            // Update existing marker
            riderMarkers[rider.id].setLatLng([lat, lng]);
        } else {
            // Create new marker
            const statusClass = rider.delivery_status ? 'busy' : (rider.status === 'online' ? 'online' : 'offline');
            const marker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: `<div class="rider-marker ${statusClass}">${rider.name.charAt(0)}</div>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                })
            }).addTo(map);
            
            const popupContent = `
                <div class="p-2">
                    <p class="font-bold">${rider.name}</p>
                    <p class="text-sm">Status: ${rider.status}</p>
                    ${rider.delivery_status ? `<p class="text-sm">Delivery: ${rider.delivery_status}</p>` : ''}
                    ${rider.order_number ? `<p class="text-sm">Order: #${rider.order_number}</p>` : ''}
                    <p class="text-xs text-gray-500">Updated: ${rider.last_update}</p>
                </div>
            `;
            marker.bindPopup(popupContent);
            riderMarkers[rider.id] = marker;
        }
    });
    
    // Remove markers for riders that are no longer online
    currentIds.forEach(id => {
        if (!newIds.includes(id)) {
            map.removeLayer(riderMarkers[id]);
            delete riderMarkers[id];
        }
    });
}

function refreshMap() {
    loadRiderLocations();
}

function centerMap() {
    if (Object.keys(riderMarkers).length > 0) {
        const bounds = L.latLngBounds([]);
        Object.values(riderMarkers).forEach(marker => {
            bounds.extend(marker.getLatLng());
        });
        map.fitBounds(bounds, { padding: [50, 50] });
    } else {
        map.setView([27.7172, 85.3240], 13);
    }
}

// Cleanup
window.addEventListener('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});
</script>
@endpush
@endsection