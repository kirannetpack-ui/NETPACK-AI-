{{-- resources/views/components/location-map.blade.php --}}
<div x-data="locationMap()" x-init="initMap()" class="space-y-4">
    <!-- Map Container -->
    <div id="{{ $mapId ?? 'location-map' }}" style="height: 400px; width: 100%; border-radius: 12px;" class="shadow-md"></div>
    
    <!-- Hidden inputs for form submission -->
    <input type="hidden" name="pickup_latitude" x-model="pickupLat">
    <input type="hidden" name="pickup_longitude" x-model="pickupLng">
    <input type="hidden" name="delivery_latitude" x-model="deliveryLat">
    <input type="hidden" name="delivery_longitude" x-model="deliveryLng">
    
    <!-- Route Info -->
    <div class="bg-gray-50 rounded-lg p-3 text-sm" x-show="distance">
        <div class="flex justify-between items-center">
            <span><i class="fas fa-road text-teal-600"></i> Distance: <span x-text="distance"></span></span>
            <span><i class="fas fa-clock text-teal-600"></i> Est. Time: <span x-text="duration"></span></span>
        </div>
    </div>
</div>

<script>
function locationMap() {
    return {
        map: null,
        pickupMarker: null,
        deliveryMarker: null,
        directionsRenderer: null,
        geocoder: null,
        pickupLat: {{ $pickupLat ?? 27.7172 }},
        pickupLng: {{ $pickupLng ?? 85.3240 }},
        deliveryLat: {{ $deliveryLat ?? 27.7172 }},
        deliveryLng: {{ $deliveryLng ?? 85.3240 }},
        pickupAddress: '',
        deliveryAddress: '',
        distance: '',
        duration: '',
        
        initMap() {
            if (typeof google === 'undefined') {
                const script = document.createElement('script');
                script.src = `https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initMapCallback`;
                script.async = true;
                script.defer = true;
                window.initMapCallback = () => this.initializeMap();
                document.head.appendChild(script);
            } else {
                this.initializeMap();
            }
        },
        
        initializeMap() {
            this.map = new google.maps.Map(document.getElementById('{{ $mapId ?? "location-map" }}'), {
                center: { lat: parseFloat(this.pickupLat), lng: parseFloat(this.pickupLng) },
                zoom: 13,
                styles: [{ featureType: 'poi', elementType: 'labels', stylers: [{ visibility: 'off' }] }]
            });
            
            this.geocoder = new google.maps.Geocoder();
            
            // Setup address autocomplete for pickup and delivery fields
            this.setupAddressAutocomplete();
        },
        
        setupAddressAutocomplete() {
            // Pickup address autocomplete
            const pickupInput = document.querySelector('input[name="pickup_address"]');
            if (pickupInput) {
                const pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput);
                pickupAutocomplete.addListener('place_changed', () => {
                    const place = pickupAutocomplete.getPlace();
                    if (place.geometry) {
                        this.setPickupLocation(
                            place.geometry.location.lat(),
                            place.geometry.location.lng(),
                            place.formatted_address
                        );
                    }
                });
            }
            
            // Delivery address autocomplete
            const deliveryInput = document.querySelector('input[name="delivery_address"]');
            if (deliveryInput) {
                const deliveryAutocomplete = new google.maps.places.Autocomplete(deliveryInput);
                deliveryAutocomplete.addListener('place_changed', () => {
                    const place = deliveryAutocomplete.getPlace();
                    if (place.geometry) {
                        this.setDeliveryLocation(
                            place.geometry.location.lat(),
                            place.geometry.location.lng(),
                            place.formatted_address
                        );
                    }
                });
            }
        },
        
        setPickupLocation(lat, lng, address = null) {
            if (this.pickupMarker) this.pickupMarker.setMap(null);
            
            this.pickupMarker = new google.maps.Marker({
                position: { lat, lng },
                map: this.map,
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
                    scaledSize: new google.maps.Size(40, 40)
                },
                title: 'Pickup Location'
            });
            
            this.pickupLat = lat;
            this.pickupLng = lng;
            
            // Update address field
            if (address) {
                const pickupInput = document.querySelector('input[name="pickup_address"]');
                if (pickupInput) pickupInput.value = address;
            }
            
            this.map.setCenter({ lat, lng });
            this.calculateRoute();
        },
        
        setDeliveryLocation(lat, lng, address = null) {
            if (this.deliveryMarker) this.deliveryMarker.setMap(null);
            
            this.deliveryMarker = new google.maps.Marker({
                position: { lat, lng },
                map: this.map,
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
                    scaledSize: new google.maps.Size(40, 40)
                },
                title: 'Delivery Location'
            });
            
            this.deliveryLat = lat;
            this.deliveryLng = lng;
            
            // Update address field
            if (address) {
                const deliveryInput = document.querySelector('input[name="delivery_address"]');
                if (deliveryInput) deliveryInput.value = address;
            }
            
            this.calculateRoute();
        },
        
        calculateRoute() {
            if (this.pickupMarker && this.deliveryMarker) {
                if (this.directionsRenderer) this.directionsRenderer.setMap(null);
                
                this.directionsRenderer = new google.maps.DirectionsRenderer({
                    map: this.map,
                    suppressMarkers: true,
                    polylineOptions: { strokeColor: '#0D9488', strokeWeight: 4 }
                });
                
                const directionsService = new google.maps.DirectionsService();
                directionsService.route({
                    origin: this.pickupMarker.getPosition(),
                    destination: this.deliveryMarker.getPosition(),
                    travelMode: google.maps.TravelMode.DRIVING
                }, (result, status) => {
                    if (status === 'OK') {
                        this.directionsRenderer.setDirections(result);
                        this.distance = result.routes[0].legs[0].distance.text;
                        this.duration = result.routes[0].legs[0].duration.text;
                        
                        // Update hidden fields
                        const distanceInput = document.querySelector('input[name="route_distance"]');
                        const durationInput = document.querySelector('input[name="route_duration"]');
                        if (distanceInput) distanceInput.value = this.distance;
                        if (durationInput) durationInput.value = this.duration;
                    }
                });
            }
        },
        
        useMyLocation(type) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    this.geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                        if (status === 'OK' && results[0]) {
                            if (type === 'pickup') {
                                this.setPickupLocation(lat, lng, results[0].formatted_address);
                            } else {
                                this.setDeliveryLocation(lat, lng, results[0].formatted_address);
                            }
                        }
                    });
                }, (error) => {
                    alert('Unable to get your location. Please check permissions.');
                });
            } else {
                alert('Geolocation is not supported by your browser.');
            }
        }
    };
}
</script>