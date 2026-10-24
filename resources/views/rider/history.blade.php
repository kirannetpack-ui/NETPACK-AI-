@extends('layouts.app')

@section('title', 'Delivery History')
@section('page-title', 'Delivery History')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">📋 Delivery History</h1>
            <p class="text-sm text-gray-500 mt-1">View all your completed deliveries</p>
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

            <!-- Stats Summary -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total Deliveries</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total'] ?? 0) }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($stats['completed'] ?? 0) }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">In Progress</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['in_progress'] ?? 0) }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total Earnings</p>
                    <p class="text-2xl font-bold text-purple-600">Rs. {{ number_format($stats['earnings'] ?? 0, 2) }}</p>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" class="flex flex-wrap gap-3 mb-6">
                <div>
                    <select name="status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">All Status</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="out_for_delivery" {{ request('status') === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" 
                           class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" 
                           class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                </div>
                @if(request('status') || request('from_date') || request('to_date'))
                    <div>
                        <a href="{{ route('rider.history') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                            <i class="fas fa-undo mr-2"></i> Reset
                        </a>
                    </div>
                @endif
            </form>

            <!-- History Table -->
            @if($deliveries->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Delivery #</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Order</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Recipient</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Address</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Fee</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deliveries as $delivery)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-mono text-sm">#{{ $delivery->id }}</td>
                                    <td class="py-3 px-4">#{{ $delivery->order->order_number ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">{{ $delivery->recipient_name }}</td>
                                    <td class="py-3 px-4 text-sm">{{ Str::limit($delivery->address, 30) }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $delivery->status_badge }}">
                                            {{ $delivery->status_label }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-medium text-teal-600">Rs. {{ number_format($delivery->delivery_fee, 2) }}</td>
                                    <td class="py-3 px-4 text-sm">{{ $delivery->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('rider.orders.track', $delivery->tracking_number) }}" 
                                           class="text-blue-600 hover:text-blue-800" title="Track">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $deliveries->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-history text-5xl text-gray-300 mb-4 block"></i>
                    <h3 class="text-lg font-semibold text-gray-700">No Delivery History</h3>
                    <p class="text-gray-500 mt-2">You haven't completed any deliveries yet.</p>
                    <a href="{{ route('rider.orders.available') }}" class="inline-block mt-4 bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-search mr-2"></i> Find Orders
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection