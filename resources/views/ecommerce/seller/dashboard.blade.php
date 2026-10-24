{{-- resources/views/ecommerce/seller/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'E-commerce Dashboard')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl shadow-lg p-6 mb-8 text-white">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold flex items-center gap-2">
                    <i class="fas fa-shopping-cart"></i>
                    <span>E-commerce Dashboard</span>
                </h1>
                <p class="text-purple-100 mt-1">Manage your online orders from multiple platforms</p>
            </div>
            <a href="{{ route('ecommerce.seller.create') }}" class="bg-white text-purple-600 px-4 py-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-plus mr-2"></i> New Order
            </a>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_orders'] }}</p>
                </div>
                <i class="fas fa-shopping-bag text-3xl text-purple-500"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending_orders'] }}</p>
                </div>
                <i class="fas fa-clock text-3xl text-yellow-500"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Delivered</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['delivered_orders'] }}</p>
                </div>
                <i class="fas fa-check-circle text-3xl text-green-500"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Earnings</p>
                    <p class="text-2xl font-bold text-teal-600">रू {{ number_format($stats['total_earnings'], 2) }}</p>
                </div>
                <i class="fas fa-wallet text-3xl text-teal-500"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending COD</p>
                    <p class="text-2xl font-bold text-orange-600">रू {{ number_format($stats['pending_cod'], 2) }}</p>
                </div>
                <i class="fas fa-money-bill-wave text-3xl text-orange-500"></i>
            </div>
        </div>
    </div>
    
    <!-- Platform Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-1">
            <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                <i class="fas fa-chart-pie text-purple-600"></i>
                <span>Sales by Platform</span>
            </h3>
            <div class="space-y-3">
                @foreach($platforms as $platform)
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        @switch($platform->platform)
                            @case('daraz')
                                <i class="fab fa-daraz text-orange-500"></i>
                                @break
                            @case('hamrobazar')
                                <i class="fas fa-exchange-alt text-blue-500"></i>
                                @break
                            @case('sastodeal')
                                <i class="fas fa-tag text-green-500"></i>
                                @break
                            @case('facebook')
                                <i class="fab fa-facebook text-indigo-500"></i>
                                @break
                            @default
                                <i class="fas fa-store text-gray-500"></i>
                        @endswitch
                        <span class="capitalize">{{ $platform->platform }}</span>
                    </div>
                    <span class="font-semibold">{{ $platform->total }} orders</span>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-2">
            <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                <i class="fas fa-bolt text-purple-600"></i>
                <span>Quick Actions</span>
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('ecommerce.seller.create') }}" class="bg-purple-600 text-white text-center py-3 rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-plus mr-2"></i> Create New Order
                </a>
                <a href="{{ route('ecommerce.seller.orders') }}" class="bg-gray-200 text-gray-700 text-center py-3 rounded-lg hover:bg-gray-300 transition">
                    <i class="fas fa-list mr-2"></i> View All Orders
                </a>
                <a href="{{ route('domestic.pickup.create') }}" class="bg-teal-600 text-white text-center py-3 rounded-lg hover:bg-teal-700 transition">
                    <i class="fas fa-truck mr-2"></i> Request Pickup
                </a>
                <a href="{{ route('seller.dashboard') }}" class="bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-chart-line mr-2"></i> View Analytics
                </a>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i class="fas fa-history text-purple-600"></i>
                <span>Recent Orders</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Ref</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Platform</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentOrders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-mono text-sm">{{ $order->order_reference }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs {{ $order->platform_badge }}">
                                <i class="{{ $order->platform_icon }}"></i>
                                {{ ucfirst($order->platform) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium">{{ $order->customer_name }}</div>
                            <div class="text-xs text-gray-500">{{ $order->customer_phone }}</div>
                        </td>
                        <td class="px-6 py-4 font-medium">रू {{ number_format($order->cod_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($order->status == 'delivered') bg-green-100 text-green-800
                                @elseif($order->status == 'picked_up') bg-blue-100 text-blue-800
                                @elseif($order->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($order->payment_status == 'paid') bg-green-100 text-green-800
                                @elseif($order->payment_status == 'cod') bg-orange-100 text-orange-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ strtoupper($order->payment_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('ecommerce.seller.order.show', $order) }}" class="text-teal-600 hover:text-teal-800">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button onclick="printLabel({{ $order->id }})" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-print"></i>
                                </button>
                                @if(in_array($order->status, ['pending', 'assigned']))
                                <button onclick="cancelOrder({{ $order->id }})" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            No e-commerce orders yet
                            <div class="mt-2">
                                <a href="{{ route('ecommerce.seller.create') }}" class="text-teal-600">Create your first order →</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function printLabel(orderId) {
    window.open(`/seller/ecommerce/orders/${orderId}/print`, '_blank');
}

function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        fetch(`/seller/ecommerce/orders/${orderId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => location.reload());
    }
}
</script>
@endsection