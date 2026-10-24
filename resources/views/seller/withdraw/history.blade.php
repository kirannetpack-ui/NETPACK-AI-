@extends('layouts.seller')

@section('title', 'Withdrawal History')
@section('page-title', 'Withdrawal History')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">📤 Withdrawal History</h1>
                <p class="text-sm text-gray-500 mt-1">View all your withdrawal requests</p>
            </div>
            <a href="{{ route('seller.withdraw') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Withdraw
            </a>
        </div>

        <div class="p-6">
            @if($withdrawals->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Reference</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Amount</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Method</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($withdrawals as $withdrawal)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-mono text-sm">{{ $withdrawal->reference ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 font-medium">Rs. {{ number_format($withdrawal->amount, 2) }}</td>
                                    <td class="py-3 px-4">{{ $withdrawal->metadata['payment_method_name'] ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $withdrawal->status_badge }}">
                                            {{ $withdrawal->status_label }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm">{{ $withdrawal->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $withdrawals->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-history text-5xl text-gray-300 mb-4 block"></i>
                    <h3 class="text-lg font-semibold text-gray-700">No Withdrawal History</h3>
                    <p class="text-gray-500 mt-2">You haven't made any withdrawals yet.</p>
                    <a href="{{ route('seller.withdraw') }}" class="inline-block mt-4 bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-hand-holding-usd mr-2"></i> Make a Withdrawal
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection