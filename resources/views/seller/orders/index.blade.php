@extends('layouts.seller')

@section('title', 'Orders')
@section('page-title', 'My Orders')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">My Orders</h2>
            <div class="flex gap-2">
                <a href="{{ route('seller.orders.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                    <i class="fas fa-plus mr-2"></i> Create Order
                </a>
                <a href="{{ route('seller.orders.export') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-download mr-2"></i> Export
                </a>
            </div>
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

            <!-- Orders Table -->
            @if($orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Order #</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Customer</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Amount</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Payment</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-mono text-sm">#{{ $order->order_number ?? $order->id }}</td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium">{{ $order->customer_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $order->customer_phone }}</div>
                                    </td>
                                    <td class="py-3 px-4 font-medium">Rs. {{ number_format($order->total_amount, 2) }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $order->status_badge }}">
                                            {{ $order->status_label ?? ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($order->payment_status ?? 'Pending') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm">{{ $order->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('seller.orders.show', $order->id) }}" 
                                           class="text-blue-600 hover:text-blue-800" title="View Order">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-shopping-cart text-5xl text-gray-300 mb-4 block"></i>
                    <h3 class="text-lg font-semibold text-gray-700">No Orders Found</h3>
                    <p class="text-gray-500 mt-2">You haven't created any orders yet.</p>
                    <a href="{{ route('seller.orders.create') }}" class="inline-block mt-4 bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-plus mr-2"></i> Create Your First Order
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection