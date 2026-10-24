@extends('layouts.app')

@section('title', 'Create International Shipment')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Create International Shipment</h1>
            <p class="text-sm text-gray-500 mt-1">Create a new international shipment</p>
        </div>

        <div class="p-6">
            <!-- Shipment Type Selection -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="border-2 border-teal-600 rounded-lg p-4 bg-teal-50">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-ship text-2xl text-teal-600"></i>
                        <div>
                            <h3 class="font-semibold text-gray-800">International</h3>
                            <p class="text-xs text-gray-500">Worldwide delivery</p>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('international.shipments.store') }}">
                @csrf

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Sender Information -->
                <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Sender Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium mb-1">Sender Name *</label>
                        <input type="text" name="sender_name" value="{{ old('sender_name', auth()->user()->name) }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Sender Phone *</label>
                        <input type="tel" name="sender_phone" value="{{ old('sender_phone', auth()->user()->phone) }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Sender Address *</label>
                        <input type="text" name="sender_address" value="{{ old('sender_address', auth()->user()->address) }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Sender Country</label>
                        <input type="text" name="sender_country" value="{{ old('sender_country', 'Nepal') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Sender City</label>
                        <input type="text" name="sender_city" value="{{ old('sender_city', 'Kathmandu') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <!-- Receiver Information -->
                <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Receiver Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium mb-1">Receiver Name *</label>
                        <input type="text" name="receiver_name" value="{{ old('receiver_name') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Receiver Phone *</label>
                        <input type="tel" name="receiver_phone" value="{{ old('receiver_phone') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Receiver Address *</label>
                        <input type="text" name="receiver_address" value="{{ old('receiver_address') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Receiver Country *</label>
                        <input type="text" name="receiver_country" value="{{ old('receiver_country') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Receiver City *</label>
                        <input type="text" name="receiver_city" value="{{ old('receiver_city') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Postal Code</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <!-- Package Details -->
                <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Package Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium mb-1">Weight (kg) *</label>
                        <input type="number" name="weight" step="0.01" value="{{ old('weight') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Length (cm)</label>
                        <input type="number" name="length" step="0.01" value="{{ old('length') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Width (cm)</label>
                        <input type="number" name="width" step="0.01" value="{{ old('width') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Height (cm)</label>
                        <input type="number" name="height" step="0.01" value="{{ old('height') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Package Type *</label>
                        <select name="package_type" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="box" {{ old('package_type') === 'box' ? 'selected' : '' }}>Box</option>
                            <option value="carton" {{ old('package_type') === 'carton' ? 'selected' : '' }}>Carton</option>
                            <option value="pallet" {{ old('package_type') === 'pallet' ? 'selected' : '' }}>Pallet</option>
                            <option value="envelope" {{ old('package_type') === 'envelope' ? 'selected' : '' }}>Envelope</option>
                            <option value="tube" {{ old('package_type') === 'tube' ? 'selected' : '' }}>Tube</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Service Type *</label>
                        <select name="service_type" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="express" {{ old('service_type') === 'express' ? 'selected' : '' }}>Express</option>
                            <option value="standard" {{ old('service_type') === 'standard' ? 'selected' : '' }}>Standard</option>
                            <option value="economy" {{ old('service_type') === 'economy' ? 'selected' : '' }}>Economy</option>
                            <option value="priority" {{ old('service_type') === 'priority' ? 'selected' : '' }}>Priority</option>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium mb-1">Package Description</label>
                        <textarea name="description" rows="2" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Additional Options -->
                <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Additional Options</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="requires_signature" value="1" {{ old('requires_signature') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            <span class="text-sm font-medium">Requires Signature</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_insured" value="1" {{ old('is_insured') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            <span class="text-sm font-medium">Insurance</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_cod" value="1" {{ old('is_cod') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            <span class="text-sm font-medium">Cash on Delivery</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Insurance Amount ($)</label>
                        <input type="number" name="insurance_amount" step="0.01" value="{{ old('insurance_amount', 0) }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <!-- Submit -->
                <div class="mt-6 flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-save mr-2"></i> Create Shipment
                    </button>
                    <a href="{{ route('international.shipments') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection