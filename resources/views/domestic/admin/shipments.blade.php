@extends('layouts.app')

@section('title', 'Domestic Shipments')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Domestic Shipments</h1>
            <p class="text-sm text-gray-500 mt-1">Manage domestic shipments</p>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filters -->
            <form method="GET" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select name="status" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="out_for_delivery" {{ request('status') === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Partner</label>
                    <select name="partner_id" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">All Partners</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Service Type</label>
                    <select name="service_type" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">All</option>
                        <option value="flash" {{ request('service_type') === 'flash' ? 'selected' : '' }}>FLASH</option>
                        <option value="same_day" {{ request('service_type') === 'same_day' ? 'selected' : '' }}>SAME DAY</option>
                        <option value="standard" {{ request('service_type') === 'standard' ? 'selected' : '' }}>STANDARD</option>
                        <option value="himalayan" {{ request('service_type') === 'himalayan' ? 'selected' : '' }}>HIMALAYAN</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 w-full">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Tracking</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Client</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Partner</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Service</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Amount</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments as $shipment)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-mono text-sm">{{ $shipment->tracking_number ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $shipment->client->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $shipment->partner->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium 
                                        {{ $shipment->service_type === 'flash' ? 'bg-red-100 text-red-800' : 
                                           ($shipment->service_type === 'same_day' ? 'bg-orange-100 text-orange-800' : 
                                           ($shipment->service_type === 'standard' ? 'bg-blue-100 text-blue-800' : 
                                           'bg-purple-100 text-purple-800')) }}">
                                        {{ strtoupper($shipment->service_type ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium 
                                        {{ $shipment->status === 'delivered' ? 'bg-green-100 text-green-800' : 
                                           ($shipment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($shipment->status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                           'bg-blue-100 text-blue-800')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-medium">Rs. {{ number_format($shipment->total_amount ?? 0, 2) }}</td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('domestic.shipments.show', $shipment->id) }}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-truck text-4xl block mb-2"></i>
                                    No domestic shipments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $shipments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection