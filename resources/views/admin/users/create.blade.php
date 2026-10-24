@extends('layouts.app')

@section('title', 'Add New User')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Add New User</h1>
            <p class="text-sm text-gray-500 mt-1">Create a permitted account type with a clear approval status.</p>
        </div>
        
        <div class="p-6">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                
                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Personal Information -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Personal Information</h3>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('phone') border-red-500 @enderror">
                        @error('phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Gender</label>
                        <select name="gender" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Date of Birth</label>
                        <input type="date" name="dob" value="{{ old('dob') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <!-- Role & Status -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Account Settings</h3>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Account Type *</label>
                        <select name="user_type" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('user_type') border-red-500 @enderror">
                            <option value="">Select account type</option>
                            @foreach($manageableUserTypes as $type => $label)
                                <option value="{{ $type }}" @selected(old('user_type') === $type)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('user_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Only account types your role is allowed to manage are shown.</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Account Status *</label>
                        <select name="verification_status" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('verification_status') border-red-500 @enderror">
                            <option value="pending" {{ old('verification_status') === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                            <option value="approved" {{ old('verification_status', 'approved') === 'approved' ? 'selected' : '' }}>✅ Approved</option>
                            <option value="rejected" {{ old('verification_status') === 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                            <option value="suspended" {{ old('verification_status') === 'suspended' ? 'selected' : '' }}>⛔ Suspended</option>
                        </select>
                        @error('verification_status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Staff Service Scope (For Staff Accounts)</label>
                        <select name="service_scope" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                            <option value="all" selected>👑 All Services (Super Admin Staff — Can view all services and underneath)</option>
                            <option value="international">✈️ International Air Freight Only (Scoped strictly to International Hubs & Flights)</option>
                            <option value="domestic">🏔️ Nepal Domestic Logistics Only (Scoped strictly to 7 Provincial Hubs & Depots)</option>
                            <option value="ecommerce">🛵 E-Commerce & Rider Fleet Only (Scoped strictly to E-Commerce & Deliveries)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Defines the operational boundary. Super Admin staff can view all 3 services; department staff only see their assigned domain.</p>
                    </div>

                    <!-- Password -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Security</h3>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Password *</label>
                        <input type="password" name="password" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Use at least 12 characters. The user should change it after first login.</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <!-- Address -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Territory & Address Information</h3>
                    </div>

                    <div class="md:col-span-2">
                        <x-nepal-territory-picker 
                            provinceName="province" 
                            districtName="district" 
                            provinceLabel="Operating Province / Sector" 
                            districtLabel="Operating District" 
                            idPrefix="admin_create_geo" 
                            helperText="Assigns the operating territory across Nepal's 7 Provinces and 77 Districts." />
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Permanent Address</label>
                        <input type="text" name="permanent_address" value="{{ old('permanent_address') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Temporary Address</label>
                        <input type="text" name="temporary_address" value="{{ old('temporary_address') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>
                
                <div class="mt-6 flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-user-plus mr-2"></i> Create User
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
