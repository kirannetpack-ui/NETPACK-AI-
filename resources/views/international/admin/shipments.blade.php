@extends('layouts.app')

@section('title', 'International Shipments')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">International Shipments</h1>
                <p class="text-sm text-gray-500 mt-1">Manage international shipments</p>
            </div>
            <a href="{{ route('international.shipments.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-plus mr-2"></i> Create Shipment
            </a>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Tracking</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Customer</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Destination</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Partner</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Amount</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments as $shipment)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-mono text-sm">{{ $shipment->tracking_number ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $shipment->customer->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $shipment->receiver_country ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $shipment->overseasPartner->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium 
                                        {{ $shipment->status === 'delivered' ? 'bg-green-100 text-green-800' : 
                                           ($shipment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($shipment->status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                           'bg-blue-100 text-blue-800')) }}">
                                        {{ ucfirst($shipment->status ?? 'Unknown') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-medium">${{ number_format($shipment->total_amount ?? 0, 2) }}</td>
                                <td class="py-3 px-4">
    <div class="flex gap-2">
        <a href="{{ route('international.shipments.show', $shipment->id) }}" class="text-blue-600 hover:text-blue-800" title="View">
            <i class="fas fa-eye"></i>
        </a>
        <a href="{{ route('hawb.international', $shipment->id) }}" target="_blank" class="text-purple-600 hover:text-purple-800" title="Generate HAWB">
            <i class="fas fa-file-alt"></i>
        </a>
        <button onclick="openTrackingModal('{{ $shipment->id }}', '{{ $shipment->tracking_number }}')" 
                class="text-teal-600 hover:text-teal-800" title="Update Tracking">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-ship text-4xl block mb-2"></i>
                                    No international shipments found.
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