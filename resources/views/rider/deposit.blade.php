@extends('layouts.app')

@section('title', 'Deposit Funds')
@section('page-title', 'Deposit to Wallet')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">💰 Deposit Funds</h1>
                <p class="text-sm text-gray-500 mt-1">Add funds to your wallet</p>
            </div>
            <a href="{{ route('rider.wallet') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Wallet
            </a>
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

            <!-- Balance Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <p class="text-sm text-gray-600">Current Deposit Balance</p>
                    <p class="text-2xl font-bold text-blue-600">Rs. {{ number_format($depositBalance, 2) }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <p class="text-sm text-gray-600">Available Deposit Limit</p>
                    <p class="text-2xl font-bold text-purple-600">Rs. {{ number_format($maxDeposit, 2) }}</p>
                </div>
            </div>

            <!-- No Payment Method Warning -->
            @if($paymentMethods->count() == 0)
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    You don't have any verified payment methods. 
                    <a href="{{ route('rider.payment-methods.add') }}" class="text-yellow-800 font-semibold hover:underline">
                        Add a payment method →
                    </a>
                </div>
            @endif

            <!-- Deposit Form -->
            <form method="POST" action="{{ route('rider.deposit.process') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-1">Amount (NPR) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500">Rs.</span>
                        <input type="number" name="amount" step="0.01" min="100" max="{{ $maxDeposit }}" required 
                               class="w-full pl-10 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                               placeholder="Enter amount" id="depositAmount" oninput="updateSummary()">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Minimum deposit: Rs. 100 | Maximum: Rs. {{ number_format($maxDeposit, 0) }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Select Payment Method <span class="text-red-500">*</span></label>
                    @if($paymentMethods->count() > 0)
                        <div class="space-y-3">
                            @foreach($paymentMethods as $method)
                                <label class="payment-method border rounded-lg p-4 cursor-pointer hover:border-teal-500 transition flex items-center justify-between
                                    {{ $method->is_default ? 'border-teal-500 bg-teal-50' : 'border-gray-200' }}">
                                    <div class="flex items-center gap-4">
                                        <input type="radio" name="payment_method_id" value="{{ $method->id }}" 
                                               class="payment-radio w-4 h-4 text-teal-600"
                                               {{ $method->is_default ? 'checked' : '' }}
                                               onchange="updateSummary()">
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
                                            <p class="text-sm text-gray-600">{{ $method->display_name }}</p>
                                            @if($method->is_verified)
                                                <span class="text-xs text-green-600">✓ Verified</span>
                                            @else
                                                <span class="text-xs text-yellow-600">⏳ Pending Verification</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($method->is_default)
                                        <div class="text-teal-600">
                                            <i class="fas fa-check-circle text-xl"></i>
                                        </div>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                        
                        <!-- Add Payment Method Link -->
                        <div class="mt-3 text-center">
                            <a href="{{ route('rider.payment-methods.add') }}" class="text-teal-600 hover:text-teal-800 text-sm">
                                <i class="fas fa-plus mr-1"></i> Add another payment method
                            </a>
                            <span class="text-gray-300 mx-2">|</span>
                            <a href="{{ route('rider.payment-methods') }}" class="text-teal-600 hover:text-teal-800 text-sm">
                                <i class="fas fa-list mr-1"></i> Manage payment methods
                            </a>
                        </div>
                    @else
                        <div class="text-center py-6 text-gray-500 border rounded-lg">
                            <i class="fas fa-credit-card text-3xl block mb-2 text-gray-300"></i>
                            <p>No payment methods available</p>
                            <a href="{{ route('rider.payment-methods.add') }}" class="text-teal-600 hover:underline text-sm inline-block mt-2">
                                <i class="fas fa-plus mr-1"></i> Add your first payment method
                            </a>
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Transaction ID (Optional)</label>
                    <input type="text" name="transaction_id" 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                           placeholder="Reference number or transaction ID">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Remarks (Optional)</label>
                    <textarea name="remarks" rows="2" 
                              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                              placeholder="Additional notes"></textarea>
                </div>

                <!-- Deposit Summary -->
                <div id="depositSummary" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-700 mb-2">📊 Deposit Summary</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Amount</p>
                            <p class="font-bold text-teal-600" id="summaryAmount">Rs. 0.00</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Payment Method</p>
                            <p class="font-bold" id="summaryMethod">Select a method</p>
                        </div>
                        <div>
                            <p class="text-gray-500">New Balance</p>
                            <p class="font-bold text-blue-600" id="summaryBalance">Rs. {{ number_format($depositBalance, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Available Limit</p>
                            <p class="font-bold text-purple-600">Rs. {{ number_format($maxDeposit, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        Deposits are processed immediately. Your deposit balance will be updated instantly.
                    </p>
                </div>

                <div class="flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition flex-1" 
                            {{ $paymentMethods->count() == 0 ? 'disabled' : '' }}>
                        <i class="fas fa-plus mr-2"></i> Deposit Funds
                    </button>
                    <a href="{{ route('rider.wallet') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>

            <!-- Quick Actions -->
            <div class="mt-6 pt-4 border-t">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('rider.payment-methods.add') }}" class="text-teal-600 hover:text-teal-800 text-sm">
                        <i class="fas fa-plus mr-1"></i> Add Payment Method
                    </a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('rider.payment-methods') }}" class="text-teal-600 hover:text-teal-800 text-sm">
                        <i class="fas fa-list mr-1"></i> Manage Payment Methods
                    </a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('rider.wallet') }}" class="text-teal-600 hover:text-teal-800 text-sm">
                        <i class="fas fa-wallet mr-1"></i> View Wallet
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateSummary() {
    const amount = document.getElementById('depositAmount').value;
    const selectedMethod = document.querySelector('input[name="payment_method_id"]:checked');
    
    const summaryAmount = document.getElementById('summaryAmount');
    const summaryMethod = document.getElementById('summaryMethod');
    const summaryBalance = document.getElementById('summaryBalance');
    
    if (amount && parseFloat(amount) > 0) {
        summaryAmount.textContent = 'Rs. ' + parseFloat(amount).toFixed(2);
        const newBalance = {{ $depositBalance }} + parseFloat(amount);
        summaryBalance.textContent = 'Rs. ' + newBalance.toFixed(2);
    } else {
        summaryAmount.textContent = 'Rs. 0.00';
        summaryBalance.textContent = 'Rs. {{ number_format($depositBalance, 2) }}';
    }
    
    if (selectedMethod) {
        const label = selectedMethod.closest('.payment-method');
        const methodName = label.querySelector('.font-semibold')?.textContent || 'Selected';
        summaryMethod.textContent = methodName.trim();
    } else {
        summaryMethod.textContent = 'Select a method';
    }
}

// Update summary on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSummary();
    
    // Also update when radio buttons change
    document.querySelectorAll('.payment-radio').forEach(function(radio) {
        radio.addEventListener('change', updateSummary);
    });
});
</script>
@endpush
@endsection