@extends('layouts.app')

@section('title', 'Create Order')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Create New Order</h1>
            <p class="text-sm text-gray-500 mt-1">Create a new e-commerce order</p>
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

            <form method="POST" action="{{ route('seller.orders.store') }}">
                @csrf

                <h3 class="font-semibold text-gray-700 mb-3">Customer Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium mb-1">Customer Name *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" required 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Phone *</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" required 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="customer_email" value="{{ old('customer_email') }}" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Delivery Address *</label>
                        <textarea name="shipping_address" rows="2" required 
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('shipping_address') }}</textarea>
                    </div>
                </div>

                <h3 class="font-semibold text-gray-700 mb-3">Order Items</h3>
                <div id="items-container">
                    <div class="item-row grid grid-cols-4 gap-3 mb-3">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-1">Product Name</label>
                            <input type="text" name="items[0][name]" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Quantity</label>
                            <input type="number" name="items[0][quantity]" min="1" value="1" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Price (Rs.)</label>
                            <input type="number" name="items[0][price]" step="0.01" min="0" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>
                </div>

                <button type="button" onclick="addItemRow()" class="text-teal-600 hover:text-teal-800 text-sm mb-4">
                    <i class="fas fa-plus mr-1"></i> Add Item
                </button>

                <h3 class="font-semibold text-gray-700 mb-3">Delivery Options</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium mb-1">Delivery Date</label>
                        <input type="date" name="delivery_date" value="{{ old('delivery_date') }}" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Time Slot</label>
                        <select name="delivery_time_slot" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">Select Time Slot</option>
                            <option value="morning">Morning (8 AM - 12 PM)</option>
                            <option value="afternoon">Afternoon (12 PM - 4 PM)</option>
                            <option value="evening">Evening (4 PM - 8 PM)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-1">Special Instructions</label>
                    <textarea name="special_instructions" rows="2" 
                              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('special_instructions') }}</textarea>
                </div>

                <div class="flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-save mr-2"></i> Create Order
                    </button>
                    <a href="{{ route('seller.dashboard') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let itemCount = 1;
    
    function addItemRow() {
        const container = document.getElementById('items-container');
        const row = document.createElement('div');
        row.className = 'item-row grid grid-cols-4 gap-3 mb-3';
        row.innerHTML = `
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Product Name</label>
                <input type="text" name="items[${itemCount}][name]" required 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Quantity</label>
                <input type="number" name="items[${itemCount}][quantity]" min="1" value="1" required 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Price (Rs.)</label>
                <input type="number" name="items[${itemCount}][price]" step="0.01" min="0" required 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                <button type="button" onclick="removeItemRow(this)" class="text-red-500 hover:text-red-700 text-xs mt-1">
                    <i class="fas fa-times"></i> Remove
                </button>
            </div>
        `;
        container.appendChild(row);
        itemCount++;
    }
    
    function removeItemRow(btn) {
        const row = btn.closest('.item-row');
        if (document.querySelectorAll('.item-row').length > 1) {
            row.remove();
        } else {
            alert('You need at least one item.');
        }
    }
</script>
@endpush
@endsection