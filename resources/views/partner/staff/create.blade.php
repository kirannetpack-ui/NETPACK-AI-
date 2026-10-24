{{-- resources/views/partner/staff/create.blade.php --}}
@extends('layouts.partner')

@section('title', 'Add Staff Member')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h1 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-user-plus text-teal-600"></i>
                <span>Add Staff Member</span>
            </h1>
        </div>
        
        <form method="POST" action="{{ route('partner.staff.store') }}" class="p-6">
            @csrf
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Full Name *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Email *</label>
                    <input type="email" name="email" required class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Phone *</label>
                    <input type="text" name="phone" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Temporary password *</label>
                    <input type="password" name="password" required autocomplete="new-password" class="w-full px-3 py-2 border rounded-lg">
                    <p class="text-xs text-gray-500">Use at least 12 characters with upper and lower case letters, a number, and a symbol.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Confirm temporary password *</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password" class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Position *</label>
                    <input type="text" name="position" required placeholder="e.g., Senior Scanner" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Role *</label>
                    <select name="role" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="scanner">Scanner</option>
                        <option value="delivery_boy">Delivery Boy</option>
                        <option value="dispatcher">Dispatcher</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Permissions</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="can_scan_arrival" value="1">
                        <span>Can Scan Arrival</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="can_scan_departure" value="1">
                        <span>Can Scan Departure</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="can_scan_delivery" value="1">
                        <span>Can Scan Delivery</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="can_add_notes" value="1" checked>
                        <span>Can Add Notes</span>
                    </label>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700">
                    Add Staff
                </button>
                <a href="{{ route('partner.staff.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
