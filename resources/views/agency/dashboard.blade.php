@extends('layouts.agency')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Arrived</p>
                    <p class="text-3xl font-bold text-green-600">{{ $stats['arrived'] }}</p>
                </div>
                <i class="fas fa-inbox text-4xl text-green-500"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Departed</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['departed'] }}</p>
                </div>
                <i class="fas fa-truck text-4xl text-blue-500"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Processed</p>
                    <p class="text-3xl font-bold text-teal-600">{{ $stats['total_processed'] }}</p>
                </div>
                <i class="fas fa-boxes text-4xl text-teal-500"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Recently Arrived Shipments</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">HAWB</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Tracking</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Receiver</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Arrived At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentArrivals as $shipment)
                    <tr>
                        <td class="px-6 py-4 font-mono text-sm">{{ $shipment->hawb_number }}</td>
                        <td class="px-6 py-4 font-mono text-sm">{{ $shipment->tracking_number }}</td>
                        <td class="px-6 py-4">{{ $shipment->receiver_name }}</td>
                        <td class="px-6 py-4 text-sm">{{ $shipment->arrived_at_agency->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('agency.shipment.show', $shipment) }}" class="text-teal-600 hover:text-teal-700">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No arrivals yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('agency.scan') }}" class="block">
            <div class="bg-gradient-to-r from-teal-600 to-emerald-600 rounded-xl p-6 text-white text-center hover:shadow-xl transition">
                <i class="fas fa-qrcode text-4xl mb-3"></i>
                <h3 class="text-xl font-bold">Scan QR Code</h3>
                <p class="text-teal-100 mt-1">Mark arrival or departure</p>
            </div>
        </a>
        <a href="{{ route('agency.shipments') }}" class="block">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl p-6 text-white text-center hover:shadow-xl transition">
                <i class="fas fa-list text-4xl mb-3"></i>
                <h3 class="text-xl font-bold">View All Shipments</h3>
                <p class="text-blue-100 mt-1">Manage processed shipments</p>
            </div>
        </a>
    </div>
</div>
@endsection