@extends('layouts.app')

@section('title', 'Upload Proof of Delivery')
@section('page-title', '📷 Upload Proof of Delivery')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">📷 Upload Proof of Delivery</h1>
                <p class="text-sm text-gray-500 mt-1">Upload photo or document as proof of delivery</p>
            </div>
            <a href="{{ route('domestic.manifests.pods') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to PODs
            </a>
        </div>

        <div class="p-6">
            <form action="{{ route('domestic.manifests.pods.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="shipment_id" value="{{ $shipment->id ?? '' }}">
                <input type="hidden" name="manifest_shipment_id" value="{{ $manifestShipment->id ?? '' }}">

                <!-- Shipment Info -->
                <div class="bg-blue-50 rounded-lg p-4 mb-6">
                    <h4 class="font-semibold text-gray-700">📦 Shipment Details</h4>
                    <div class="grid grid-cols-2 gap-4 mt-2">
                        <div>
                            <span class="text-sm text-gray-500">Tracking Number</span>
                            <p class="font-medium">{{ $shipment->tracking_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Recipient</span>
                            <p class="font-medium">{{ $shipment->receiver_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Delivery Address</span>
                            <p class="font-medium">{{ $shipment->receiver_address ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Status</span>
                            <p class="font-medium">
                                <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">
                                    {{ $shipment->status ?? 'Pending' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Upload Options -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Upload Method</label>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Photo Upload -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition">
                            <i class="fas fa-camera text-3xl text-gray-400 mb-2 block"></i>
                            <p class="text-gray-600 font-medium">Take Photo</p>
                            <p class="text-sm text-gray-400">Use camera to capture</p>
                            <input type="file" name="pod_photo" accept="image/*" capture="environment" 
                                   class="mt-3 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <!-- File Upload -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition">
                            <i class="fas fa-upload text-3xl text-gray-400 mb-2 block"></i>
                            <p class="text-gray-600 font-medium">Upload File</p>
                            <p class="text-sm text-gray-400">PDF, JPG, PNG (Max 5MB)</p>
                            <input type="file" name="pod_file" accept=".pdf,.jpg,.jpeg,.png" 
                                   class="mt-3 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>
                </div>

                <!-- Signature -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Signature</label>
                    <div class="border-2 border-gray-300 rounded-lg bg-white p-2">
                        <canvas id="signature-canvas" width="600" height="200" 
                                class="w-full rounded-lg" style="touch-action: none; min-height: 150px;"></canvas>
                    </div>
                    <input type="hidden" name="signature_data" id="signature-data">
                    <button type="button" onclick="clearSignature()" 
                            class="mt-2 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition text-sm">
                        <i class="fas fa-eraser mr-2"></i> Clear Signature
                    </button>
                </div>

                <!-- Additional Info -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Name *</label>
                        <input type="text" name="recipient_name" value="{{ $shipment->receiver_name ?? '' }}" 
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Date/Time</label>
                        <input type="datetime-local" name="delivered_at" value="{{ now()->format('Y-m-d\TH:i') }}" 
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Notes</label>
                    <textarea name="delivery_notes" rows="3" 
                              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                              placeholder="Any additional delivery notes..."></textarea>
                </div>

                <div class="flex justify-end gap-3 border-t pt-6">
                    <a href="{{ route('domestic.manifests.pods') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-check mr-2"></i> Submit POD
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ============================================
    // SIGNATURE FUNCTIONALITY
    // ============================================
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;
    let signatureCanvas = null;
    let signatureContext = null;

    function initSignature() {
        signatureCanvas = document.getElementById('signature-canvas');
        signatureContext = signatureCanvas.getContext('2d');
        
        // Set up drawing
        signatureContext.strokeStyle = '#1a1a1a';
        signatureContext.lineWidth = 3;
        signatureContext.lineCap = 'round';
        signatureContext.lineJoin = 'round';
        
        // Mouse events
        signatureCanvas.addEventListener('mousedown', startDraw);
        signatureCanvas.addEventListener('mousemove', draw);
        signatureCanvas.addEventListener('mouseup', endDraw);
        signatureCanvas.addEventListener('mouseleave', endDraw);
        
        // Touch events
        signatureCanvas.addEventListener('touchstart', handleTouchStart, { passive: false });
        signatureCanvas.addEventListener('touchmove', handleTouchMove, { passive: false });
        signatureCanvas.addEventListener('touchend', handleTouchEnd, { passive: false });
    }

    function getPosition(e) {
        const rect = signatureCanvas.getBoundingClientRect();
        const scaleX = signatureCanvas.width / rect.width;
        const scaleY = signatureCanvas.height / rect.height;
        
        let clientX, clientY;
        
        if (e.touches) {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        } else {
            clientX = e.clientX;
            clientY = e.clientY;
        }
        
        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY
        };
    }

    function startDraw(e) {
        isDrawing = true;
        const pos = getPosition(e);
        lastX = pos.x;
        lastY = pos.y;
        
        signatureContext.beginPath();
        signatureContext.moveTo(lastX, lastY);
    }

    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();
        
        const pos = getPosition(e);
        signatureContext.lineTo(pos.x, pos.y);
        signatureContext.stroke();
        lastX = pos.x;
        lastY = pos.y;
    }

    function endDraw(e) {
        isDrawing = false;
        // Save signature data
        document.getElementById('signature-data').value = signatureCanvas.toDataURL('image/png');
    }

    function handleTouchStart(e) {
        e.preventDefault();
        startDraw(e);
    }

    function handleTouchMove(e) {
        e.preventDefault();
        draw(e);
    }

    function handleTouchEnd(e) {
        e.preventDefault();
        endDraw(e);
    }

    function clearSignature() {
        if (signatureContext) {
            signatureContext.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
            document.getElementById('signature-data').value = '';
        }
    }

    // Initialize signature
    document.addEventListener('DOMContentLoaded', function() {
        initSignature();
    });
</script>
@endpush

@push('styles')
<style>
    #signature-canvas {
        touch-action: none;
        cursor: pointer;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        min-height: 200px;
    }
    
    #signature-canvas:active {
        cursor: crosshair;
    }
    
    @media (max-width: 640px) {
        #signature-canvas {
            min-height: 150px;
        }
    }
</style>
@endpush
@endsection