@extends('layouts.app')

@section('title', 'E-commerce Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl shadow-lg p-6 mb-6 text-white">
        <h1 class="text-2xl font-bold">🛒 E-commerce Dashboard</h1>
        <p class="text-purple-100 mt-1">Manage e-commerce orders, sellers, and deliveries</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Total Orders</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_orders'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">Pending Orders</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pending_orders'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Completed Orders</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($stats['completed_orders'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-sm text-gray-500">Total Revenue</p>
            <p class="text-2xl font-bold text-purple-600">Rs. {{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Secondary Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-pink-50 rounded-lg p-3">
            <p class="text-xs text-gray-600">Total Sellers</p>
            <p class="text-xl font-bold text-pink-600">{{ number_format($stats['total_sellers'] ?? 0) }}</p>
        </div>
        <div class="bg-green-50 rounded-lg p-3">
            <p class="text-xs text-gray-600">Active Sellers</p>
            <p class="text-xl font-bold text-green-600">{{ number_format($stats['active_sellers'] ?? 0) }}</p>
        </div>
        <div class="bg-blue-50 rounded-lg p-3">
            <p class="text-xs text-gray-600">Total Products</p>
            <p class="text-xl font-bold text-blue-600">{{ number_format($stats['total_products'] ?? 0) }}</p>
        </div>
        <div class="bg-purple-50 rounded-lg p-3">
            <p class="text-xs text-gray-600">Active Products</p>
            <p class="text-xl font-bold text-purple-600">{{ number_format($stats['active_products'] ?? 0) }}</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('domestic.ecommerce.orders') }}" class="bg-white hover:bg-gray-50 rounded-xl shadow-sm p-4 text-center border border-gray-200 transition">
            <i class="fas fa-shopping-cart text-2xl text-blue-600 block mb-2"></i>
            <span class="text-sm font-medium text-gray-700">All Orders</span>
        </a>
        <a href="{{ route('domestic.ecommerce.sellers') }}" class="bg-white hover:bg-gray-50 rounded-xl shadow-sm p-4 text-center border border-gray-200 transition">
            <i class="fas fa-store text-2xl text-pink-600 block mb-2"></i>
            <span class="text-sm font-medium text-gray-700">Manage Sellers</span>
        </a>
        <a href="{{ route('domestic.ecommerce.products') }}" class="bg-white hover:bg-gray-50 rounded-xl shadow-sm p-4 text-center border border-gray-200 transition">
            <i class="fas fa-box text-2xl text-purple-600 block mb-2"></i>
            <span class="text-sm font-medium text-gray-700">Products</span>
        </a>
        <a href="{{ route('domestic.ecommerce.analytics') }}" class="bg-white hover:bg-gray-50 rounded-xl shadow-sm p-4 text-center border border-gray-200 transition">
            <i class="fas fa-chart-bar text-2xl text-green-600 block mb-2"></i>
            <span class="text-sm font-medium text-gray-700">Analytics</span>
        </a>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-xl shadow-sm mb-6">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Recent Orders</h3>
            <a href="{{ route('domestic.ecommerce.orders') }}" class="text-sm text-purple-600 hover:underline">View All</a>
        </div>
        <div class="p-4">
            @if($recentOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Order #</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Customer</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Seller</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Amount</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3 text-sm font-mono">#{{ $order->order_number ?? $order->id }}</td>
                                    <td class="py-2 px-3">{{ $order->customer_name ?? $order->client->name ?? 'N/A' }}</td>
                                    <td class="py-2 px-3">{{ $order->seller->name ?? 'N/A' }}</td>
                                    <td class="py-2 px-3 font-medium">Rs. {{ number_format($order->total_amount, 2) }}</td>
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                               'bg-blue-100 text-blue-800')) }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-sm">{{ $order->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-shopping-cart text-4xl block mb-2"></i>
                    <p>No orders found</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Top Products -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Top Selling Products</h3>
        </div>
        <div class="p-4">
            @if($topProducts->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    @foreach($topProducts as $product)
                        <div class="border rounded-lg p-3 text-center hover:shadow-md transition">
                            <div class="w-16 h-16 bg-gray-100 rounded-full mx-auto flex items-center justify-center">
                                <i class="fas fa-box text-2xl text-gray-400"></i>
                            </div>
                            <p class="font-medium text-sm mt-2 truncate">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500">{{ $product->orders_count }} orders</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-gray-500">No products found</div>
            @endif
        </div>
    </div>
</div>
@endsection