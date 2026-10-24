{{-- resources/views/partner/deliveries/index.blade.php --}}
@extends('layouts.partner')

@section('title', 'Deliveries')
@section('page-title', 'All Deliveries')

@section('content')
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50">
        <h1 class="text-xl font-semibold text-gray-800">My Deliveries</h1>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 p-6">
        <div class="text-center p-3 bg-gray-50 rounded-lg">
            <p class="text-2xl font-bold text-teal-600">{{ $stats['total'] }}</p>
            <p class="text-sm text-gray-500">Total</p>
        </div>
        <div class="text-center p-3 bg-yellow-50 rounded-lg">
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
            <p class="text-sm text-gray-500">Pending</p>
        </div>
        <div class="text-center p-3 bg-blue-50 rounded-lg">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['in_transit'] }}</p>
            <p class="text-sm text-gray-500">In Transit</p>
        </div>
        <div class="text-center p-3 bg-green-50 rounded-lg">
            <p class="text-2xl font-bold text-green-600">{{ $stats['delivered'] }}</p>
            <p class="text-sm text-gray-500">Delivered</p>
        </div>
        <div class="text-center p-3 bg-red-50 rounded-lg">
            <p class="text-2xl font-bold text-red-600">{{ $stats['delayed'] }}</p>
            <p class="text-sm text-gray-500">Delayed</p>
        </div>
    </div>
    
    <!-- Deliveries Table -->
    <div class="p-6">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">Customer</th>
                    <th class="px-4 py-3 text-left">Service</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Created</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveries as $delivery)
                <tr class="border-t">
                    <td class="px-4 py-3">#{{ $delivery->id }}</td>
                    <td class="px-4 py-3">{{ $delivery->customer_name }}</td>
                    <td class="px-4 py-3">{{ ucfirst($delivery->service_tier) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($delivery->status == 'delivered') bg-green-100 text-green-800
                            @elseif($delivery->status == 'out_for_delivery') bg-blue-100 text-blue-800
                            @elseif($delivery->is_delayed) bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                            @if($delivery->is_delayed) (Delayed) @endif
                        </span>
                    </td>
                    <td class="px-4 py-3">{{ $delivery->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('partner.deliveries.show', $delivery) }}" class="text-teal-600">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $deliveries->links() }}
    </div>
</div>
@endsection