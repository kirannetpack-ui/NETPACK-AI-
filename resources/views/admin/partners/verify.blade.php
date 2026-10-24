@extends('layouts.app')

@section('title', 'Verify Partner')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b bg-yellow-50">
            <h1 class="text-xl font-semibold text-gray-800">Verify Partner</h1>
            <p class="text-sm text-gray-500">Review partner information before approval</p>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Company Name</p>
                    <p class="font-medium">{{ $partner->company_name }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Contact Person</p>
                    <p class="font-medium">{{ $partner->contact_person }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium">{{ $partner->email }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="font-medium">{{ $partner->phone }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Address</p>
                    <p class="font-medium">{{ $partner->address }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">City / District</p>
                    <p class="font-medium">{{ $partner->city }}, {{ $partner->district }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Province</p>
                    <p class="font-medium">{{ $partner->province }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Margin Percentage</p>
                    <p class="font-medium">{{ $partner->margin_percentage }}%</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Current Status</p>
                    @if($partner->is_active)
                        <span class="text-green-600 font-medium">Active</span>
                    @else
                        <span class="text-red-600 font-medium">Inactive</span>
                    @endif
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Verification Status</p>
                    <p class="font-medium text-yellow-600">Pending KYC Verification</p>
                </div>
            </div>
            
            <div class="flex gap-3 pt-4 border-t">
                <form method="POST" action="{{ route('admin.partners.approve', $partner->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-check mr-2"></i> Approve Partner
                    </button>
                </form>
                
                <button onclick="showRejectForm()" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-times mr-2"></i> Reject
                </button>
                
                <a href="{{ route('admin.partners.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                    Cancel
                </a>
            </div>
            
            <div id="reject_form" class="hidden mt-4 p-4 bg-red-50 rounded-lg border border-red-200">
                <h3 class="font-medium text-red-800 mb-2">Reject Partner</h3>
                <form method="POST" action="{{ route('admin.partners.reject', $partner->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Rejection Reason *</label>
                        <textarea name="rejection_reason" rows="3" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" required placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            Submit Rejection
                        </button>
                        <button type="button" onclick="hideRejectForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showRejectForm() {
    document.getElementById('reject_form').classList.remove('hidden');
}
function hideRejectForm() {
    document.getElementById('reject_form').classList.add('hidden');
}
</script>
@endsection