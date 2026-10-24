@extends('layouts.app')

@section('title', 'Add Payment Method')
@section('page-title', 'Add Payment Method')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Add Payment Method</h1>
                <p class="text-sm text-gray-500 mt-1">Add a new payment method for deposits</p>
            </div>
            <a href="{{ route('rider.payment-methods') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('rider.payment-methods.store') }}">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Method Type <span class="text-red-500">*</span></label>
                        <select name="method_type" id="method_type" required 
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                onchange="toggleFields()">
                            <option value="">Select Method</option>
                            <option value="bank">🏦 Bank Account</option>
                            <option value="esewa">📱 eSewa</option>
                            <option value="khalti">📱 Khalti</option>
                            <option value="connectips">📱 ConnectIPS</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Account Holder Name <span class="text-red-500">*</span></label>
                        <input type="text" name="account_name" required 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                               placeholder="Full name on account">
                    </div>

                    <!-- Bank Fields -->
                    <div id="bank_fields" class="hidden space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Bank Name <span class="text-red-500">*</span></label>
                            <input type="text" name="bank_name" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="Bank name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Account Number <span class="text-red-500">*</span></label>
                            <input type="text" name="account_number" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="Account number">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Branch</label>
                            <input type="text" name="branch" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="Branch name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Account Type</label>
                            <select name="account_type" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <option value="savings">Savings</option>
                                <option value="current">Current</option>
                            </select>
                        </div>
                    </div>

                    <!-- eSewa Fields -->
                    <div id="esewa_fields" class="hidden space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">eSewa ID <span class="text-red-500">*</span></label>
                            <input type="text" name="esewa_id" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="eSewa ID">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Mobile Number <span class="text-red-500">*</span></label>
                            <input type="text" name="mobile_number" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="Mobile number">
                        </div>
                    </div>

                    <!-- Khalti Fields -->
                    <div id="khalti_fields" class="hidden space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Khalti ID <span class="text-red-500">*</span></label>
                            <input type="text" name="khalti_id" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="Khalti ID">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Mobile Number <span class="text-red-500">*</span></label>
                            <input type="text" name="mobile_number" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="Mobile number">
                        </div>
                    </div>

                    <!-- ConnectIPS Fields -->
                    <div id="connectips_fields" class="hidden space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">ConnectIPS ID <span class="text-red-500">*</span></label>
                            <input type="text" name="connectips_id" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                   placeholder="ConnectIPS ID">
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_default" value="1" checked
                                   class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            <span class="text-sm font-medium">Set as default payment method</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t mt-6">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-save mr-2"></i> Add Payment Method
                    </button>
                    <a href="{{ route('rider.payment-methods') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleFields() {
    const type = document.getElementById('method_type').value;
    const fields = ['bank_fields', 'esewa_fields', 'khalti_fields', 'connectips_fields'];
    
    fields.forEach(id => {
        document.getElementById(id).classList.add('hidden');
    });
    
    if (type) {
        document.getElementById(type + '_fields').classList.remove('hidden');
    }
}
</script>
@endpush
@endsection