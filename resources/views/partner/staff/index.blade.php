{{-- resources/views/partner/staff/index.blade.php --}}
@extends('layouts.partner')

@section('title', 'Staff Management')

@section('content')
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
            <i class="fas fa-users text-teal-600"></i>
            <span>Staff Management</span>
        </h1>
        <a href="{{ route('partner.staff.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700">
            <i class="fas fa-user-plus mr-2"></i> Add Staff
        </a>
    </div>
    
    <div class="p-6">
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
                {{ session('success') }}
            </div>
        @endif
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($staff as $member)
            <div class="border rounded-xl p-4 hover:shadow-md transition">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center">
                                <span class="font-semibold text-teal-600">{{ substr($member->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ $member->name }}</h3>
                                <p class="text-xs text-gray-500">{{ $member->position }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('partner.staff.edit', $member) }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="deleteStaff({{ $member->id }})" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mt-3 space-y-2 text-sm">
                    <p><i class="fas fa-envelope w-5 text-gray-400"></i> {{ $member->email }}</p>
                    <p><i class="fas fa-phone w-5 text-gray-400"></i> {{ $member->phone }}</p>
                    <p><i class="fas fa-user-tag w-5 text-gray-400"></i> Role: {{ ucfirst($member->role) }}</p>
                </div>
                
                <div class="mt-3 pt-3 border-t">
                    <p class="text-xs font-medium text-gray-600 mb-2">Permissions:</p>
                    <div class="flex flex-wrap gap-1">
                        @if($member->can_scan_arrival)
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">Arrival</span>
                        @endif
                        @if($member->can_scan_departure)
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">Departure</span>
                        @endif
                        @if($member->can_scan_delivery)
                            <span class="px-2 py-1 bg-purple-100 text-purple-700 text-xs rounded-full">Delivery</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <i class="fas fa-users text-5xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">No staff members added yet</p>
                <a href="{{ route('partner.staff.create') }}" class="inline-block mt-3 text-teal-600 hover:text-teal-700">
                    Add your first staff member →
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function deleteStaff(id) {
    if (confirm('Are you sure?')) {
        fetch(`/partner/staff/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(() => location.reload());
    }
}
</script>
@endsection