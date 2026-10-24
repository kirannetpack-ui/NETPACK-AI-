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

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Tracking</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Client</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Service</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Weight</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Amount</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments as $shipment)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-mono text-sm">{{ $shipment->tracking_number }}</td>
                                <td class="py-3 px-4">{{ $shipment->client->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $shipment->service_type === 'flash' ? 'red' : ($shipment->service_type === 'same_day' ? 'orange' : ($shipment->service_type === 'standard' ? 'blue' : 'purple')) }}-100 text-{{ $shipment->service_type === 'flash' ? 'red' : ($shipment->service_type === 'same_day' ? 'orange' : ($shipment->service_type === 'standard' ? 'blue' : 'purple')) }}-800">
                                        {{ $shipment->service_name }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">{{ $shipment->weight }} kg</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $shipment->status_color }}-100 text-{{ $shipment->status_color }}-800">
                                        {{ $shipment->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-medium">Rs. {{ number_format($shipment->total_amount, 2) }}</td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('admin.domestic.shipments.show', $shipment->id) }}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-box text-4xl block mb-2"></i>
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