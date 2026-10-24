@extends('layouts.app')

@section('title', 'Shipment Reports')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Shipment Reports</h1>
                <p class="text-sm text-gray-500 mt-1">View and filter shipment data</p>
            </div>
            <a href="{{ route('admin.reports') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>

        <div class="p-6">
            <!-- Filters -->
            <form method="GET" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select name="status" class="w-full border rounded-lg px-3 py-2">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
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
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Customer</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Service</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Weight</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Amount</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments as $shipment)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4 font-mono text-sm">{{ $shipment->tracking_number ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $shipment->customer->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ ucfirst($shipment->service_type ?? 'N/A') }}</td>
                                <td class="py-3 px-4">{{ $shipment->chargeable_weight ?? 0 }} kg</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $shipment->status === 'delivered' ? 'green' : ($shipment->status === 'pending' ? 'yellow' : 'blue') }}-100 text-{{ $shipment->status === 'delivered' ? 'green' : ($shipment->status === 'pending' ? 'yellow' : 'blue') }}-800">
                                        {{ ucfirst($shipment->status ?? 'Unknown') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-medium">Rs. {{ number_format($shipment->total_amount ?? 0, 2) }}</td>
                                <td class="py-3 px-4 text-sm">{{ $shipment->created_at ? $shipment->created_at->format('Y-m-d') : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">No shipments found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $shipments->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection