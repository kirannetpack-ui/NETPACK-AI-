{{-- resources/views/partner/deliveries.blade.php --}}
@extends('layouts.partner')

@section('title', 'Deliveries')

@section('content')
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50">
        <h1 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
            <i class="fas fa-truck text-teal-600"></i>
            <span>All Deliveries</span>
        </h1>
    </div>
    
    <div class="p-6">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Order Ref</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Pickup → Delivery</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($deliveries as $delivery)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-mono">#{{ $delivery->id }}</td>
                        <td class="px-4 py-3 text-sm">{{ $delivery->order_reference ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $delivery->customer_name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="text-xs">{{ $delivery->pickup_district }}</span>
                            <i class="fas fa-arrow-right mx-1 text-gray-400 text-xs"></i>
                            <span class="text-xs">{{ $delivery->delivery_district }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($delivery->status == 'delivered') bg-green-100 text-green-800
                                @elseif($delivery->status == 'out_for_delivery') bg-blue-100 text-blue-800
                                @elseif($delivery->status == 'arrived_at_partner') bg-purple-100 text-purple-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $delivery->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No deliveries found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $deliveries->links() }}
        </div>
    </div>
</div>
@endsection