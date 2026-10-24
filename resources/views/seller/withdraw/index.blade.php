@extends('layouts.seller')

@section('title', 'Withdraw')
@section('page-title', 'Withdraw Funds')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">💰 Withdraw Funds</h1>
            <p class="text-sm text-gray-500 mt-1">Withdraw your earnings to your bank account or e-wallet</p>
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
                        <p class="text-sm text-teal-200">Total Withdrawn</p>
                        <p class="text-xl font-bold">Rs. {{ number_format($stats['total_withdrawn'] ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Withdraw Form -->
            @if(($balance ?? 0) > 0)
                <div class="border rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Request Withdrawal</h3>
                    <form method="POST" action="{{ route('seller.withdraw.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Amount (NPR) <span class="text-red-500">*</span></label>
                                <input type="number" name="amount" step="0.01" min="100" max="{{ $balance ?? 0 }}" required 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                       placeholder="Enter amount">
                                <p class="text-xs text-gray-500 mt-1">Minimum: Rs. 100 | Maximum: Rs. {{ number_format($balance ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Payment Method <span class="text-red-500">*</span></label>
                                <select name="payment_method_id" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="">Select Payment Method</option>
                                    @foreach($paymentMethods ?? [] as $method)
                                        <option value="{{ $method->id }}" {{ $method->is_default ? 'selected' : '' }}>
                                            {{ $method->display_name }} {{ $method->is_default ? '(Default)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    <a href="{{ route('seller.wallet') }}" class="text-teal-600 hover:underline">Add or manage payment methods</a>
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-1">Remarks (Optional)</label>
                                <textarea name="remarks" rows="2" 
                                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                          placeholder="Any additional notes"></textarea>
                            </div>
                        </div>
                        <div class="flex gap-3 pt-4 border-t mt-4">
                            <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                                <i class="fas fa-hand-holding-usd mr-2"></i> Request Withdrawal
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-info-circle mr-2"></i>
                    Your balance is Rs. 0.00. You need to have earnings to withdraw.
                </div>
            @endif

            <!-- Withdrawal History -->
            <div class="border-t pt-4">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">📤 Withdrawal History</h3>
                
                @if(isset($withdrawals) && $withdrawals->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Reference</th>
                                    <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Amount</th>
                                    <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Method</th>
                                    <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Status</th>
                                    <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Date</th>
                                    <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($withdrawals as $withdrawal)
                                    <tr class="border-b hover:bg-gray-50 transition">
                                        <td class="py-2 px-3 font-mono text-sm">{{ $withdrawal->reference_number ?? 'N/A' }}</td>
                                        <td class="py-2 px-3 font-medium">Rs. {{ number_format($withdrawal->amount, 2) }}</td>
                                        <td class="py-2 px-3">{{ $withdrawal->paymentMethod->display_name ?? 'N/A' }}</td>
                                        <td class="py-2 px-3">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $withdrawal->status_badge ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $withdrawal->status_label ?? ucfirst($withdrawal->status ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 text-sm">{{ $withdrawal->created_at->format('M d, Y') }}</td>
                                        <td class="py-2 px-3">
                                            @if($withdrawal->status === 'pending')
                                                <form method="POST" action="{{ route('seller.withdraw.cancel', $withdrawal->id) }}" 
                                                      onsubmit="return confirm('Cancel this withdrawal request?')">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $withdrawals->links() }}
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-history text-4xl block mb-2 text-gray-300"></i>
                        <p>No withdrawal history</p>
                    </div>
                @endif
            </div>

            <!-- Stats Summary -->
            <div class="border-t pt-4 mt-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500">Total Withdrawn</p>
                        <p class="text-lg font-bold text-teal-600">Rs. {{ number_format($stats['total_withdrawn'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500">Pending Withdrawals</p>
                        <p class="text-lg font-bold text-yellow-600">Rs. {{ number_format($stats['pending_withdrawals'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500">Total Withdrawals</p>
                        <p class="text-lg font-bold text-blue-600">{{ number_format($stats['total_count'] ?? 0) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500">Available Balance</p>
                        <p class="text-lg font-bold text-green-600">Rs. {{ number_format($balance ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection