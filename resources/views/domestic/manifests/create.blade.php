@extends('layouts.app')

@section('title', 'Create New Manifest')
@section('page-title', 'Create New Manifest')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">📦 Create New Manifest</h1>
                <p class="text-sm text-gray-500 mt-1">Create a manifest with bags and shipments</p>
            </div>
            <a href="{{ route('domestic.manifests.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Manifests
            </a>
        </div>

        <form action="{{ route('domestic.manifests.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Load Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Load Type *</label>
                    <select name="load_type" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                        <option value="consolidated">Consolidated</option>
                        <option value="direct">Direct</option>
                        <option value="express">Express</option>
                    </select>
                </div>

                <!-- Assign to Partner -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assign to Partner</label>
                    <select name="partner_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Select Partner</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}">{{ $partner->name }} ({{ $partner->email }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Origin City -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Origin City *</label>
                    <input type="text" name="origin_city" value="Kathmandu" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                </div>

                <!-- Destination City -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Destination City *</label>
                    <input type="text" name="destination_city" value="Pokhara" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                </div>

                <!-- Delivery Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Type</label>
                    <select name="delivery_type" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="door_delivery">Door Delivery</option>
                        <option value="pickup">Pickup</option>
                        <option value="warehouse">Warehouse</option>
                    </select>
                </div>

                <!-- Payment Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                    <select name="payment_status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="cod">COD</option>
                    </select>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- AVAILABLE SHIPMENTS SECTION - KEY PART -->
            <!-- ============================================ -->
            <div class="mt-8 border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Available Shipments</h3>
                
                @if(isset($shipments) && $shipments->count() > 0)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            Showing <strong>{{ $shipments->count() }}</strong> shipment(s) that are <strong>NOT</strong> yet manifested.
                            Hold <kbd class="px-2 py-1 bg-gray-200 rounded text-xs">Ctrl</kbd> (or <kbd class="px-2 py-1 bg-gray-200 rounded text-xs">Cmd</kbd> on Mac) to select multiple shipments.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Shipments</label>
                        <select name="shipments[]" id="shipments" multiple 
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 h-64">
                            @foreach($shipments as $shipment)
                                <option value="{{ $shipment->id }}" class="py-2">
                                    🔢 {{ $shipment->tracking_number }} 
                                    | 👤 {{ $shipment->receiver_name ?? 'N/A' }}
                                    | 📍 {{ $shipment->origin_city ?? 'N/A' }} → {{ $shipment->destination_city ?? 'N/A' }}
                                    | 📦 {{ $shipment->weight ?? 'N/A' }} kg
                                    | 💰 {{ $shipment->total_amount ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-check-circle text-green-500 mr-1"></i>
                            {{ $shipments->count() }} shipment(s) available for manifest
                        </p>
                    </div>

                    <button type="button" onclick="addSelectedToBag()" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i> Add Selected to Bag
                    </button>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <i class="fas fa-check-circle text-yellow-500 text-4xl mb-3 block"></i>
                        <h4 class="text-lg font-semibold text-yellow-800">All Shipments Manifested!</h4>
                        <p class="text-yellow-700 mt-2">
                            There are no pending shipments available for manifest. 
                            All shipments have been assigned to a manifest or are already in progress.
                        </p>
                        <a href="{{ route('shipments.create') }}" class="inline-block mt-4 bg-yellow-600 text-white px-6 py-2 rounded-lg hover:bg-yellow-700 transition">
                            <i class="fas fa-plus mr-2"></i> Create New Shipment
                        </a>
                    </div>
                @endif
            </div>

            <!-- ============================================ -->
            <!-- BAGS SECTION -->
            <!-- ============================================ -->
            <div class="mt-8 border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">🎒 Bags</h3>
                <div id="bags-container">
                    <!-- Bags will be added here dynamically -->
                </div>
                <button type="button" onclick="addBag()" class="mt-4 bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-plus mr-2"></i> Add Bag
                </button>
            </div>

            <!-- Submit -->
            <div class="mt-8 border-t pt-6 flex justify-end gap-3">
                <a href="{{ route('domestic.manifests.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i> Create Manifest
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let bagCount = 0;
    let bagCounter = 0;

    function addSelectedToBag() {
        const select = document.getElementById('shipments');
        const selectedOptions = Array.from(select.selectedOptions);
        
        if (selectedOptions.length === 0) {
            alert('Please select at least one shipment to add to a bag.');
            return;
        }

        // Create a new bag
        bagCounter++;
        const bagId = 'bag-' + bagCounter;
        const bagHtml = `
            <div id="${bagId}" class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-semibold text-gray-700">Bag #${bagCounter}</h4>
                    <button type="button" onclick="removeBag('${bagId}')" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Bag Type *</label>
                        <select name="bags[${bagCounter}][bag_type]" class="w-full rounded-lg border-gray-300">
                            <option value="consolidated">Consolidated</option>
                            <option value="direct">Direct</option>
                            <option value="express">Express</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Weight (kg) *</label>
                        <input type="number" step="0.01" name="bags[${bagCounter}][weight]" 
                               class="w-full rounded-lg border-gray-300" placeholder="0.00">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Selected Shipments</label>
                    <div class="bg-white border border-gray-200 rounded-lg p-3 max-h-32 overflow-y-auto">
                        ${selectedOptions.map(opt => `
                            <div class="flex items-center gap-2 text-sm py-1">
                                <input type="hidden" name="bags[${bagCounter}][shipments][]" value="${opt.value}">
                                <i class="fas fa-box text-blue-500"></i>
                                <span>${opt.text}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;

        document.getElementById('bags-container').insertAdjacentHTML('beforeend', bagHtml);
        bagCount++;

        // Remove selected options from the select
        selectedOptions.forEach(opt => opt.remove());

        // Update the count
        updateShipmentCount();
    }

    function addBag() {
        // Create an empty bag
        bagCounter++;
        const bagId = 'bag-' + bagCounter;
        const bagHtml = `
            <div id="${bagId}" class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-semibold text-gray-700">Bag #${bagCounter}</h4>
                    <button type="button" onclick="removeBag('${bagId}')" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Bag Type *</label>
                        <select name="bags[${bagCounter}][bag_type]" class="w-full rounded-lg border-gray-300">
                            <option value="consolidated">Consolidated</option>
                            <option value="direct">Direct</option>
                            <option value="express">Express</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Weight (kg) *</label>
                        <input type="number" step="0.01" name="bags[${bagCounter}][weight]" 
                               class="w-full rounded-lg border-gray-300" placeholder="0.00">
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-sm text-gray-500 italic">No shipments assigned to this bag yet.</p>
                </div>
            </div>
        `;

        document.getElementById('bags-container').insertAdjacentHTML('beforeend', bagHtml);
        bagCount++;
    }

    function removeBag(bagId) {
        if (confirm('Remove this bag?')) {
            const bag = document.getElementById(bagId);
            // Return shipments to the available list
            const shipments = bag.querySelectorAll('input[name*="[shipments][]"]');
            const select = document.getElementById('shipments');
            shipments.forEach(input => {
                const option = document.createElement('option');
                option.value = input.value;
                option.text = input.parentElement.textContent.trim();
                select.appendChild(option);
            });
            bag.remove();
            bagCount--;
            updateShipmentCount();
        }
    }

    function updateShipmentCount() {
        const select = document.getElementById('shipments');
        const count = select.options.length;
        const countElement = document.querySelector('.text-xs.text-gray-500.mt-1');
        if (countElement) {
            countElement.innerHTML = `<i class="fas fa-check-circle text-green-500 mr-1"></i> ${count} shipment(s) available for manifest`;
        }
    }
</script>
@endpush

@push('styles')
<style>
    kbd {
        display: inline-block;
        padding: 2px 6px;
        font-size: 11px;
        line-height: 1.4;
        color: #333;
        background-color: #f7f7f7;
        border: 1px solid #ccc;
        border-radius: 3px;
        box-shadow: 0 1px 0 rgba(0,0,0,0.2);
    }
</style>
@endpush
@endsection