@extends('layouts.seller')

@section('title', 'Create Order')
@section('page-title', 'Create New Order')

@push('styles')
<!-- Leaflet CSS for Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #delivery-map { height: 300px; border-radius: 8px; border: 1px solid #ddd; }
    .delivery-card { 
        border: 1px solid #e5e7eb; 
        border-radius: 8px; 
        padding: 16px; 
        margin-bottom: 16px;
        background: #f9fafb;
        transition: all 0.3s ease;
    }
    .delivery-card:hover { border-color: #0d9488; }
    .delivery-card .delivery-number { 
        font-weight: 600; 
        color: #0d9488; 
        margin-bottom: 8px;
    }
    .remove-delivery {
        color: #ef4444;
        cursor: pointer;
        font-size: 14px;
    }
    .remove-delivery:hover { color: #dc2626; }
    .add-delivery-btn {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        color: #6b7280;
    }
    .add-delivery-btn:hover {
        border-color: #0d9488;
        color: #0d9488;
        background: #f0fdfa;
    }
    .delivery-summary {
        background: #f0fdfa;
        border: 1px solid #0d9488;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 16px;
    }
    .delivery-summary .count {
        font-weight: 700;
        color: #0d9488;
        font-size: 18px;
    }
    .payment-card {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .payment-card:hover { border-color: #0d9488; }
    .payment-card.selected { border-color: #0d9488; background: #f0fdfa; }
    .payment-card .payment-icon { font-size: 28px; display: block; margin-bottom: 8px; }
    .payment-card .payment-label { font-weight: 600; }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Create New Order</h1>
                <p class="text-sm text-gray-500 mt-1">Add customer details, payment method, and delivery addresses</p>
            </div>
            <a href="{{ route('seller.orders') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Orders
            </a>
        </div>

        <div class="p-6">
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 3-Tier Channel Switcher Banner -->
            <div class="mb-6 bg-slate-900 text-white rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-800">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Global Dispatch Desks</span>
                        <h2 class="text-base font-bold text-white mt-0.5">Select Shipment Service Tier</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('shipments.create', ['shipment_type' => 'ecommerce']) }}" class="px-3 py-1.5 rounded-xl bg-teal-500/20 text-teal-300 hover:bg-teal-500/30 text-xs font-bold transition border border-teal-500/30 flex items-center gap-1.5 self-start sm:self-auto">
                            <i class="fas fa-boxes-packing text-teal-400"></i>
                            <span>Unified Booking Console &rarr;</span>
                        </a>
                        <a href="{{ route('rates.inquiry') }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 text-xs font-bold transition border border-amber-500/30 flex items-center gap-1.5 self-start sm:self-auto">
                            <i class="fas fa-calculator text-amber-400"></i>
                            <span>Calculate Rates &rarr;</span>
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mt-4">
                    <!-- 1. Rider Delivery (Active here) -->
                    <div class="p-3 rounded-xl bg-emerald-600 text-white font-bold text-xs flex items-center gap-2.5 shadow-sm">
                        <i class="fas fa-motorcycle text-base"></i>
                        <div>
                            <p class="leading-tight">1. Rider Delivery</p>
                            <p class="text-[10px] font-normal text-emerald-100">Local City E-Commerce</p>
                        </div>
                        <span class="ml-auto text-[10px] bg-white/20 px-1.5 py-0.5 rounded uppercase">Current</span>
                    </div>

                    <!-- 2. Domestic Courier -->
                    <a href="{{ route('shipments.create', ['shipment_type' => 'domestic']) }}" 
                       class="p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-semibold text-xs flex items-center gap-2.5 transition border border-slate-700">
                        <i class="fas fa-truck-fast text-base text-teal-400"></i>
                        <div>
                            <p class="leading-tight">2. Domestic Courier</p>
                            <p class="text-[10px] font-normal text-slate-400">Nepal 7 Provinces</p>
                        </div>
                    </a>

                    <!-- 3. International Air Cargo -->
                    <a href="{{ route('shipments.create', ['shipment_type' => 'international']) }}" 
                       class="p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-semibold text-xs flex items-center gap-2.5 transition border border-slate-700">
                        <i class="fas fa-plane-departure text-base text-sky-400"></i>
                        <div>
                            <p class="leading-tight">3. International Cargo</p>
                            <p class="text-[10px] font-normal text-slate-400">Worldwide Air Freight</p>
                        </div>
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('seller.orders.store') }}" id="orderForm">
                @csrf

                <!-- Customer Details -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">👤 Customer Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Customer Name <span class="text-red-500">*</span></label>
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="Full name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Phone <span class="text-red-500">*</span></label>
                            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="Phone number">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" name="customer_email" value="{{ old('customer_email') }}" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="Email address">
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">📦 Order Items</h3>
                    <div id="items-container">
                        <div class="item-row grid grid-cols-4 gap-3 mb-3">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium mb-1">Product Name <span class="text-red-500">*</span></label>
                                <input type="text" name="items[0][name]" required 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                       placeholder="Product name">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Qty <span class="text-red-500">*</span></label>
                                <input type="number" name="items[0][quantity]" min="1" value="1" required 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Price (Rs.) <span class="text-red-500">*</span></label>
                                <div class="flex gap-2">
                                    <input type="number" name="items[0][price]" step="0.01" min="0" required 
                                           class="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                           placeholder="0.00">
                                    <button type="button" onclick="removeItemRow(this)" class="text-red-500 hover:text-red-700" title="Remove item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="addItemRow()" class="text-teal-600 hover:text-teal-800 text-sm mt-2">
                        <i class="fas fa-plus mr-1"></i> Add Item
                    </button>
                </div>

                <!-- Payment Method -->
<div class="mb-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">💳 Payment Method</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="payment-card selected" onclick="selectPayment('prepaid')" id="payment-prepaid">
            <div class="payment-icon">💳</div>
            <div class="payment-label">Prepaid</div>
            <p class="text-sm text-gray-500">Payment already made</p>
        </div>
        <div class="payment-card" onclick="selectPayment('cod')" id="payment-cod">
            <div class="payment-icon">💵</div>
            <div class="payment-label">Cash on Delivery</div>
            <p class="text-sm text-gray-500">Pay at delivery</p>
        </div>
    </div>
    <input type="hidden" name="payment_method" id="payment_method" value="prepaid">
    <input type="hidden" name="payment_status" id="payment_status" value="paid">
    
    <!-- COD Specific Fields -->
    <div id="cod-fields" class="hidden mt-4 p-4 border-2 border-orange-200 rounded-lg bg-orange-50">
        <h4 class="font-semibold text-orange-700 mb-3">📋 COD Details</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">COD Amount <span class="text-red-500">*</span></label>
                <input type="number" name="cod_amount" step="0.01" min="0" 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                       placeholder="Total COD amount to collect">
                <p class="text-xs text-gray-500 mt-1">Total amount to be collected from customer</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Delivery Charge <span class="text-red-500">*</span></label>
                <input type="number" name="delivery_charge" step="0.01" min="0" value="100"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                <p class="text-xs text-gray-500 mt-1">Delivery charge to be collected</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Upload Invoice/Bill <span class="text-red-500">*</span></label>
                <input type="file" name="cod_invoice" accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                <p class="text-xs text-gray-500 mt-1">Upload invoice or bill showing COD amount (PDF, JPG, PNG)</p>
                <p class="text-xs text-orange-600 mt-1">⚠️ Mandatory for COD orders</p>
            </div>

            <!-- COD Remittance Account Notice -->
            <div class="md:col-span-2 p-3.5 rounded-xl bg-white border border-orange-300 shadow-xs text-xs space-y-1">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900 flex items-center gap-1.5">
                        <i class="fas fa-building-columns text-emerald-600"></i>
                        <span>COD Cash Remittance Destination</span>
                    </span>
                    <a href="{{ route('seller.settings') }}" target="_blank" class="text-[11px] text-teal-700 font-bold hover:underline">
                        Set Payout Account &rarr;
                    </a>
                </div>
                @php
                    $sellerUser = Auth::user();
                @endphp
                @if($sellerUser->bank_name || $sellerUser->account_number)
                    <p class="text-slate-600">
                        Collected COD cash will be remitted to: <strong>{{ $sellerUser->bank_name }}</strong> (A/C: {{ $sellerUser->account_number }})
                    </p>
                @else
                    <p class="text-amber-700 font-medium">
                        ⚠️ Please configure your Bank Account or QR (eSewa / Khalti) in Settings so COD collections can be remitted to you automatically.
                    </p>
                @endif
            </div>
        </div>
    </div>
    
    <div id="payment-status-container" class="mt-3">
        <label class="block text-sm font-medium mb-1">Payment Status</label>
        <select name="payment_status_display" id="payment_status_select" class="w-full md:w-1/2 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            <option value="paid">✅ Paid</option>
            <option value="pending">⏳ Pending</option>
        </select>
    </div>
</div>

                <!-- Delivery Addresses -->
                <div class="mb-6">
                    <div class="flex items-center justify-between border-b pb-2 mb-3">
                        <h3 class="text-lg font-semibold text-gray-700">📍 Delivery Addresses</h3>
                        <span class="text-sm text-gray-500" id="deliveryCountDisplay">1 delivery</span>
                    </div>
                    
                    <!-- Delivery Summary -->
                    <div class="delivery-summary flex items-center justify-between">
                        <div>
                            <span class="font-medium">Total Deliveries:</span>
                            <span class="count" id="totalDeliveriesCount">1</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">Click "Add Delivery" for multiple addresses</span>
                        </div>
                    </div>

                    <!-- Deliveries Container -->
                    <div id="deliveries-container">
                        <!-- Delivery 1 -->
                        <div class="delivery-card" id="delivery-0">
                            <div class="flex items-center justify-between mb-3">
                                <div class="delivery-number">📍 Delivery #1</div>
                                <button type="button" onclick="removeDelivery(0)" class="remove-delivery hidden">
                                    <i class="fas fa-trash mr-1"></i> Remove
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Recipient Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="deliveries[0][recipient_name]" value="{{ old('deliveries.0.recipient_name') }}" required 
                                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                           placeholder="Recipient name">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Recipient Phone <span class="text-red-500">*</span></label>
                                    <input type="text" name="deliveries[0][recipient_phone]" value="{{ old('deliveries.0.recipient_phone') }}" required 
                                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                           placeholder="Phone number">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium mb-1">Address <span class="text-red-500">*</span></label>
                                    <textarea name="deliveries[0][address]" rows="2" required 
                                              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                              placeholder="Full delivery address">{{ old('deliveries.0.address') }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Address Type</label>
                                    <select name="deliveries[0][address_type]" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                        <option value="home">🏠 Home</option>
                                        <option value="office">🏢 Office</option>
                                        <option value="other">📍 Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Landmark (Optional)</label>
                                    <input type="text" name="deliveries[0][landmark]" value="{{ old('deliveries.0.landmark') }}" 
                                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                           placeholder="Nearby landmark">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium mb-1">Instructions (Optional)</label>
                                    <input type="text" name="deliveries[0][instructions]" value="{{ old('deliveries.0.instructions') }}" 
                                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                           placeholder="Gate code, floor, building name, etc.">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium mb-1">Location on Map</label>
                                    <div id="delivery-map-0" class="delivery-map" style="height: 200px; border-radius: 8px; border: 1px solid #ddd;"></div>
                                    <div class="flex gap-2 mt-2 flex-wrap">
                                        <input type="hidden" name="deliveries[0][latitude]" id="latitude-0" value="{{ old('deliveries.0.latitude') }}">
                                        <input type="hidden" name="deliveries[0][longitude]" id="longitude-0" value="{{ old('deliveries.0.longitude') }}">
                                        <button type="button" onclick="getCurrentLocation(0)" class="text-sm text-teal-600 hover:text-teal-800">
                                            <i class="fas fa-location-arrow mr-1"></i> Use Current Location
                                        </button>
                                        <button type="button" onclick="searchLocation(0)" class="text-sm text-teal-600 hover:text-teal-800">
                                            <i class="fas fa-search mr-1"></i> Search Location
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Delivery Button -->
                    <button type="button" onclick="addDelivery()" class="add-delivery-btn w-full mt-3">
                        <i class="fas fa-plus-circle text-2xl block mb-1"></i>
                        <span class="text-sm font-medium">Add Another Delivery Address</span>
                    </button>
                </div>

                <!-- Delivery Options -->
                <div class="mb-6 border-t pt-4">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">⏰ Delivery Options</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Delivery Date</label>
                            <input type="date" name="delivery_date" value="{{ old('delivery_date', date('Y-m-d', strtotime('+1 day'))) }}" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Time Slot</label>
                            <select name="delivery_time_slot" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <option value="">Select Time Slot</option>
                                <option value="morning">🌅 Morning (8 AM - 12 PM)</option>
                                <option value="afternoon">☀️ Afternoon (12 PM - 4 PM)</option>
                                <option value="evening">🌆 Evening (4 PM - 8 PM)</option>
                                <option value="anytime">🕐 Anytime</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Special Instructions -->
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-1">📝 Special Instructions (Optional)</label>
                    <textarea name="special_instructions" rows="2" 
                              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('special_instructions') }}</textarea>
                </div>

                <!-- Order Summary -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6 border">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">📊 Order Summary</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Subtotal</p>
                            <p class="font-semibold" id="summary-subtotal">Rs. 0.00</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Tax (13%)</p>
                            <p class="font-semibold" id="summary-tax">Rs. 0.00</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Shipping</p>
                            <p class="font-semibold" id="summary-shipping">Rs. 0.00</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Total</p>
                            <p class="font-semibold text-teal-600" id="summary-total">Rs. 0.00</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-save mr-2"></i> Create Order
                    </button>
                    <button type="reset" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        <i class="fas fa-undo mr-2"></i> Reset
                    </button>
                    <a href="{{ route('seller.orders') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let deliveryCount = 1;
    let itemCount = 1;
    let maps = {};

    // =============================================
    // PAYMENT METHOD
    // =============================================
    function selectPayment(method) {
    // Reset all
    document.querySelectorAll('.payment-card').forEach(c => c.classList.remove('selected'));
    document.getElementById(`payment-${method}`).classList.add('selected');
    document.getElementById('payment_method').value = method;
    
    const statusSelect = document.getElementById('payment_status_select');
    const codFields = document.getElementById('cod-fields');
    
    if (method === 'cod') {
        statusSelect.value = 'pending';
        statusSelect.disabled = true;
        document.getElementById('payment_status').value = 'pending';
        codFields.classList.remove('hidden');
    } else {
        statusSelect.disabled = false;
        document.getElementById('payment_status').value = statusSelect.value;
        codFields.classList.add('hidden');
        // Reset COD fields
        document.querySelector('input[name="cod_amount"]').value = '';
        document.querySelector('input[name="delivery_charge"]').value = '100';
        document.querySelector('input[name="cod_invoice"]').value = '';
    }
}

    document.addEventListener('DOMContentLoaded', function() {
        // Default payment selection
        const defaultMethod = document.getElementById('payment_method').value || 'prepaid';
        selectPayment(defaultMethod);
        
        // Payment status change
        document.getElementById('payment_status_select').addEventListener('change', function() {
            document.getElementById('payment_status').value = this.value;
        });
    });

    // =============================================
    // ORDER ITEMS
    // =============================================
    function addItemRow() {
        const container = document.getElementById('items-container');
        const row = document.createElement('div');
        row.className = 'item-row grid grid-cols-4 gap-3 mb-3';
        row.innerHTML = `
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Product Name <span class="text-red-500">*</span></label>
                <input type="text" name="items[${itemCount}][name]" required 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                       placeholder="Product name">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Qty <span class="text-red-500">*</span></label>
                <input type="number" name="items[${itemCount}][quantity]" min="1" value="1" required 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Price (Rs.) <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <input type="number" name="items[${itemCount}][price]" step="0.01" min="0" required 
                           class="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                           placeholder="0.00">
                    <button type="button" onclick="removeItemRow(this)" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(row);
        itemCount++;
        updateSummary();
    }

    function removeItemRow(btn) {
        const row = btn.closest('.item-row');
        if (document.querySelectorAll('.item-row').length > 1) {
            row.remove();
            updateSummary();
        } else {
            alert('You need at least one item.');
        }
    }

    // =============================================
    // DELIVERY ADDRESSES
    // =============================================
    function addDelivery() {
        const container = document.getElementById('deliveries-container');
        const deliveryId = deliveryCount;
        
        const div = document.createElement('div');
        div.className = 'delivery-card';
        div.id = `delivery-${deliveryId}`;
        div.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <div class="delivery-number">📍 Delivery #${deliveryId + 1}</div>
                <button type="button" onclick="removeDelivery(${deliveryId})" class="remove-delivery">
                    <i class="fas fa-trash mr-1"></i> Remove
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Recipient Name <span class="text-red-500">*</span></label>
                    <input type="text" name="deliveries[${deliveryId}][recipient_name]" required 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                           placeholder="Recipient name">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Recipient Phone <span class="text-red-500">*</span></label>
                    <input type="text" name="deliveries[${deliveryId}][recipient_phone]" required 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                           placeholder="Phone number">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Address <span class="text-red-500">*</span></label>
                    <textarea name="deliveries[${deliveryId}][address]" rows="2" required 
                              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                              placeholder="Full delivery address"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Address Type</label>
                    <select name="deliveries[${deliveryId}][address_type]" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="home">🏠 Home</option>
                        <option value="office">🏢 Office</option>
                        <option value="other">📍 Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Landmark (Optional)</label>
                    <input type="text" name="deliveries[${deliveryId}][landmark]" 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                           placeholder="Nearby landmark">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Instructions (Optional)</label>
                    <input type="text" name="deliveries[${deliveryId}][instructions]" 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                           placeholder="Gate code, floor, building name, etc.">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Location on Map</label>
                    <div id="delivery-map-${deliveryId}" class="delivery-map" style="height: 200px; border-radius: 8px; border: 1px solid #ddd;"></div>
                    <div class="flex gap-2 mt-2 flex-wrap">
                        <input type="hidden" name="deliveries[${deliveryId}][latitude]" id="latitude-${deliveryId}">
                        <input type="hidden" name="deliveries[${deliveryId}][longitude]" id="longitude-${deliveryId}">
                        <button type="button" onclick="getCurrentLocation(${deliveryId})" class="text-sm text-teal-600 hover:text-teal-800">
                            <i class="fas fa-location-arrow mr-1"></i> Use Current Location
                        </button>
                        <button type="button" onclick="searchLocation(${deliveryId})" class="text-sm text-teal-600 hover:text-teal-800">
                            <i class="fas fa-search mr-1"></i> Search Location
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(div);
        
        setTimeout(() => {
            initializeMap(deliveryId);
        }, 100);
        
        deliveryCount++;
        updateDeliveryCount();
        updateSummary();
    }

    function removeDelivery(id) {
        const element = document.getElementById(`delivery-${id}`);
        if (element && document.querySelectorAll('.delivery-card').length > 1) {
            element.remove();
            updateDeliveryCount();
            updateSummary();
        } else {
            alert('You need at least one delivery address.');
        }
    }

    function updateDeliveryCount() {
        const count = document.querySelectorAll('.delivery-card').length;
        document.getElementById('totalDeliveriesCount').textContent = count;
        document.getElementById('deliveryCountDisplay').textContent = count + ' delivery' + (count > 1 ? 's' : '');
    }

    // =============================================
    // ORDER SUMMARY
    // =============================================
    function updateSummary() {
        let subtotal = 0;
        const itemRows = document.querySelectorAll('.item-row');
        itemRows.forEach(row => {
            const price = parseFloat(row.querySelector('input[name*="[price]"]')?.value) || 0;
            const qty = parseInt(row.querySelector('input[name*="[quantity]"]')?.value) || 0;
            subtotal += price * qty;
        });
        
        const deliveryCount = document.querySelectorAll('.delivery-card').length;
        const tax = subtotal * 0.13;
        const shipping = deliveryCount * 100;
        const total = subtotal + tax + shipping;
        
        document.getElementById('summary-subtotal').textContent = 'Rs. ' + subtotal.toFixed(2);
        document.getElementById('summary-tax').textContent = 'Rs. ' + tax.toFixed(2);
        document.getElementById('summary-shipping').textContent = 'Rs. ' + shipping.toFixed(2);
        document.getElementById('summary-total').textContent = 'Rs. ' + total.toFixed(2);
    }

    // =============================================
    // MAP FUNCTIONS
    // =============================================
    function initializeMap(deliveryId) {
        const mapId = `delivery-map-${deliveryId}`;
        const mapElement = document.getElementById(mapId);
        if (!mapElement) return;
        
        const defaultLat = 27.7172;
        const defaultLng = 85.3240;
        
        const map = L.map(mapId).setView([defaultLat, defaultLng], 13);
        maps[deliveryId] = map;
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);
        
        const marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
        
        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            document.getElementById(`latitude-${deliveryId}`).value = pos.lat;
            document.getElementById(`longitude-${deliveryId}`).value = pos.lng;
        });
        
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            document.getElementById(`latitude-${deliveryId}`).value = e.latlng.lat;
            document.getElementById(`longitude-${deliveryId}`).value = e.latlng.lng;
        });
        
        map.marker = marker;
    }

    function getCurrentLocation(deliveryId) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    updateMapLocation(deliveryId, position.coords.latitude, position.coords.longitude);
                },
                function() {
                    alert('Unable to get location. Please enter address manually.');
                }
            );
        } else {
            alert('Geolocation is not supported by this browser.');
        }
    }

    function searchLocation(deliveryId) {
        const address = prompt('Enter location to search:');
        if (address) {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        updateMapLocation(deliveryId, parseFloat(data[0].lat), parseFloat(data[0].lon));
                    } else {
                        alert('Location not found. Please try a different search term.');
                    }
                })
                .catch(() => {
                    alert('Error searching location. Please try again.');
                });
        }
    }

    function updateMapLocation(deliveryId, lat, lng) {
        const map = maps[deliveryId];
        if (map) {
            map.setView([lat, lng], 15);
            if (map.marker) {
                map.marker.setLatLng([lat, lng]);
            }
            document.getElementById(`latitude-${deliveryId}`).value = lat;
            document.getElementById(`longitude-${deliveryId}`).value = lng;
        }
    }

    // =============================================
    // INITIALIZE
    // =============================================
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            initializeMap(0);
        }, 200);
        
        // Update summary on input change
        document.addEventListener('input', updateSummary);
        
        // Update summary on delivery change
        const observer = new MutationObserver(updateSummary);
        const container = document.getElementById('deliveries-container');
        observer.observe(container, { childList: true, subtree: true });
    });
</script>
@endpush
@endsection