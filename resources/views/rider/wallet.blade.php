@extends('layouts.app')

@section('title', 'Wallet')
@section('page-title', 'My Wallet')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">💰 My Wallet</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your earnings and withdrawals</p>
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

            <!-- Balance Card -->
<div class="bg-gradient-to-r from-teal-600 to-teal-700 rounded-xl shadow-lg p-6 mb-6 text-white">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-teal-200">Available Balance</p>
            <p class="text-3xl font-bold">Rs. {{ number_format($balance ?? 0, 2) }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-teal-200">Total Earned</p>
            <p class="text-xl font-bold">Rs. {{ number_format($totalEarned ?? 0, 2) }}</p>
        </div>
    </div>
    <div class="mt-4 flex gap-2">
        <a href="{{ route('rider.deposit') }}" class="bg-white text-teal-700 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-sm font-medium">
            <i class="fas fa-plus mr-2"></i> Deposit
        </a>
        <a href="{{ route('rider.orders.available') }}" class="bg-teal-500 text-white px-4 py-2 rounded-lg hover:bg-teal-400 transition text-sm font-medium">
            <i class="fas fa-search mr-2"></i> Find Orders
        </a>
    </div>
</div>



            <!-- Rider Deposit Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-700">💰 Rider Deposit Balance</p>
                        <p class="text-2xl font-bold text-blue-700">Rs. {{ number_format($depositBalance ?? 0, 2) }}</p>
                        <p class="text-xs text-blue-500">Deposit used for COD orders</p>
                    </div>
                    <div>
                        <p class="text-sm text-blue-700">Deposit Limit</p>
                        <p class="text-lg font-bold text-blue-700">Rs. {{ number_format($depositLimit ?? 50000, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <a href="{{ route('rider.earnings') }}" class="bg-white hover:bg-gray-50 rounded-xl shadow-sm p-4 text-center border border-gray-200 transition">
                    <i class="fas fa-chart-bar text-2xl text-blue-600 block mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">View Earnings</span>
                </a>
                <a href="{{ route('rider.orders.available') }}" class="bg-white hover:bg-gray-50 rounded-xl shadow-sm p-4 text-center border border-gray-200 transition">
                    <i class="fas fa-search text-2xl text-teal-600 block mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">Find Orders</span>
                </a>
            </div>

            <!-- Transactions -->
            <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Recent Transactions</h3>
            
            @if(isset($transactions) && $transactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Date</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Description</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Amount</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Type</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3 text-sm">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                    <td class="py-2 px-3">{{ $transaction->description }}</td>
                                    <td class="py-2 px-3 font-medium {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->type === 'credit' ? '+' : '-' }} Rs. {{ number_format($transaction->amount, 2) }}
                                    </td>
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $transaction->type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                               'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-history text-4xl block mb-2 text-gray-300"></i>
                    <p>No transactions yet</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection