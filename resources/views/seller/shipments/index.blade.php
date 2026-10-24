@extends('layouts.seller')

@section('title', 'Shipments')
@section('page-title', 'My Shipments')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Total Shipments</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-sm text-gray-500">In Transit</p>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['processing'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Delivered</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['delivered'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">My Shipments</h2>
                <p class="text-sm text-gray-500 mt-1">Manage your shipments</p>
            </div>
            <a href="{{ route('seller.shipments.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition flex items-center gap-2">
                <i class="fas fa-plus"></i> New Shipment
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

            <!-- Filters -->
            <form method="GET" class="flex flex-wrap gap-3 mb-6">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search shipments..." 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <select name="status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>
                </div>
                @if(request('search') || request('status'))
                    <div>
                        <a href="{{ route('seller.shipments') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                            <i class="fas fa-undo mr-2"></i> Reset
                        </a>
                    </div>
                @endif
            </form>

            <!-- Shipments Table -->
            @if($shipments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Reference</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Tracking</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Carrier</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Order #</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shipments as $shipment)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-mono text-sm">{{ $shipment->reference_number ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 font-mono text-sm">{{ $shipment->tracking_number ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">{{ $shipment->carrier ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">#{{ $shipment->order_id ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $shipment->status_badge ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $shipment->status_label ?? ucfirst($shipment->status ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm">{{ $shipment->created_at ? $shipment->created_at->format('M d, Y') : 'N/A' }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex gap-2">
                                            <a href="{{ route('seller.shipments.show', $shipment->id) }}" 
                                               class="text-blue-600 hover:text-blue-800" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('seller.shipments.label', $shipment->id) }}" 
                                               class="text-teal-600 hover:text-teal-800" title="Print Label">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            @if($shipment->status !== 'delivered' && $shipment->status !== 'cancelled')
                                                <a href="{{ route('seller.shipments.cancel', $shipment->id) }}" 
                                                   onclick="return confirm('Cancel this shipment?')"
                                                   class="text-red-600 hover:text-red-800" title="Cancel">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $shipments->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-truck text-5xl text-gray-300 mb-4 block"></i>
                    <h3 class="text-lg font-semibold text-gray-700">No Shipments Found</h3>
                    <p class="text-gray-500 mt-2">You haven't created any shipments yet.</p>
                    <a href="{{ route('seller.shipments.create') }}" class="inline-block mt-4 bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-plus mr-2"></i> Create Your First Shipment
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection