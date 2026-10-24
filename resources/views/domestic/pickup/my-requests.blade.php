{{-- resources/views/domestic/pickup/my-requests.blade.php --}}
@extends('layouts.app')

@section('title', 'My Pickup Requests')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-teal-600 to-emerald-600 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-truck-fast"></i>
                        <span>My Pickup Requests</span>
                    </h1>
                    <p class="text-teal-100 text-xs mt-1">Track and manage your domestic pickup requests</p>
                </div>
                <a href="{{ route('domestic.pickup.create') }}" class="bg-white text-teal-600 px-4 py-2 rounded-xl text-sm font-medium hover:bg-gray-100">
                    <i class="fas fa-plus mr-2"></i> New Request
                </a>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Status Filter Tabs -->
            <div class="flex flex-wrap gap-2 mb-6 border-b pb-3">
                <a href="{{ route('domestic.pickup.my-requests') }}" class="px-4 py-2 rounded-lg text-sm {{ !request('status') ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                    All
                </a>
                <a href="{{ route('domestic.pickup.my-requests', ['status' => 'pending']) }}" class="px-4 py-2 rounded-lg text-sm {{ request('status') == 'pending' ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                    Pending
                </a>
                <a href="{{ route('domestic.pickup.my-requests', ['status' => 'assigned']) }}" class="px-4 py-2 rounded-lg text-sm {{ request('status') == 'assigned' ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                    Assigned
                </a>
                <a href="{{ route('domestic.pickup.my-requests', ['status' => 'picked_up']) }}" class="px-4 py-2 rounded-lg text-sm {{ request('status') == 'picked_up' ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                    Picked Up
                </a>
                <a href="{{ route('domestic.pickup.my-requests', ['status' => 'in_transit']) }}" class="px-4 py-2 rounded-lg text-sm {{ request('status') == 'in_transit' ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                    In Transit
                </a>
                <a href="{{ route('domestic.pickup.my-requests', ['status' => 'delivered']) }}" class="px-4 py-2 rounded-lg text-sm {{ request('status') == 'delivered' ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                    Delivered
                </a>
            </div>
            
            <!-- Requests List -->
            @forelse($requests as $request)
            <div class="border rounded-xl p-4 mb-4 hover:shadow-md transition">
                <div class="flex flex-wrap justify-between items-start gap-4">
                    <!-- Request Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">#{{ $request->id }}</span>
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($request->status == 'delivered') bg-green-100 text-green-800
                                @elseif($request->status == 'picked_up') bg-blue-100 text-blue-800
                                @elseif($request->status == 'assigned') bg-purple-100 text-purple-800
                                @elseif($request->status == 'pending') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                            </span>
                            <span class="px-2 py-1 bg-teal-100 text-teal-800 text-xs rounded-full">
                                <i class="fas fa-bolt mr-1"></i>{{ ucfirst($request->service_tier) }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-map-marker-alt text-green-600 mt-0.5"></i>
                                <div>
                                    <p class="text-gray-500 text-xs">Pickup</p>
                                    <p class="text-gray-800">{{ $request->pickup_address }}, Ward {{ $request->pickup_ward_no }}, {{ $request->pickup_municipality }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <i class="fas fa-flag-checkered text-red-600 mt-0.5"></i>
                                <div>
                                    <p class="text-gray-500 text-xs">Delivery</p>
                                    <p class="text-gray-800">{{ $request->delivery_address }}, Ward {{ $request->delivery_ward_no }}, {{ $request->delivery_municipality }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap gap-4 mt-3 text-sm text-gray-500">
                            <span><i class="fas fa-weight-hanging mr-1"></i> {{ $request->estimated_weight_kg }} kg</span>
                            <span><i class="fas fa-calendar mr-1"></i> Scheduled: {{ \Carbon\Carbon::parse($request->scheduled_pickup_time)->format('M d, h:i A') }}</span>
                            @if($request->calculated_price)
                            <span><i class="fas fa-rupee-sign mr-1"></i> रू {{ number_format($request->calculated_price, 2) }}</span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <a href="{{ route('domestic.pickup.show', $request) }}" class="text-teal-600 hover:text-teal-800 px-3 py-1">
                            <i class="fas fa-eye"></i> View
                        </a>
                        @if($request->status == 'pending')
                        <button onclick="cancelRequest({{ $request->id }})" class="text-red-600 hover:text-red-800 px-3 py-1">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-12">
                <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">No pickup requests found</p>
                <a href="{{ route('domestic.pickup.create') }}" class="inline-block mt-3 text-teal-600 hover:text-teal-700">
                    Create your first pickup request →
                </a>
            </div>
            @endforelse
            
            <!-- Pagination -->
            <div class="mt-6">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>

<script>
function cancelRequest(id) {
    if (confirm('Are you sure you want to cancel this request?')) {
        fetch(`/domestic/pickup/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => location.reload());
    }
}
</script>
@endsection