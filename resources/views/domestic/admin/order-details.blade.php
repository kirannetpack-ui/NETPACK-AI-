@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Order Details</h1>
                <p class="text-sm text-gray-500 mt-1">View order #{{ $order->id }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('domestic.orders') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Back
                </a>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Order Status</p>
                    <p class="font-medium">
                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 
                               ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                               ($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                               'bg-blue-100 text-blue-800')) }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Amount</p>
                    <p class="font-medium text-lg">Rs. {{ number_format($order->total_amount, 2) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Created At</p>
                    <p class="font-medium">{{ $order->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>

            <!-- Update Status -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Update Status</h3>
                <form method="POST" action="{{ route('domestic.orders.update-status', $order->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <select name="status" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <input type="text" name="notes" placeholder="Admin notes" 
                                   class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-save mr-2"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>

            <!-- Order Items -->
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Order Items</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Product</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Quantity</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Price</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3">{{ $item->product->name ?? 'N/A' }}</td>
                                    <td class="py-2 px-3">{{ $item->quantity }}</td>
                                    <td class="py-2 px-3">Rs. {{ number_format($item->price, 2) }}</td>
                                    <td class="py-2 px-3">Rs. {{ number_format($item->price * $item->quantity, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-gray-500">No items found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection