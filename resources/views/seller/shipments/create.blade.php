@extends('layouts.seller')

@section('title', 'Create Shipment')
@section('page-title', 'Create New Shipment')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Create New Shipment</h1>
                <p class="text-sm text-gray-500 mt-1">Create a shipment for a completed order</p>
            </div>
            <a href="{{ route('seller.shipments') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Shipments
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

            @if($orders->count() > 0)
                <form method="POST" action="{{ route('seller.shipments.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Order Selection -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Select Order <span class="text-red-500">*</span></label>
                            <select name="order_id" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <option value="">Select an order</option>
                                @foreach($orders as $order)
                                    <option value="{{ $order->id }}">
                                        #{{ $order->order_number }} - {{ $order->customer_name }} (Rs. {{ number_format($order->total_amount, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Only completed orders without shipments are shown</p>
                        </div>

                        <!-- Receiver Details -->
                        <div class="md:col-span-2 border-t pt-4 mt-2">
                            <h3 class="text-lg font-semibold text-gray-700 mb-3">Receiver Details</h3>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Receiver Name <span class="text-red-500">*</span></label>
                            <input type="text" name="receiver_name" value="{{ old('receiver_name') }}" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="Full name">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Receiver Phone <span class="text-red-500">*</span></label>
                            <input type="text" name="receiver_phone" value="{{ old('receiver_phone') }}" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="Phone number">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Receiver Address <span class="text-red-500">*</span></label>
                            <textarea name="receiver_address" rows="2" required 
                                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                      placeholder="Full address">{{ old('receiver_address') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">City <span class="text-red-500">*</span></label>
                            <input type="text" name="receiver_city" value="{{ old('receiver_city') }}" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="City">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Country <span class="text-red-500">*</span></label>
                            <input type="text" name="receiver_country" value="{{ old('receiver_country') }}" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="Country">
                        </div>

                        <!-- Shipment Details -->
                        <div class="md:col-span-2 border-t pt-4 mt-2">
                            <h3 class="text-lg font-semibold text-gray-700 mb-3">Shipment Details</h3>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Weight (kg) <span class="text-red-500">*</span></label>
                            <input type="number" name="weight" step="0.01" min="0.01" value="{{ old('weight', 1) }}" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="0.00">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Service Type <span class="text-red-500">*</span></label>
                            <select name="service_type" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <option value="standard">🚚 Standard (3-5 days)</option>
                                <option value="express">⚡ Express (1-2 days)</option>
                                <option value="priority">🔴 Priority (Same day)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Package Type <span class="text-red-500">*</span></label>
                            <select name="package_type" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <option value="parcel">📦 Parcel</option>
                                <option value="box">📦 Box</option>
                                <option value="envelope">✉️ Envelope</option>
                                <option value="other">📦 Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t mt-6">
                        <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-save mr-2"></i> Create Shipment
                        </button>
                        <a href="{{ route('seller.shipments') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-truck text-5xl text-gray-300 mb-4 block"></i>
                    <h3 class="text-lg font-semibold text-gray-700">No Orders Available</h3>
                    <p class="text-gray-500 mt-2">You don't have any completed orders without shipments.</p>
                    <a href="{{ route('seller.orders') }}" class="inline-block mt-4 bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-shopping-cart mr-2"></i> View Orders
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection