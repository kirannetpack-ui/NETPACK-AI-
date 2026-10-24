{{-- resources/views/profile/show.blade.php --}}
@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-teal-600 to-emerald-600 px-6 py-4">
            <h1 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </h1>
            <p class="text-teal-100 text-xs mt-1">Manage your account information</p>
        </div>
        
        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-4">
                    {{ session('success') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            
            <!-- Profile Information -->
            <form method="POST" action="{{ route('profile.update') }}" class="mb-8">
                @csrf
                @method('PUT')
                
                <h2 class="text-lg font-medium mb-4 flex items-center gap-2">
                    <i class="fas fa-info-circle text-teal-600"></i>
                    <span>Profile Information</span>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Member Since</label>
                        <input type="text" value="{{ $user->created_at->format('M d, Y') }}" disabled
                               class="w-full px-3 py-2 border rounded-xl text-sm bg-gray-50">
                    </div>
                </div>
                
                <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-xl hover:bg-teal-700 text-sm">
                    <i class="fas fa-save mr-2"></i> Update Profile
                </button>
            </form>
            
            <!-- Change Password -->
            <form method="POST" action="{{ route('profile.change-password') }}" class="border-t pt-6">
                @csrf
                
                <h2 class="text-lg font-medium mb-4 flex items-center gap-2">
                    <i class="fas fa-lock text-teal-600"></i>
                    <span>Change Password</span>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Current Password</label>
                        <input type="password" name="current_password" required
                               class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">New Password</label>
                        <input type="password" name="new_password" required
                               class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" required
                               class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                </div>
                
                <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded-xl hover:bg-yellow-700 text-sm">
                    <i class="fas fa-key mr-2"></i> Change Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection