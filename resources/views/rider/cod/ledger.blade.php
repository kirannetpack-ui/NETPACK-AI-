@extends('layouts.app')

@section('title', 'Rider COD Cash Ledger & Remittance')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900">COD Cash Ledger & Remittance</h1>
            <p class="text-xs text-slate-500 mt-0.5">Track your collected cash-on-delivery and submit deposit receipts to clear your outstanding balance.</p>
        </div>
        <a href="{{ route('rider.delivery.my') }}" class="px-3 py-1.5 text-xs font-bold bg-white border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-700 transition">
            Back to Cockpit
        </a>
    </div>

    <!-- Outstanding COD Card -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 text-white border border-teal-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-[10px] uppercase font-bold text-teal-300 tracking-wider">Current Outstanding Cash in Hand</p>
            <p class="text-3xl font-black font-mono text-white mt-1">Rs. {{ number_format($rider->current_outstanding_cod, 2) }}</p>
            <p class="text-xs text-slate-300 mt-1">
                Authorized COD Limit: <strong class="text-teal-300">Rs. {{ number_format($rider->cod_limit) }}</strong> ({{ $rider->cod_level }})
            </p>
        </div>
        <div class="text-right">
            <p class="text-[10px] uppercase font-bold text-slate-400">Available Headroom</p>
            <p class="text-xl font-black text-teal-400 font-mono">
                Rs. {{ number_format(max(0, $rider->cod_limit - $rider->current_outstanding_cod), 2) }}
            </p>
            <p class="text-[10px] text-slate-400 mt-0.5">Remaining capacity for COD jobs</p>
        </div>
    </div>

    <!-- Submit Deposit Form -->
    <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <h3 class="font-bold text-sm text-slate-900 border-b pb-2 flex items-center gap-2">
            <i class="fas fa-money-bill-transfer text-teal-600"></i> Submit COD Cash Deposit / Remittance
        </h3>
        <p class="text-xs text-slate-500">
            Deposit collected cash into NETPACK official accounts or at the central office counter to restore your COD delivery limit.
        </p>

        <form method="POST" action="{{ route('rider.delivery.deposit') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deposit Amount (NPR) *</label>
                    <input type="number" step="1" name="amount" required placeholder="Amount in NPR" max="{{ max(1, $rider->current_outstanding_cod) }}"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500 font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deposit Method *</label>
                    <select name="deposit_method" required class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                        <option value="bank_transfer">Bank Transfer (IPS / Mobile Banking)</option>
                        <option value="digital_wallet">Digital Wallet (eSewa / Khalti)</option>
                        <option value="cash_office">Cash Handover at Office Counter</option>
                        <option value="partner_point">Partner Agency Point</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bank Reference / Txn ID *</label>
                    <input type="text" name="deposit_reference" required placeholder="e.g. TXN-12345678"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500 font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Deposit Receipt / Screenshot (Optional)</label>
                <input type="file" name="deposit_receipt" accept="image/*"
                       class="w-full text-xs border border-slate-200 rounded-xl p-2 bg-slate-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-teal-600 file:text-white">
            </div>

            <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl transition shadow-sm">
                Submit Deposit for Admin Approval
            </button>
        </form>
    </div>

    <!-- Transactions Audit Ledger -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900 text-sm">COD Transactions History</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Balance After</th>
                        <th class="px-4 py-3">Reference / Notes</th>
                        <th class="px-4 py-3 text-right">Approval</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ledgers as $ledger)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-4 py-3 font-mono text-[10px] text-slate-500">
                            {{ $ledger->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            @if($ledger->transaction_type === 'collected')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    Cash Collected
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    Deposit Remitted
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono font-bold {{ $ledger->amount > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                            {{ $ledger->amount > 0 ? '+' : '' }}Rs. {{ number_format($ledger->amount, 2) }}
                        </td>
                        <td class="px-4 py-3 font-mono font-bold text-slate-800">
                            Rs. {{ number_format($ledger->balance_after, 2) }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-mono text-[11px] text-slate-700">{{ $ledger->deposit_reference ?? 'Pickup Handover' }}</p>
                            <p class="text-[10px] text-slate-400">{{ $ledger->notes }}</p>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $ledger->approval_status === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ ucfirst($ledger->approval_status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400">
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
