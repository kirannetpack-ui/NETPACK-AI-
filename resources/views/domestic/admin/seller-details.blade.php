@extends('layouts.app')

@section('title', 'Seller Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Seller Details</h1>
                <p class="text-sm text-gray-500 mt-1">View seller information</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('domestic.sellers') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Back
                </a>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="font-medium">{{ $seller->name }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Store Name</p>
                    <p class="font-medium">{{ $seller->business_name ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium">{{ $seller->email }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="font-medium">{{ $seller->phone ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Status</p>
                    <p class="font-medium">
                        @if($seller->verification_status === 'approved')
                            <span class="text-green-600">Active</span>
                        @elseif($seller->verification_status === 'pending')
                            <span class="text-yellow-600">Pending</span>
                        @else
                            <span class="text-red-600">{{ ucfirst($seller->verification_status) }}</span>
                        @endif
                    </p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Products</p>
                    <p class="font-medium">{{ number_format($stats['total_products'] ?? 0) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Active Products</p>
                    <p class="font-medium">{{ number_format($stats['active_products'] ?? 0) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Orders</p>
                    <p class="font-medium">{{ number_format($stats['total_orders'] ?? 0) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Pending Orders</p>
                    <p class="font-medium">{{ number_format($stats['pending_orders'] ?? 0) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Completed Orders</p>
                    <p class="font-medium">{{ number_format($stats['completed_orders'] ?? 0) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Revenue</p>
                    <p class="font-medium">Rs. {{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection