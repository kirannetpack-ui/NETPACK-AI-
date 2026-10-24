@extends('layouts.app')

@section('title', 'Rider COD Cash Ledgers & Deposit Approvals')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900">Rider COD Cash Ledgers & Settlements</h1>
            <p class="text-xs text-slate-500 mt-0.5">Strictly segregated audit trail of cash collected by riders and pending remittance deposits.</p>
        </div>
        <a href="{{ route('domestic.ecommerce.riders.index') }}" class="px-3 py-1.5 text-xs font-bold bg-white border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-700 transition">
            Back to Riders
        </a>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <p class="text-xs font-semibold text-slate-500">Total Outstanding Cash Held</p>
            <p class="text-2xl font-black text-amber-600 mt-1">Rs. {{ number_format($totalOutstandingCod) }}</p>
            <p class="text-[11px] text-slate-400">Cash in field awaiting deposit</p>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <p class="text-xs font-semibold text-slate-500">Pending Deposit Approvals</p>
            <p class="text-2xl font-black text-teal-700 mt-1">{{ $pendingDepositsCount }}</p>
            <p class="text-[11px] text-slate-400">Rider remittances needing review</p>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <p class="text-xs font-semibold text-slate-500">COD Security Policy</p>
            <p class="text-sm font-bold text-slate-800 mt-1">Tier-Enforced Limits</p>
            <p class="text-[11px] text-slate-400">Assignments blocked once limit reached</p>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Rider</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Balance After</th>
                        <th class="px-4 py-3">Method & Reference</th>
                        <th class="px-4 py-3">Receipt / Details</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ledgers as $ledger)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-4 py-3">
                            <p class="font-bold text-slate-900">{{ $ledger->riderProfile->full_name ?? 'Rider' }}</p>
                            <p class="text-[10px] font-mono text-teal-600">{{ $ledger->riderProfile->rider_code ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($ledger->transaction_type === 'collected')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Cash Collected
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Deposit Remitted
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono font-bold {{ $ledger->amount > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                            {{ $ledger->amount > 0 ? '+' : '' }}Rs. {{ number_format($ledger->amount, 2) }}
                        </td>
                        <td class="px-4 py-3 font-mono font-semibold text-slate-800">
                            Rs. {{ number_format($ledger->balance_after, 2) }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-bold text-slate-700 uppercase text-[10px]">{{ str_replace('_', ' ', $ledger->deposit_method ?? 'N/A') }}</p>
                            <p class="text-[10px] font-mono text-slate-500">{{ $ledger->deposit_reference ?? 'N/A' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($ledger->deposit_receipt_path)
                                <a href="{{ asset('storage/' . $ledger->deposit_receipt_path) }}" target="_blank" class="text-teal-600 hover:underline text-xs font-semibold flex items-center gap-1">
                                    <i class="fas fa-file-invoice"></i> View Receipt
                                </a>
                            @else
                                <span class="text-slate-400 text-[10px]">{{ $ledger->notes ?? 'Standard' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($ledger->approval_status === 'approved')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                    Approved
                                </span>
                            @elseif($ledger->approval_status === 'pending')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 animate-pulse">
                                    Pending Review
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700">
                                    {{ ucfirst($ledger->approval_status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($ledger->transaction_type === 'deposited' && $ledger->approval_status === 'pending')
                                <form method="POST" action="{{ route('domestic.ecommerce.riders.cod.approve', $ledger->id) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-[10px] transition shadow-xs">
                                        Approve Deposit
                                    </button>
                                </form>
                            @else
                                <span class="text-slate-400 text-[10px]">Settled</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                            No COD transactions recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($ledgers->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $ledgers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
