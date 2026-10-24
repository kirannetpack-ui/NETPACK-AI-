@extends('layouts.app')

@section('title', 'Rider Earnings & Remittance Ledger')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header Hero Card -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 text-white border border-teal-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                <i class="fas fa-wallet text-teal-400"></i> Segregated Earnings Wallet
            </span>
            <h1 class="text-xl md:text-2xl font-black mt-1">Earnings & Remittance Ledger</h1>
            <p class="text-xs text-slate-300 mt-0.5">
                Delivery payout fees, bonuses, and withdrawals strictly separated from Cash-On-Delivery accounting.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-right bg-slate-950/60 p-3.5 rounded-xl border border-teal-700/60">
                <p class="text-[10px] uppercase font-bold text-slate-400">Available Payout Balance</p>
                <p class="text-2xl font-black text-emerald-400 font-mono">Rs. {{ number_format($walletBalance, 2) }}</p>
                <p class="text-[10px] text-teal-300 mt-0.5">Ready for bank transfer</p>
            </div>
            <a href="{{ route('rider.delivery.cod') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-amber-300 border border-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fas fa-hand-holding-dollar"></i> View COD Ledger
            </a>
        </div>
    </div>

    <!-- Earnings Overview Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Delivery Fees</p>
            <p class="text-xl font-black text-slate-900 font-mono mt-1">Rs. {{ number_format($totalDeliveryEarnings, 2) }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">{{ number_format($stats['total_deliveries']) }} Completed Deliveries</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Performance Bonuses</p>
            <p class="text-xl font-black text-emerald-600 font-mono mt-1">+Rs. {{ number_format($totalBonus, 2) }}</p>
            <p class="text-[10px] text-emerald-600 mt-0.5">SLA & volume incentives</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Penalties Deducted</p>
            <p class="text-xl font-black text-rose-600 font-mono mt-1">-Rs. {{ number_format($totalPenalties, 2) }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Disputes or cancellations</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Withdrawn</p>
            <p class="text-xl font-black text-slate-700 font-mono mt-1">Rs. {{ number_format($totalWithdrawn, 2) }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Transferred to bank/wallet</p>
        </div>
    </div>

    <!-- Segregated Earnings Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Rider Earnings Ledger Transactions</h3>
                <p class="text-xs text-slate-500">Immutable credit/debit record for deliveries fulfilled by you</p>
            </div>
            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-teal-50 text-teal-800 border border-teal-200">
                Verified Ledger
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Timestamp</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Master AWB / Reference</th>
                        <th class="px-4 py-3">Notes</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Balance After</th>
                        <th class="px-4 py-3 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($earningsLedgers as $item)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-mono text-[11px] text-slate-500">
                            {{ $item->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            @if($item->type === 'delivery_fee')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-800 border border-teal-200">
                                    Delivery Fee
                                </span>
                            @elseif($item->type === 'bonus')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    Bonus
                                </span>
                            @elseif($item->type === 'penalty')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                    Penalty
                                </span>
                            @elseif($item->type === 'withdrawal')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                    Withdrawal
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                    {{ ucfirst($item->type) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono font-bold text-slate-800">
                            @if($item->assignment)
                                <a href="{{ route('rider.delivery.show', $item->assignment_id) }}" class="text-teal-700 hover:underline">
                                    {{ $item->assignment->master_awb }}
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 max-w-xs truncate">
                            {{ $item->notes ?? 'Delivery payout credit' }}
                        </td>
                        <td class="px-4 py-3 font-mono font-bold {{ $item->amount >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $item->amount >= 0 ? '+' : '' }}Rs. {{ number_format($item->amount, 2) }}
                        </td>
                        <td class="px-4 py-3 font-mono font-bold text-slate-900">
                            Rs. {{ number_format($item->balance_after, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $item->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-coins text-3xl text-slate-300 mb-2"></i>
                            <p class="font-bold text-slate-600">No earnings recorded yet</p>
                            <p class="text-[11px] mt-0.5 text-slate-400">Complete delivery jobs to accumulate payout fees and bonuses.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($earningsLedgers->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $earningsLedgers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection