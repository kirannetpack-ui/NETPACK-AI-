@extends('layouts.partner')

@section('title', 'QR Scan')
@section('page-title', 'QR Code Scanner')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="text-center mb-6">
            <i class="fas fa-qrcode text-4xl text-teal-600 mb-2"></i>
            <h2 class="text-xl font-semibold text-gray-800">Scan Delivery QR Code</h2>
            <p class="text-gray-500 text-sm">Scan the QR code on the delivery package to update status</p>
        </div>

        <!-- Scanner Area -->
        <div id="scanner-container" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
            <div id="reader" style="width: 100%; max-width: 400px; margin: 0 auto;"></div>
            <p class="text-sm text-gray-500 mt-4">
                <i class="fas fa-camera mr-2"></i> Position the QR code in front of the camera
            </p>
        </div>

        <!-- Manual Entry -->
        <div class="mt-6">
            <p class="text-sm text-gray-500 text-center mb-3">Or enter tracking number manually</p>
            <div class="flex gap-2 max-w-md mx-auto">
                <input type="text" id="manualTracking" placeholder="Enter tracking number..." 
                       class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                <button onclick="manualLookup()" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- Result -->
        <div id="scanResult" class="mt-6 hidden">
            <div class="border rounded-lg p-4 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Tracking Number</p>
                        <p class="font-mono font-bold text-lg" id="resultTracking"></p>
                    </div>
                    <span id="resultStatusBadge" class="px-3 py-1 rounded-full text-xs font-medium"></span>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-3 text-sm">
                    <div>
                        <p class="text-gray-500">Sender</p>
                        <p class="font-medium" id="resultSender"></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Receiver</p>
                        <p class="font-medium" id="resultReceiver"></p>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <button onclick="updateStatusFromScan()" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition flex-1">
                        <i class="fas fa-sync-alt mr-2"></i> Update Status
                    </button>
                    <a id="trackingLink" href="#" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex-1 text-center">
                        <i class="fas fa-external-link-alt mr-2"></i> View Tracking
                    </a>
                </div>
            </div>
        </div>

        <div id="scanError" class="mt-4 hidden">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span id="errorMessage">Shipment not found</span>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>

<script>
let scannedShipment = null;
let html5QrCode;

function onScanSuccess(decodedText, decodedResult) {
    console.log(`Code scanned: ${decodedText}`);
    lookupShipment(decodedText);
    
    if (html5QrCode) {
        html5QrCode.stop();
    }
}

function onScanFailure(error) {
    // Ignore
}

document.addEventListener('DOMContentLoaded', function() {
    html5QrCode = new Html5Qrcode("reader");
    
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };
    
    html5QrCode.start(
        { facingMode: "environment" },
        config,
        onScanSuccess,
        onScanFailure
    ).catch(err => {
        console.error('Unable to start scanner:', err);
        document.getElementById('reader').innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-exclamation-triangle text-3xl block mb-2"></i>
                <p>Camera access denied. Please allow camera access or enter tracking manually.</p>
            </div>
        `;
    });
});

function lookupShipment(trackingNumber) {
    fetch('/partner/fetch/' + encodeURIComponent(trackingNumber))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                scannedShipment = data.shipment;
                showResult(scannedShipment);
            } else {
                showError('Shipment not found with tracking number: ' + trackingNumber);
            }
        })
        .catch(error => {
            showError('Error: ' + error.message);
        });
}

function manualLookup() {
    const tracking = document.getElementById('manualTracking').value.trim();
    if (!tracking) {
        alert('Please enter a tracking number');
        return;
    }
    lookupShipment(tracking);
}

function showResult(shipment) {
    document.getElementById('scanResult').classList.remove('hidden');
    document.getElementById('scanError').classList.add('hidden');
    
    document.getElementById('resultTracking').textContent = shipment.tracking_number;
    document.getElementById('resultSender').textContent = shipment.sender_name || 'N/A';
    document.getElementById('resultReceiver').textContent = shipment.receiver_name || 'N/A';
    
    const badge = document.getElementById('resultStatusBadge');
    const statusLabel = shipment.status_label || shipment.status;
    const statusColor = shipment.status === 'delivered' ? 'green' : 
                       (shipment.status === 'pending' ? 'yellow' : 'blue');
    badge.textContent = statusLabel;
    badge.className = 'px-3 py-1 rounded-full text-xs font-medium bg-' + statusColor + '-100 text-' + statusColor + '-800';
    
    document.getElementById('trackingLink').href = '/track/' + shipment.tracking_number;
}

function showError(message) {
    document.getElementById('scanResult').classList.add('hidden');
    document.getElementById('scanError').classList.remove('hidden');
    document.getElementById('errorMessage').textContent = message;
}

function updateStatusFromScan() {
    if (!scannedShipment) return;
    
    const status = prompt('Enter new status:\n(picked_up, in_transit, out_for_delivery, delivered)', 'out_for_delivery');
    if (!status) return;
    
    const location = prompt('Enter location:', 'Scan Point');
    const notes = prompt('Enter notes:', 'Status updated via QR scan');
    
    fetch('/partner/deliveries/' + scannedShipment.id + '/update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            status: status,
            location: location || 'Scan Point',
            status_note: notes || 'Status updated via QR scan'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Status updated successfully!');
            lookupShipment(scannedShipment.tracking_number);
        } else {
            alert('❌ Failed to update status: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
    });
}
</script>
@endsection