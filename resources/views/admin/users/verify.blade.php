@extends('layouts.app')

@section('title', 'Verify User')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b bg-yellow-50">
            <h1 class="text-xl font-semibold text-gray-800">Verify User</h1>
            <p class="text-sm text-gray-500">Review user information before approval</p>
        </div>
        
        <div class="p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center text-2xl">
                    @if($user->profile_photo)
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full">
                    @else
                        <span class="text-gray-600 font-medium">{{ substr($user->name, 0, 2) }}</span>
                    @endif
                </div>
                <div>
                    <h2 class="text-2xl font-bold">{{ $user->name }}</h2>
                    <div class="flex gap-2 mt-1">
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $user->admin_role_badge ?? 'gray' }}-100 text-{{ $user->admin_role_badge ?? 'gray' }}-800">
                            {{ $user->user_type_label ?? ucfirst($user->user_type) }}
                        </span>
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-1"></i> Pending Verification
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium">{{ $user->email }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="font-medium">{{ $user->phone ?? 'Not provided' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">User Type</p>
                    <p class="font-medium">{{ $user->user_type_label ?? ucfirst($user->user_type) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Date of Birth</p>
                    <p class="font-medium">{{ $user->dob ? \Carbon\Carbon::parse($user->dob)->format('M d, Y') : 'Not specified' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Gender</p>
                    <p class="font-medium">{{ ucfirst($user->gender ?? 'Not specified') }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Nationality</p>
                    <p class="font-medium">{{ $user->nationality ?? 'Not specified' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">District</p>
                    <p class="font-medium">{{ $user->district ?? 'Not specified' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Province</p>
                    <p class="font-medium">{{ $user->province ?? 'Not specified' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Postal Code</p>
                    <p class="font-medium">{{ $user->postal_code ?? 'Not specified' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Country</p>
                    <p class="font-medium">{{ $user->country ?? 'Nepal' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">City / Municipality</p>
                    <p class="font-medium">{{ $user->city ?? 'Not specified' }}</p>
                </div>
                @if($user->address)
                <div class="md:col-span-2 border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Address</p>
                    <p class="font-medium">{{ $user->address }}</p>
                </div>
                @endif
                @if($user->permanent_address || $user->temporary_address)
                    <div class="md:col-span-2 border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Registered Addresses</p>
                        <dl class="mt-2 grid gap-3 text-sm md:grid-cols-2">
                            <div><dt class="font-medium text-gray-700">Permanent</dt><dd>{{ $user->permanent_address ?? 'Not specified' }}</dd></div>
                            <div><dt class="font-medium text-gray-700">Temporary</dt><dd>{{ $user->temporary_address ?? 'Not specified' }}</dd></div>
                        </dl>
                    </div>
                @endif
                @if($user->business_name)
                <div class="md:col-span-2 border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Business Name</p>
                    <p class="font-medium">{{ $user->business_name }}</p>
                </div>
                @endif
                @if($user->license_number)
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">License Number</p>
                    <p class="font-medium">{{ $user->license_number }}</p>
                </div>
                @endif
                @if($user->vehicle_type)
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Vehicle Type</p>
                    <p class="font-medium">{{ ucfirst($user->vehicle_type) }}</p>
                </div>
                @endif
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Registered On</p>
                    <p class="font-medium">{{ $user->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
            
            <div class="flex gap-3 pt-4 border-t">
                <form method="POST" action="{{ route('admin.users.approve', $user->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-check mr-2"></i> Approve User
                    </button>
                </form>
                
                <button onclick="showRejectForm()" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-times mr-2"></i> Reject
                </button>
                
                <a href="{{ route('admin.users.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                    Back
                </a>
            </div>
            
            <div id="reject_form" class="hidden mt-4 p-4 bg-red-50 rounded-lg border border-red-200">
                <h3 class="font-medium text-red-800 mb-2">Reject User</h3>
                <form method="POST" action="{{ route('admin.users.reject', $user->id) }}">
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
