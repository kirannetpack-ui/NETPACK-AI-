{{-- resources/views/tracking/map.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Shipment - {{ $shipment->tracking_number }} | NETPACK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Leaflet Routing Machine -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
@vite(['resources/css/app.css'])
</head>
<body class="np-system np-legacy-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow-lg sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-box-open text-teal-600 text-2xl"></i>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Live Tracking</h1>
                        <p class="text-xs text-gray-500">HAWB: {{ $shipment->hawb_number }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-600">Tracking #</p>
                    <p class="text-xs font-mono text-teal-600">{{ $shipment->tracking_number }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6">
        <!-- Status Bar -->
        <div class="bg-white rounded-xl shadow-md p-4 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="text-center">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <p class="text-xs text-gray-500">Order Placed</p>
                    <p class="text-sm font-semibold">{{ $shipment->created_at->format('M d') }}</p>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 {{ $shipment->status != 'pending' ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas {{ $shipment->status != 'pending' ? 'fa-check-circle text-green-600' : 'fa-clock text-gray-400' }}"></i>
                    </div>
                    <p class="text-xs text-gray-500">Confirmed</p>
                    <p class="text-sm font-semibold">{{ $shipment->updated_at->format('M d') }}</p>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 {{ $shipment->status == 'picked_up' || $shipment->status == 'in_transit' ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas {{ $shipment->status == 'picked_up' || $shipment->status == 'in_transit' ? 'fa-truck text-green-600' : 'fa-clock text-gray-400' }}"></i>
                    </div>
                    <p class="text-xs text-gray-500">Picked Up</p>
                    <p class="text-sm font-semibold">{{ $shipment->picked_up_at ? $shipment->picked_up_at->format('M d') : 'Pending' }}</p>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 {{ $shipment->status == 'out_for_delivery' ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas {{ $shipment->status == 'out_for_delivery' ? 'fa-hand-peace text-green-600' : 'fa-clock text-gray-400' }}"></i>
                    </div>
                    <p class="text-xs text-gray-500">Out for Delivery</p>
                    <p class="text-sm font-semibold">Today</p>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 {{ $shipment->status == 'delivered' ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas {{ $shipment->status == 'delivered' ? 'fa-check-circle text-green-600' : 'fa-clock text-gray-400' }}"></i>
                    </div>
                    <p class="text-xs text-gray-500">Delivered</p>
                    <p class="text-sm font-semibold">{{ $shipment->delivered_at ? $shipment->delivered_at->format('M d') : 'Pending' }}</p>
                </div>
            </div>
        </div>

        <!-- Map and Info -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Map -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-md overflow-hidden">
                <div id="map" style="height: 500px; width: 100%;"></div>
                <div class="p-4 border-t">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-teal-600 rounded-full mr-2"></div>
                                <span class="text-sm">Current Location</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-red-600 rounded-full mr-2"></div>
                                <span class="text-sm">Destination</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-blue-600 rounded-full mr-2"></div>
                                <span class="text-sm">Route</span>
                            </div>
                        </div>
                        <button onclick="centerMap()" class="text-teal-600 hover:text-teal-700 text-sm">
                            <i class="fas fa-crosshairs mr-1"></i> Recenter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Shipment Info Panel -->
            <div class="space-y-6">
                <!-- Delivery Status Card -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-info-circle text-teal-600 mr-2"></i>
                        Delivery Status
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 text-sm">Status</span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                @if($shipment->status == 'delivered') bg-green-100 text-green-800
                                @elseif($shipment->status == 'out_for_delivery') bg-blue-100 text-blue-800
                                @elseif($shipment->status == 'in_transit') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">Current Location</span>
                            <span class="text-sm font-medium" id="current-location-name">
                                {{ $currentLocation->location_name ?? 'Processing at NETPACK Hub' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">Last Update</span>
                            <span class="text-sm" id="last-update">
                                {{ $currentLocation ? $currentLocation->recorded_at->diffForHumans() : 'Just now' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">Estimated Arrival</span>
                            <span class="text-sm font-semibold text-teal-600" id="eta">
                                {{ $estimatedArrival ? $estimatedArrival->format('M d, h:i A') : 'Calculating...' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Delivery Address Card -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-map-marker-alt text-teal-600 mr-2"></i>
                        Delivery Address
                    </h3>
                    <div class="space-y-2">
                        <p class="text-sm font-medium">{{ $shipment->receiver_name }}</p>
                        <p class="text-sm text-gray-600">{{ $shipment->receiver_address }}</p>
                        <p class="text-sm text-gray-600">{{ $shipment->receiver_city }}, {{ $shipment->receiver_country }}</p>
                        <p class="text-sm text-gray-600">Phone: {{ $shipment->receiver_phone }}</p>
                    </div>
                </div>

                <!-- Package Details Card -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-box text-teal-600 mr-2"></i>
                        Package Details
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Weight</span>
                            <span>{{ $shipment->actual_weight }} kg</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Service Type</span>
                            <span class="capitalize">{{ $shipment->service_type }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Shipment Type</span>
                            <span class="capitalize">{{ $shipment->shipment_type }}</span>
                        </div>
                        @php
                            $boxes = json_decode($shipment->boxes, true);
                            $itemCount = 0;
                            if ($boxes) {
                                foreach ($boxes as $box) {
                                    $itemCount += count($box);
                                }
                            }
                        @endphp
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Items</span>
                            <span>{{ $itemCount }} products</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <a href="{{ route('hawb.international', $shipment->id) }}" target="_blank" class="flex-1 bg-teal-600 text-white text-center px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-file-pdf mr-2"></i> View / Print HAWB
                    </a>
                    <a href="{{ route('shipments.show', $shipment) }}" class="flex-1 bg-gray-600 text-white text-center px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-info-circle mr-2"></i> Details
                    </a>
                </div>
            </div>
        </div>

        <!-- Tracking Timeline -->
        <div class="bg-white rounded-xl shadow-md p-5 mt-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-history text-teal-600 mr-2"></i>
                Tracking History
            </h3>
            <div class="space-y-4">
                @forelse($shipment->tracking_history ?? [] as $event)
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-3 h-3 bg-teal-600 rounded-full mt-1"></div>
                            @if(!$loop->last)
                                <div class="w-0.5 h-full bg-gray-200 ml-1"></div>
                            @endif
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $event['status'])) }}</p>
                            <p class="text-sm text-gray-600">{{ $event['location'] ?? 'NETPACK Hub' }}</p>
                            @if(isset($event['description']))
                                <p class="text-xs text-gray-500 mt-1">{{ $event['description'] }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($event['timestamp'])->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No tracking history available yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        // Map initialization
        let map;
        let marker;
        let routeControl;
        
        // Coordinates
        const startLat = {{ $currentLocation->latitude ?? 27.7172 }};
        const startLng = {{ $currentLocation->longitude ?? 85.3240 }};
        const destLat = 27.7172; // Replace with actual destination coordinates
        const destLng = 85.3240;
        
        // Initialize map
        function initMap() {
            map = L.map('map').setView([startLat, startLng], 13);
            
            // Add tile layer
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);
            
            // Add destination marker
            L.marker([destLat, destLng], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background-color: #EF4444; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>',
                    iconSize: [16, 16],
                    popupAnchor: [0, -8]
                })
            }).addTo(map).bindPopup('<strong>Destination</strong><br>Delivery Address');
            
            // Add current location marker
            marker = L.marker([startLat, startLng], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background-color: #0D9488; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.3);"></div>',
                    iconSize: [16, 16],
                    popupAnchor: [0, -8]
                })
            }).addTo(map).bindPopup('<strong>Current Location</strong><br>{{ $currentLocation->location_name ?? "NETPACK Hub" }}');
            
            // Add route if both points exist
            if (startLat && startLng && destLat && destLng) {
                routeControl = L.Routing.control({
                    waypoints: [
                        L.latLng(startLat, startLng),
                        L.latLng(destLat, destLng)
                    ],
                    routeWhileDragging: false,
                    showAlternatives: false,
                    lineOptions: {
                        styles: [{ color: '#0D9488', weight: 4, opacity: 0.7 }]
                    },
                    createMarker: function() { return null; }
                }).addTo(map);
            }
        }
        
        // Center map on current location
        function centerMap() {
            map.setView([startLat, startLng], 15);
        }
        
        // Update location dynamically (via AJAX)
        function updateLiveLocation() {
            const shipmentId = {{ $shipment->id }};
            
            fetch(`/tracking/live/${shipmentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.current_location) {
                        const newLat = data.current_location.lat;
                        const newLng = data.current_location.lng;
                        
                        // Update marker position
                        marker.setLatLng([newLat, newLng]);
                        
                        // Update location name
                        document.getElementById('current-location-name').innerText = data.current_location.location_name || 'In Transit';
                        document.getElementById('last-update').innerText = data.current_location.last_update;
                        
                        // Update ETA
                        if (data.estimated_arrival) {
                            document.getElementById('eta').innerText = data.estimated_arrival;
                        }
                        
                        // Center map on new location
                        map.setView([newLat, newLng], map.getZoom());
                    }
                })
                .catch(error => console.log('Error fetching live location:', error));
        }
        
        // Update every 30 seconds
        let interval = setInterval(updateLiveLocation, 30000);
        
        // Initialize map when page loads
        document.addEventListener('DOMContentLoaded', initMap);

 // Listen for real-time updates
    window.Echo = window.Echo || {};
    window.Echo.channel(`shipment.{{ $shipment->tracking_number }}`)
        .listen('.location.updated', (e) => {
            console.log('Real-time update:', e);
            if (e.latitude && e.longitude) {
                updateMarker(e.latitude, e.longitude, e.location);
            }
            showToast(e.message || 'Location updated');
        })
        .listen('.status.changed', (e) => {
            updateStatusDisplay(e.new_status, e.message);
            showToast(e.message);
        });
    
    function updateMarker(lat, lng, locationName) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        }
        map.setView([lat, lng], 15);
        document.getElementById('current-location-name').innerText = locationName;
        document.getElementById('last-update').innerText = 'Just now';
    }
    
    function updateStatusDisplay(status, message) {
        const statusEl = document.getElementById('shipment-status');
        if (statusEl) {
            statusEl.innerText = status.replace(/_/g, ' ');
        }
        
        // Update progress
        const progressMap = {
            'pending': 0, 'confirmed': 10, 'processing': 20,
            'picked_up': 30, 'in_transit': 50, 'arrived_at_agency': 70,
            'out_for_delivery': 85, 'delivered': 100
        };
        const progress = progressMap[status] || 0;
        document.getElementById('progress-bar').style.width = `${progress}%`;
    }
    
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-teal-600 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        toast.innerHTML = `<i class="fas fa-bell mr-2"></i>${message}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }
    </script>

    <style>
        .leaflet-routing-container {
            display: none;
        }
        .custom-div-icon {
            background: transparent;
            border: none;
        }
    </style>
</body>
</html>