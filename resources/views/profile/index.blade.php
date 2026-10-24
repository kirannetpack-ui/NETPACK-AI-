@extends('layouts.app')

@section('title', 'Profile')
@section('page-title', 'My Profile')

@section('sidebar')
    <a href="{{ route('client.dashboard') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-home w-5"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('shipments.create') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-plus-circle w-5"></i>
        <span>New Shipment</span>
    </a>
    <a href="{{ route('tracking.page') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-search w-5"></i>
        <span>Track Shipment</span>
    </a>
    <a href="{{ route('profile') }}" class="sidebar-link active flex items-center space-x-3 px-4 py-3 text-sm text-white">
        <i class="fas fa-user w-5"></i>
        <span>Profile</span>
    </a>
    <a href="{{ route('client.settings') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-cog w-5"></i>
        <span>Settings</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Profile Header -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center space-x-4">
            <div class="w-20 h-20 rounded-full bg-teal-100 flex items-center justify-center text-3xl font-bold text-teal-600">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-2xl font-bold">{{ Auth::user()->name }}</h2>
                <p class="text-gray-500">{{ Auth::user()->email }}</p>
                <p class="text-sm text-gray-500">Member since {{ Auth::user()->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Address Management -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Permanent Address -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-lg mb-4">
                <i class="fas fa-home text-teal-500 mr-2"></i> Permanent Address
            </h3>
            <form action="{{ route('profile.update-address') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input type="text" name="permanent_address" value="{{ Auth::user()->permanent_address }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500" 
                               placeholder="Enter your permanent address" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                            <input type="text" name="address_lat" value="{{ Auth::user()->address_lat }}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500" 
                                   placeholder="27.7172" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                            <input type="text" name="address_lng" value="{{ Auth::user()->address_lng }}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500" 
                                   placeholder="85.3240" />
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-teal-500 hover:bg-teal-600 text-white font-semibold py-2 rounded-lg transition">
                        <i class="fas fa-save mr-2"></i> Update Address
                    </button>
                </div>
            </form>
        </div>

        <!-- Temporary Address -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-lg mb-4">
                <i class="fas fa-location-dot text-blue-500 mr-2"></i> Temporary Address
            </h3>
            <form action="{{ route('profile.update-temporary') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input type="text" name="temporary_address" value="{{ Auth::user()->temporary_address }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500" 
                               placeholder="Enter your temporary address" />
                    </div>
                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 rounded-lg transition">
                        <i class="fas fa-save mr-2"></i> Update Temporary Address
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Map Preview -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-lg mb-4">
            <i class="fas fa-map-marked-alt text-red-500 mr-2"></i> Address Map Preview
        </h3>
        <div id="profileMap" style="height: 300px; width: 100%; border-radius: 12px;" class="border border-gray-200"></div>
    </div>
</div>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initProfileMap" async defer></script>

<script>
    let profileMap;
    let profileMarker;

    function initProfileMap() {
        const lat = {{ Auth::user()->address_lat ?? 27.7172 }};
        const lng = {{ Auth::user()->address_lng ?? 85.3240 }};
        const position = { lat: lat, lng: lng };

        profileMap = new google.maps.Map(document.getElementById('profileMap'), {
            center: position,
            zoom: 14,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        profileMarker = new google.maps.Marker({
            map: profileMap,
            position: position,
            draggable: true,
            label: {
                text: '📍',
                fontSize: '24px'
            }
        });

        profileMarker.addListener('dragend', function() {
            const pos = this.getPosition();
            const lat = pos.lat();
            const lng = pos.lng();
            
            // Update hidden fields or show coordinates
            document.querySelector('input[name="address_lat"]').value = lat;
            document.querySelector('input[name="address_lng"]').value = lng;
        });

        // Click to set marker
        profileMap.addListener('click', function(e) {
            const latLng = e.latLng;
            profileMarker.setPosition(latLng);
            document.querySelector('input[name="address_lat"]').value = latLng.lat();
            document.querySelector('input[name="address_lng"]').value = latLng.lng();
        });
    }
</script>
@endsection