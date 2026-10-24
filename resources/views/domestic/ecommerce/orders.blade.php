@extends('layouts.app')

@section('title', 'E-commerce Orders')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">🛒 E-commerce Orders</h1>
                <p class="text-sm text-gray-500 mt-1">Manage all e-commerce orders</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('domestic.ecommerce.export-orders') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-file-export mr-2"></i> Export
                </a>
                <a href="{{ route('domestic.ecommerce.analytics') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-chart-bar mr-2"></i> Analytics
                </a>
            </div>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filters -->
            <form method="GET" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select name="status" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Seller</label>
                    <select name="seller_id" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">All Sellers</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->business_name ?? $seller->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="md:col-span-4">
                    <div class="flex gap-2">
                        <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                            <i class="fas fa-filter mr-2"></i> Filter
                        </button>
                        <a href="{{ route('domestic.ecommerce.orders') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                            <i class="fas fa-undo mr-2"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <!-- Orders Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Order #</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Customer</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Seller</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Items</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Amount</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Date</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-mono text-sm">#{{ $order->order_number ?? $order->id }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-medium">{{ $order->customer_name ?? $order->client->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->customer_phone ?? $order->client->phone ?? '' }}</div>
                                </td>
                                <td class="py-3 px-4">{{ $order->seller->business_name ?? $order->seller->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $order->items->count() }}</td>
                                <td class="py-3 px-4 font-medium">Rs. {{ number_format($order->total_amount, 2) }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium 
                                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                           'bg-blue-100 text-blue-800')) }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-sm">{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('domestic.ecommerce.orders.show', $order->id) }}" 
                                       class="text-purple-600 hover:text-purple-800" title="View Order">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-shopping-cart text-4xl block mb-2"></i>
                                    No orders found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection