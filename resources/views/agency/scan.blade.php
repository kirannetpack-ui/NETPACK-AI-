@extends('layouts.agency')

@section('content')
<div class="max-w-4xl mx-auto px-4">
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-teal-600 to-emerald-600">
            <h1 class="text-white text-xl font-bold">Scan Shipment QR Code</h1>
            <p class="text-teal-100 text-sm">Scan the QR code on HAWB to mark arrival or departure</p>
        </div>
        
        <div class="p-6">
            <div id="qr-reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
            
            <div id="scan-result" class="mt-6 hidden">
                <div class="rounded-lg p-4" id="result-box">
                    <!-- Result will appear here -->
                </div>
            </div>
            
            <div class="mt-6 text-center">
                <p class="text-gray-500 text-sm">
                    <i class="fas fa-info-circle mr-1"></i>
                    Position the QR code in front of the camera
                </p>
            </div>
        </div>
    </div>
</div>

<script>
const html5QrCode = new Html5Qrcode("qr-reader");

function onScanSuccess(decodedText, decodedResult) {
    html5QrCode.stop();
    
    // Parse QR data (expected JSON format)
    let qrData;
    try {
        qrData = JSON.parse(decodedText);
    } catch(e) {
        // If not JSON, treat as HAWB number
        qrData = { hawb: decodedText };
    }
    
    const hawbNumber = qrData.hawb || decodedText;
    
    // Show action selection
    document.getElementById('result-box').innerHTML = `
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-center mb-4">
                <i class="fas fa-check-circle text-green-500 text-3xl"></i>
                <h3 class="text-lg font-bold mt-2">QR Code Scanned!</h3>
                <p class="text-gray-600">HAWB: ${hawbNumber}</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <button onclick="processScan('${hawbNumber}', 'arrival')" class="bg-green-600 text-white py-2 rounded-lg hover:bg-green-700">
                    <i class="fas fa-inbox mr-2"></i> Mark Arrival
                </button>
                <button onclick="processScan('${hawbNumber}', 'departure')" class="bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-truck mr-2"></i> Mark Departure
                </button>
            </div>
            <div class="mt-3">
                <textarea id="status_note" placeholder="Add status note (optional)" class="w-full px-3 py-2 border rounded-lg text-sm"></textarea>
            </div>
            <button onclick="resetScanner()" class="mt-3 w-full bg-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-400">
                Scan Another
            </button>
        </div>
    `;
    document.getElementById('scan-result').classList.remove('hidden');
}

function processScan(hawbNumber, action) {
    const note = document.getElementById('status_note')?.value || '';
    
    fetch('{{ route("agency.process-scan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            hawb_number: hawbNumber,
            action: action,
            status_note: note
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('result-box').innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                    <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
                    <h3 class="text-lg font-bold text-green-700">${data.message}</h3>
                    <div class="mt-3 text-left text-sm">
                        <p><strong>HAWB:</strong> ${data.shipment.hawb}</p>
                        <p><strong>Tracking:</strong> ${data.shipment.tracking}</p>
                        <p><strong>Status:</strong> ${data.shipment.status}</p>
                        <p><strong>Location:</strong> ${data.shipment.location}</p>
                        <p><strong>Time:</strong> ${data.shipment.time}</p>
                    </div>
                    <button onclick="resetScanner()" class="mt-4 bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700">
                        Scan Another
                    </button>
                </div>
            `;
        } else {
            document.getElementById('result-box').innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                    <i class="fas fa-times-circle text-red-500 text-3xl mb-2"></i>
                    <h3 class="text-lg font-bold text-red-700">Error</h3>
                    <p>${data.message}</p>
                    <button onclick="resetScanner()" class="mt-4 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                        Try Again
                    </button>
                </div>
            `;
        }
    });
}

function resetScanner() {
    document.getElementById('scan-result').classList.add('hidden');
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        onScanSuccess
    );
}

// Start scanner
html5QrCode.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: { width: 250, height: 250 } },
    onScanSuccess
).catch(err => {
    console.log("Unable to start scanning", err);
    document.getElementById('result-box').innerHTML = `
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl mb-2"></i>
            <p>Unable to access camera. Please ensure camera permissions are granted.</p>
        </div>
    `;
    document.getElementById('scan-result').classList.remove('hidden');
});
</script>
@endsection