@extends('layouts.app')

@section('title', 'Shipment Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Shipment Details</h1>
                <p class="text-sm text-gray-500 mt-1">View domestic shipment information</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('hawb.domestic', $shipment->id) }}" target="_blank" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 text-sm font-semibold transition flex items-center gap-1.5">
                    <i class="fas fa-print"></i> Print HAWB
                </a>
                <a href="{{ route('tracking.show', $shipment->tracking_number) }}" target="_blank" class="bg-slate-800 text-white px-4 py-2 rounded-lg hover:bg-slate-900 text-sm font-semibold transition flex items-center gap-1.5">
                    <i class="fas fa-search-location"></i> Public Tracker
                </a>
                <a href="{{ route('domestic.shipments') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-semibold transition">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Tracking Number</p>
                    <p class="font-medium font-mono">{{ $shipment->tracking_number ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Service Type</p>
                    <p class="font-medium">{{ strtoupper($shipment->service_type ?? 'N/A') }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Status</p>
                    <p class="font-medium">
                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                            {{ $shipment->status === 'delivered' ? 'bg-green-100 text-green-800' : 
                               ($shipment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                               ($shipment->status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                               'bg-blue-100 text-blue-800')) }}">
                            {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                        </span>
                    </p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Weight</p>
                    <p class="font-medium">{{ number_format($shipment->weight ?? 0, 2) }} kg</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Amount</p>
                    <p class="font-medium">Rs. {{ number_format($shipment->total_amount ?? 0, 2) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Client</p>
                    <p class="font-medium">{{ $shipment->client->name ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Partner</p>
                    <p class="font-medium">{{ $shipment->partner->name ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Created At</p>
                    <p class="font-medium">{{ $shipment->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Last Updated</p>
                    <p class="font-medium">{{ $shipment->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>

            <!-- Status Update Form -->
            <div class="mt-6 pt-4 border-t">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Update Status</h3>
                <form method="POST" action="{{ route('domestic.shipments.update-status', $shipment->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Status</label>
                            <select name="status" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <option value="pending" {{ $shipment->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ $shipment->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="picked_up" {{ $shipment->status === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                                <option value="in_transit" {{ $shipment->status === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                <option value="out_for_delivery" {{ $shipment->status === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                <option value="delivered" {{ $shipment->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $shipment->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Location</label>
                            <input type="text" name="location" placeholder="e.g., Kathmandu" 
                                   class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Notes</label>
                            <input type="text" name="notes" placeholder="Status notes" 
                                   class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-save mr-2"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection