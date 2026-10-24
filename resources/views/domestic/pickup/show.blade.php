{{-- resources/views/domestic/pickup/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Pickup Request #' . $pickupRequest->id)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-teal-600 to-emerald-600 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-truck"></i>
                        <span>Pickup Request #{{ $pickupRequest->id }}</span>
                    </h1>
                    <p class="text-teal-100 text-xs mt-1">View and track your pickup request details</p>
                </div>
                <a href="{{ route('domestic.pickup.my-requests') }}" class="bg-white text-teal-600 px-4 py-2 rounded-xl text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Status Timeline -->
            <div class="mb-8">
                <div class="flex justify-between mb-2">
                    @php
                        $statuses = ['pending', 'assigned', 'picked_up', 'in_transit', 'delivered'];
                        $currentStatusIndex = array_search($pickupRequest->status, $statuses);
                    @endphp
                    @foreach($statuses as $index => $status)
                        <div class="text-center flex-1">
                            <div class="w-8 h-8 rounded-full mx-auto mb-2 flex items-center justify-center
                                {{ $index <= $currentStatusIndex ? 'bg-teal-600 text-white' : 'bg-gray-200 text-gray-400' }}">
                                @if($index < $currentStatusIndex)
                                    <i class="fas fa-check"></i>
                                @elseif($index == $currentStatusIndex)
                                    <i class="fas fa-circle"></i>
                                @else
                                    <i class="fas fa-circle"></i>
                                @endif
                            </div>
                            <p class="text-xs {{ $index <= $currentStatusIndex ? 'text-teal-600 font-medium' : 'text-gray-400' }}">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </p>
                        </div>
                        @if($index < count($statuses) - 1)
                            <div class="flex-1 h-0.5 mt-4 {{ $index < $currentStatusIndex ? 'bg-teal-600' : 'bg-gray-200' }}"></div>
                        @endif
                    @endforeach
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pickup Details -->
                <div class="border rounded-xl p-4">
                    <h3 class="font-medium text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-green-600"></i>
                        <span>Pickup Details</span>
                    </h3>
                    <div class="space-y-2 text-sm">
                        <p><strong>Address:</strong> {{ $pickupRequest->pickup_address }}</p>
                        <p><strong>Ward:</strong> {{ $pickupRequest->pickup_ward_no }}</p>
                        <p><strong>Municipality:</strong> {{ $pickupRequest->pickup_municipality }}</p>
                        <p><strong>District:</strong> {{ $pickupRequest->pickup_district }}</p>
                        <p><strong>Province:</strong> {{ $pickupRequest->pickup_province }}</p>
                    </div>
                </div>
                
                <!-- Delivery Details -->
                <div class="border rounded-xl p-4">
                    <h3 class="font-medium text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-flag-checkered text-red-600"></i>
                        <span>Delivery Details</span>
                    </h3>
                    <div class="space-y-2 text-sm">
                        <p><strong>Address:</strong> {{ $pickupRequest->delivery_address }}</p>
                        <p><strong>Ward:</strong> {{ $pickupRequest->delivery_ward_no }}</p>
                        <p><strong>Municipality:</strong> {{ $pickupRequest->delivery_municipality }}</p>
                        <p><strong>District:</strong> {{ $pickupRequest->delivery_district }}</p>
                        <p><strong>Province:</strong> {{ $pickupRequest->delivery_province }}</p>
                    </div>
                </div>
                
                <!-- Package Details -->
                <div class="border rounded-xl p-4">
                    <h3 class="font-medium text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-box text-teal-600"></i>
                        <span>Package Details</span>
                    </h3>
                    <div class="space-y-2 text-sm">
                        <p><strong>Items:</strong> {{ $pickupRequest->items_description }}</p>
                        <p><strong>Estimated Weight:</strong> {{ $pickupRequest->estimated_weight_kg }} kg</p>
                        @if($pickupRequest->actual_weight_kg)
                        <p><strong>Actual Weight:</strong> {{ $pickupRequest->actual_weight_kg }} kg</p>
                        @endif
                        <p><strong>Service Tier:</strong> {{ ucfirst($pickupRequest->service_tier) }}</p>
                    </div>
                </div>
                
                <!-- Timeline Details -->
                <div class="border rounded-xl p-4">
                    <h3 class="font-medium text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-clock text-teal-600"></i>
                        <span>Timeline</span>
                    </h3>
                    <div class="space-y-2 text-sm">
                        <p><strong>Requested:</strong> {{ $pickupRequest->created_at->format('M d, Y h:i A') }}</p>
                        <p><strong>Scheduled Pickup:</strong> {{ \Carbon\Carbon::parse($pickupRequest->scheduled_pickup_time)->format('M d, Y h:i A') }}</p>
                        @if($pickupRequest->picked_up_at)
                        <p><strong>Picked Up:</strong> {{ \Carbon\Carbon::parse($pickupRequest->picked_up_at)->format('M d, Y h:i A') }}</p>
                        @endif
                        @if($pickupRequest->delivered_at)
                        <p><strong>Delivered:</strong> {{ \Carbon\Carbon::parse($pickupRequest->delivered_at)->format('M d, Y h:i A') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Price Summary -->
            @if($pickupRequest->calculated_price)
            <div class="mt-6 bg-gray-50 rounded-xl p-4">
                <div class="flex justify-between items-center">
                    <span class="font-medium">Total Cost</span>
                    <span class="text-2xl font-bold text-teal-600">रू {{ number_format($pickupRequest->calculated_price, 2) }}</span>
                </div>
            </div>
            @endif
            
            <!-- Status Notes -->
            @if($pickupRequest->status_notes)
            <div class="mt-4 bg-blue-50 rounded-xl p-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    {{ $pickupRequest->status_notes }}
                </p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection