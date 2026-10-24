@extends('layouts.app')

@section('title', 'Pickup Requests')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Pickup Requests</h1>
            <p class="text-sm text-gray-500 mt-1">Manage domestic pickup requests</p>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filters -->
            <form method="GET" class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select name="status" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">ID</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Customer</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Pickup Address</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Service</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pickups as $pickup)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4">#{{ $pickup->id }}</td>
                                <td class="py-3 px-4">{{ $pickup->seller->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm">{{ $pickup->pickup_address }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium 
                                        {{ $pickup->service_tier === 'flash' ? 'bg-red-100 text-red-800' : 
                                           ($pickup->service_tier === 'same_day' ? 'bg-orange-100 text-orange-800' : 
                                           ($pickup->service_tier === 'standard' ? 'bg-blue-100 text-blue-800' : 
                                           'bg-purple-100 text-purple-800')) }}">
                                        {{ strtoupper($pickup->service_tier ?? 'Standard') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium 
                                        {{ $pickup->status === 'delivered' ? 'bg-green-100 text-green-800' : 
                                           ($pickup->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($pickup->status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                           'bg-blue-100 text-blue-800')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $pickup->status)) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <a href="#" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-box-open text-4xl block mb-2"></i>
                                    No pickup requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $pickups->links() }}
            </div>
        </div>
    </div>
</div>
@endsection