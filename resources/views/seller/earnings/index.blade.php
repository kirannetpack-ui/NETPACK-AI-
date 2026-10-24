@extends('layouts.seller')

@section('title', 'Earnings')
@section('page-title', 'My Earnings')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Total Earnings</p>
            <p class="text-2xl font-bold text-green-600">
                Rs. {{ number_format($totalEarnings ?? 0, 2) }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">This Month</p>
            <p class="text-2xl font-bold text-blue-600">
                Rs. {{ number_format($monthlyEarnings ?? 0, 2) }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-sm text-gray-500">This Week</p>
            <p class="text-2xl font-bold text-purple-600">
                Rs. {{ number_format($weeklyEarnings ?? 0, 2) }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">Total Orders</p>
            <p class="text-2xl font-bold text-yellow-600">
                {{ number_format($totalOrders ?? 0) }}
            </p>
        </div>
    </div>

    <!-- Earnings Chart -->
    <div class="bg-white rounded-xl shadow-sm mb-6">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Earnings Overview</h3>
            <p class="text-sm text-gray-500">Monthly earnings chart</p>
        </div>
        <div class="p-6">
            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                <div class="text-center text-gray-500">
                    <i class="fas fa-chart-bar text-4xl mb-2 block text-gray-300"></i>
                    <p>Earnings chart will appear here</p>
                    <p class="text-sm">(Data visualization coming soon)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings by Source -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Earnings by Source</h3>
            </div>
            <div class="p-6">
                @if(isset($earningsBySource) && $earningsBySource->count() > 0)
                    <div class="space-y-3">
                        @foreach($earningsBySource as $source)
                            <div class="flex items-center justify-between border-b pb-2">
                                <div>
                                    <span class="font-medium">{{ ucfirst($source->source ?? 'Other') }}</span>
                                    <span class="text-xs text-gray-500 ml-2">{{ $source->count ?? 0 }} transactions</span>
                                </div>
                                <span class="font-bold text-green-600">Rs. {{ number_format($source->total ?? 0, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-coins text-3xl mb-2 block text-gray-300"></i>
                        <p>No earnings data available</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Recent Transactions</h3>
            </div>
            <div class="p-6">
                @if(isset($recentTransactions) && $recentTransactions->count() > 0)
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach($recentTransactions as $transaction)
                            <div class="flex items-center justify-between border-b pb-2">
                                <div>
                                    <p class="font-medium">{{ $transaction->description ?? 'Transaction' }}</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->created_at ? $transaction->created_at->diffForHumans() : 'N/A' }}</p>
                                </div>
                                <span class="font-bold {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $transaction->type === 'credit' ? '+' : '-' }} Rs. {{ number_format($transaction->amount ?? 0, 2) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-history text-3xl mb-2 block text-gray-300"></i>
                        <p>No recent transactions</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Withdraw Button -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Withdraw Earnings</h3>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <p class="text-sm text-gray-500">Available Balance</p>
                    <p class="text-2xl font-bold text-teal-600">
                        Rs. {{ number_format($availableBalance ?? 0, 2) }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('seller.withdraw') }}" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-hand-holding-usd mr-2"></i> Withdraw Now
                    </a>
                    <a href="{{ route('seller.earnings.export') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition ml-2">
                        <i class="fas fa-file-export mr-2"></i> Export
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection