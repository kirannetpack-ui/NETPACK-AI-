@extends('layouts.app')

@section('title', 'Rider Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $rider->name }}</h1>
                <p class="text-sm text-gray-500">{{ $rider->email }}</p>
                <div class="flex gap-4 mt-2">
                    <span class="status-badge {{ $rider->is_online ? 'online' : 'offline' }}">
                        {{ $rider->is_online ? '🟢 Online' : '🔴 Offline' }}
                    </span>
                    <span class="text-sm text-gray-500">Phone: {{ $rider->phone ?? 'N/A' }}</span>
                    <span class="text-sm text-gray-500">Vehicle: {{ $rider->vehicle_type ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.riders.dashboard') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Total Deliveries</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_deliveries'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Today's Earnings</p>
            <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($stats['today_earnings'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-sm text-gray-500">Total Earnings</p>
            <p class="text-2xl font-bold text-purple-600">Rs. {{ number_format($stats['total_earnings'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">Rating</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['rating'] ?? 0, 1) }} ★</p>
        </div>
    </div>

    <!-- More Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-sm text-gray-500">Active Deliveries</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['active_deliveries'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-sm text-gray-500">Deposit Balance</p>
            <p class="text-2xl font-bold text-teal-600">Rs. {{ number_format($stats['deposit_balance'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-sm text-gray-500">Week Earnings</p>
            <p class="text-2xl font-bold text-purple-600">Rs. {{ number_format($stats['week_earnings'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-sm text-gray-500">Month Earnings</p>
            <p class="text-2xl font-bold text-orange-600">Rs. {{ number_format($stats['month_earnings'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Delivery History -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">📋 Delivery History</h3>
        </div>
        <div class="p-4">
            @if($deliveries->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Delivery #</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Order</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Customer</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Fee</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deliveries as $delivery)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3 font-mono text-sm">#{{ $delivery->id }}</td>
                                    <td class="py-2 px-3">#{{ $delivery->order->order_number ?? 'N/A' }}</td>
                                    <td class="py-2 px-3">{{ $delivery->recipient_name ?? 'N/A' }}</td>
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $delivery->status_badge }}">
                                            {{ $delivery->status_label }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3">Rs. {{ number_format($delivery->delivery_fee, 2) }}</td>
                                    <td class="py-2 px-3 text-sm">{{ $delivery->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $deliveries->links() }}
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>No delivery history</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Deposit History -->
    <div class="mt-6 bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">💰 Deposit History</h3>
        </div>
        <div class="p-4">
            @if($deposits->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Date</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Type</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Amount</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Balance</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deposits as $deposit)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3 text-sm">{{ $deposit->created_at->format('M d, Y H:i') }}</td>
                                    <td class="py-2 px-3">{{ $deposit->type_label }}</td>
                                    <td class="py-2 px-3 font-medium {{ $deposit->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $deposit->amount > 0 ? '+' : '' }}Rs. {{ number_format($deposit->amount, 2) }}
                                    </td>
                                    <td class="py-2 px-3">Rs. {{ number_format($deposit->balance, 2) }}</td>
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $deposit->status_badge }}">
                                            {{ ucfirst($deposit->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>No deposit history</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection