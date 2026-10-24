@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Account Settings')

@section('sidebar')
    <a href="{{ route('client.dashboard') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-home w-5"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('shipments.create') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-plus-circle w-5"></i>
        <span>New Shipment</span>
    </a>
    <a href="{{ route('tracking.page') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-search w-5"></i>
        <span>Track Shipment</span>
    </a>
    <a href="{{ route('grocery.box') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-shopping-bag w-5"></i>
        <span>Grocery Box</span>
    </a>
    <a href="{{ route('client.feedback') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-comment w-5"></i>
        <span>Feedback</span>
    </a>
    <a href="{{ route('client.support') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-headset w-5"></i>
        <span>Support</span>
    </a>
    <a href="{{ route('profile') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-user w-5"></i>
        <span>Profile</span>
    </a>
    <a href="{{ route('client.settings') }}" class="sidebar-link active flex items-center space-x-3 px-4 py-3 text-sm text-white">
        <i class="fas fa-cog w-5"></i>
        <span>Settings</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-800">Account Settings</h2>
        <p class="text-gray-500 mt-1">Manage your account preferences and security settings</p>
    </div>

    <!-- Settings -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Profile Settings -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-lg mb-4">Profile Settings</h3>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" value="{{ Auth::user()->name }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ Auth::user()->email }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ Auth::user()->phone ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </div>
                    <button type="submit" class="w-full bg-teal-500 hover:bg-teal-600 text-white font-semibold py-2 rounded-lg transition">
                        <i class="fas fa-save mr-2"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-lg mb-4">Change Password</h3>
            <form action="{{ route('profile.change-password') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input type="password" name="current_password" placeholder="Enter current password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" name="new_password" placeholder="Enter new password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" placeholder="Confirm new password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </div>
                    <button type="submit" class="w-full bg-teal-500 hover:bg-teal-600 text-white font-semibold py-2 rounded-lg transition">
                        <i class="fas fa-key mr-2"></i> Change Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Preferences -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-lg mb-4">Preferences</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">Email Notifications</p>
                        <p class="text-sm text-gray-500">Receive email updates about your shipments</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">SMS Notifications</p>
                        <p class="text-sm text-gray-500">Receive SMS alerts for shipment updates</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">Two-Factor Authentication</p>
                        <p class="text-sm text-gray-500">Add an extra layer of security</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Account Actions -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-lg mb-4">Account Actions</h3>
            <div class="space-y-3">
                <button class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg transition flex items-center justify-center">
                    <i class="fas fa-download mr-2"></i> Download Account Data
                </button>
                <button class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg transition flex items-center justify-center">
                    <i class="fas fa-file-export mr-2"></i> Export Shipment History
                </button>
                <button class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-3 rounded-lg transition flex items-center justify-center">
                    <i class="fas fa-trash mr-2"></i> Delete Account
                </button>
            </div>
        </div>
    </div>
</div>
@endsection