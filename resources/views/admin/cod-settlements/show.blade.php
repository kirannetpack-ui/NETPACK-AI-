@extends('layouts.app')

@section('title', 'COD Settlement Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">COD Settlement #{{ $settlement->id }}</h1>
                <p class="text-sm text-gray-500 mt-1">Reference: {{ $settlement->settlement_reference ?? 'N/A' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.cod-settlements.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>
        </div>

        <div class="p-6">
            <!-- Status Banner -->
            <div class="mb-6 p-4 rounded-lg {{ $settlement->settlement_status === 'completed' ? 'bg-green-50 border-green-200' : ($settlement->settlement_status === 'pending' ? 'bg-yellow-50 border-yellow-200' : 'bg-blue-50 border-blue-200') }} border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Current Status</p>
                        <p class="text-xl font-bold">
                            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $settlement->status_badge }}">
                                {{ $settlement->status_label }}
                            </span>
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Settlement Date</p>
                        <p class="font-medium">{{ $settlement->settlement_date ? $settlement->settlement_date->format('M d, Y H:i') : 'Not settled yet' }}</p>
                    </div>
                </div>
            </div>

            <!-- Settlement Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="border rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase">Order</p>
                    <p class="font-semibold">#{{ $settlement->order->order_number ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase">Delivery</p>
                    <p class="font-semibold">#{{ $settlement->delivery_id ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase">Seller</p>
                    <p class="font-semibold">{{ $settlement->seller->name ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase">Rider</p>
                    <p class="font-semibold">{{ $settlement->rider->name ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase">Verified By</p>
                    <p class="font-semibold">{{ $settlement->verifiedBy->name ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase">Collected At</p>
                    <p class="font-semibold">{{ $settlement->collected_at ? $settlement->collected_at->format('M d, Y H:i') : 'N/A' }}</p>
                </div>
            </div>

            <!-- Amount Details -->
            <div class="border rounded-lg p-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">💰 Amount Details</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">COD Amount</p>
                        <p class="text-xl font-bold text-blue-600">Rs. {{ number_format($settlement->cod_amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Delivery Charge</p>
                        <p class="text-xl font-bold text-purple-600">Rs. {{ number_format($settlement->delivery_charge, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Admin Margin</p>
                        <p class="text-xl font-bold text-orange-600">Rs. {{ number_format($settlement->admin_margin, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Seller Amount</p>
                        <p class="text-xl font-bold text-green-600">Rs. {{ number_format($settlement->seller_amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Rider Amount</p>
                        <p class="text-xl font-bold text-teal-600">Rs. {{ number_format($settlement->rider_amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Margin Amount</p>
                        <p class="text-xl font-bold text-red-600">Rs. {{ number_format($settlement->margin_amount, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Remarks -->
            @if($settlement->remarks)
                <div class="border rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">📝 Remarks</h3>
                    <p class="text-gray-600">{{ $settlement->remarks }}</p>
                </div>
            @endif

            <!-- Update Status Form -->
            @if($settlement->settlement_status !== 'completed')
                <div class="border-t pt-4">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Update Status</h3>
                    <form method="POST" action="{{ route('admin.cod-settlements.update-status', $settlement->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Status</label>
                                <select name="status" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="pending" {{ $settlement->settlement_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $settlement->settlement_status === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="completed">Completed</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-1">Remarks</label>
                                <input type="text" name="remarks" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                                <i class="fas fa-save mr-2"></i> Update Status
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection