@extends('layouts.app')

@section('title', 'Update Tracking')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-sync-alt text-teal-600"></i>
            Update Tracking Status
        </h1>
        <p class="text-gray-500 mb-6">Search for a shipment by tracking number and update its status</p>

        <div class="mb-6">
            <div class="flex gap-2">
                <input type="text" id="searchTracking" placeholder="Enter tracking number..." 
                       class="flex-1 border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-teal-500">
                <button onclick="searchShipment()" class="bg-teal-600 text-white px-6 py-3 rounded-lg hover:bg-teal-700 transition">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <div id="shipmentResult" class="hidden">
            <div class="border rounded-lg p-4 bg-gray-50">
                <div class="flex justify-between items-center mb-3">
                    <div>
                        <p class="text-sm text-gray-500">Tracking Number</p>
                        <p class="font-mono font-bold" id="resultTracking"></p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-medium" id="resultStatus"></span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-gray-500">Sender</p>
                        <p class="font-medium" id="resultSender"></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Receiver</p>
                        <p class="font-medium" id="resultReceiver"></p>
                    </div>
                </div>
                <div class="mt-3">
                    <button onclick="openTrackingModalFromSearch()" 
                            class="w-full bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-sync-alt mr-2"></i> Update Tracking Status
                    </button>
                </div>
            </div>
        </div>

        <div id="shipmentNotFound" class="hidden text-center py-8">
            <i class="fas fa-search text-4xl text-gray-300 block mb-2"></i>
            <p class="text-gray-500">No shipment found with that tracking number.</p>
        </div>
    </div>
</div>

<script>
let foundShipment = null;

function searchShipment() {
    const tracking = document.getElementById('searchTracking').value.trim();
    if (!tracking) {
        alert('Please enter a tracking number');
        return;
    }
    
    fetch('/api/shipments/search?tracking=' + encodeURIComponent(tracking))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.shipment) {
                foundShipment = data.shipment;
                document.getElementById('shipmentResult').classList.remove('hidden');
                document.getElementById('shipmentNotFound').classList.add('hidden');
                
                document.getElementById('resultTracking').textContent = foundShipment.tracking_number;
                document.getElementById('resultSender').textContent = foundShipment.sender_name;
                document.getElementById('resultReceiver').textContent = foundShipment.receiver_name;
                
                const statusBadge = document.getElementById('resultStatus');
                statusBadge.textContent = foundShipment.status_label || foundShipment.status;
                statusBadge.className = 'px-3 py-1 rounded-full text-xs font-medium ' + 
                    (foundShipment.status === 'delivered' ? 'bg-green-100 text-green-800' :
                     foundShipment.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                     'bg-blue-100 text-blue-800');
            } else {
                document.getElementById('shipmentResult').classList.add('hidden');
                document.getElementById('shipmentNotFound').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error searching for shipment');
        });
}

function openTrackingModalFromSearch() {
    if (foundShipment) {
        openTrackingModal(foundShipment.id, foundShipment.tracking_number);
    }
}

// Enter key support
document.getElementById('searchTracking').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchShipment();
    }
});
</script>
@endsection