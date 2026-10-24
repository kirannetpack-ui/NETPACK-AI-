@extends('layouts.seller')

@section('title', 'Wallet')
@section('page-title', 'My Wallet')

@section('content')
<div class="max-w-7xl mx-auto">
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

    <!-- Balance Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-r from-teal-600 to-teal-700 rounded-xl shadow-lg p-6 text-white">
            <p class="text-sm text-teal-200">Available Balance</p>
            <p class="text-3xl font-bold">Rs. {{ number_format($summary['balance'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">Pending Balance</p>
            <p class="text-2xl font-bold text-yellow-600">Rs. {{ number_format($summary['pending'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Total Earned</p>
            <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($summary['total_earned'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-500">
            <p class="text-sm text-gray-500">Total Withdrawn</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($summary['total_withdrawn'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="bg-white rounded-xl shadow-sm mb-6">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">💳 Payment Methods</h3>
                <p class="text-sm text-gray-500">Manage your payout methods</p>
            </div>
            <button onclick="showAddPaymentModal()" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-plus mr-2"></i> Add Payment Method
            </button>
        </div>
        <div class="p-6">
            @if($paymentMethods->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($paymentMethods as $method)
                        <div class="border rounded-lg p-4 {{ $method->is_default ? 'border-teal-500 bg-teal-50' : 'border-gray-200' }}">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl">
                                            @if($method->method_type === 'bank') 🏦
                                            @elseif($method->method_type === 'esewa') 📱
                                            @elseif($method->method_type === 'khalti') 📱
                                            @elseif($method->method_type === 'connectips') 📱
                                            @endif
                                        </span>
                                        <span class="font-semibold">{{ $method->method_label }}</span>
                                        @if($method->is_default)
                                            <span class="px-2 py-0.5 bg-teal-600 text-white text-xs rounded-full">Default</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ $method->display_name }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{ $method->is_verified ? '✅ Verified' : '⏳ Pending Verification' }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    @if(!$method->is_default && $method->is_verified)
                                        <form method="POST" action="{{ route('seller.wallet.set-default', $method->id) }}">
                                            @csrf
                                            <button type="submit" class="text-teal-600 hover:text-teal-800 text-sm" title="Set as default">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if(!$method->is_default)
                                        <form method="POST" action="{{ route('seller.wallet.delete-method', $method->id) }}" 
                                              onsubmit="return confirm('Delete this payment method?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-credit-card text-4xl block mb-2 text-gray-300"></i>
                    <p>No payment methods added yet</p>
                    <p class="text-sm">Add a payment method to receive payouts</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Payout Request -->
    @if($hasPaymentMethod)
        <div class="bg-white rounded-xl shadow-sm mb-6">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">💰 Request Payout</h3>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('seller.wallet.request-payout') }}" class="flex flex-wrap items-end gap-4">
                    @csrf
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium mb-1">Amount (NPR)</label>
                        <input type="number" name="amount" step="0.01" min="100" required 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                               placeholder="Enter amount">
                        <p class="text-xs text-gray-500 mt-1">Minimum: Rs. 100. Fee: 0.5% (Min Rs. 10, Max Rs. 500)</p>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium mb-1">Payment Method</label>
                        <select name="payment_method_id" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            @foreach($paymentMethods->where('is_verified', true) as $method)
                                <option value="{{ $method->id }}" {{ $method->is_default ? 'selected' : '' }}>
                                    {{ $method->display_name }} {{ $method->is_default ? '(Default)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-hand-holding-usd mr-2"></i> Request Payout
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Recent Payouts -->
    @if($recentPayouts->count() > 0)
        <div class="bg-white rounded-xl shadow-sm mb-6">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">📤 Recent Payouts</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Reference</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Amount</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Net</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Method</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayouts as $payout)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3 font-mono text-sm">{{ $payout->reference_number }}</td>
                                    <td class="py-2 px-3 font-medium">Rs. {{ number_format($payout->amount, 2) }}</td>
                                    <td class="py-2 px-3">Rs. {{ number_format($payout->net_amount, 2) }}</td>
                                    <td class="py-2 px-3">{{ $payout->paymentMethod->display_name ?? 'N/A' }}</td>
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $payout->status_badge }}">
                                            {{ $payout->status_label }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-sm">{{ $payout->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Recent Transactions -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">📊 Recent Transactions</h3>
        </div>
        <div class="p-6">
            @if($transactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Date</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Description</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Type</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Amount</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3 text-sm">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                    <td class="py-2 px-3">{{ $transaction->description ?? 'N/A' }}</td>
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $transaction->type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 font-medium {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->type === 'credit' ? '+' : '-' }} Rs. {{ number_format($transaction->amount, 2) }}
                                    </td>
                                    <td class="py-2 px-3">Rs. {{ number_format($transaction->balance_after, 2) }}</td>
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

<!-- Add Payment Method Modal -->
<div id="addPaymentModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Add Payment Method</h3>
                <button onclick="closeAddPaymentModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('seller.wallet.add-payment-method') }}">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Method Type <span class="text-red-500">*</span></label>
                        <select name="method_type" id="method_type" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500" onchange="togglePaymentFields()">
                            <option value="">Select Method</option>
                            <option value="bank">🏦 Bank Account</option>
                            <option value="esewa">📱 eSewa</option>
                            <option value="khalti">📱 Khalti</option>
                            <option value="connectips">📱 ConnectIPS</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Account Holder Name <span class="text-red-500">*</span></label>
                        <input type="text" name="account_name" required 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <!-- Bank Fields -->
                    <div id="bank_fields" class="hidden">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Bank Name <span class="text-red-500">*</span></label>
                            <input type="text" name="bank_name" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Account Number <span class="text-red-500">*</span></label>
                            <input type="text" name="account_number" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Branch</label>
                            <input type="text" name="branch" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Account Type</label>
                            <select name="account_type" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <option value="savings">Savings</option>
                                <option value="current">Current</option>
                            </select>
                        </div>
                    </div>

                    <!-- eSewa Fields -->
                    <div id="esewa_fields" class="hidden">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">eSewa ID <span class="text-red-500">*</span></label>
                            <input type="text" name="esewa_id" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Mobile Number <span class="text-red-500">*</span></label>
                            <input type="text" name="mobile_number" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>

                    <!-- Khalti Fields -->
                    <div id="khalti_fields" class="hidden">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Khalti ID <span class="text-red-500">*</span></label>
                            <input type="text" name="khalti_id" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Mobile Number <span class="text-red-500">*</span></label>
                            <input type="text" name="mobile_number" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>

                    <!-- ConnectIPS Fields -->
                    <div id="connectips_fields" class="hidden">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">ConnectIPS ID <span class="text-red-500">*</span></label>
                            <input type="text" name="connectips_id" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_default" value="1" checked
                                   class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            <span class="text-sm font-medium">Set as default payment method</span>
                        </label>
                    </div>

                    <div class="flex gap-3 pt-4 border-t">
                        <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-save mr-2"></i> Add Payment Method
                        </button>
                        <button type="button" onclick="closeAddPaymentModal()" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showAddPaymentModal() {
    document.getElementById('addPaymentModal').classList.remove('hidden');
}

function closeAddPaymentModal() {
    document.getElementById('addPaymentModal').classList.add('hidden');
}

function togglePaymentFields() {
    const type = document.getElementById('method_type').value;
    document.querySelectorAll('[id$="_fields"]').forEach(el => el.classList.add('hidden'));
    if (type) {
        document.getElementById(type + '_fields').classList.remove('hidden');
    }
}

// Click outside to close modal
document.getElementById('addPaymentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddPaymentModal();
    }
});
</script>
@endsection