@extends('layouts.app')

@section('title', 'Partner Dashboard')
@section('page-title', 'Partner Dashboard')

@section('sidebar')
    <a href="{{ route('partner.dashboard') }}" class="sidebar-link active flex items-center space-x-3 px-4 py-3 text-sm">
        <i class="fas fa-home w-5"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('partner.deliveries') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-truck w-5"></i>
        <span>Deliveries</span>
    </a>
    <a href="{{ route('partner.zones.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-map w-5"></i>
        <span>Zones</span>
    </a>
    <a href="{{ route('partner.rates.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-calculator w-5"></i>
        <span>Rates</span>
    </a>
    <a href="{{ route('partner.staff.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-users w-5"></i>
        <span>Staff</span>
    </a>
    <a href="{{ route('partner.settings') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-cog w-5"></i>
        <span>Settings</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Status Banner -->
    @if(Auth::user()->verification_status === 'pending')
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-clock text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Verification in Progress:</strong> Your KYC documents are being reviewed. 
                        You'll receive an email confirmation once approved.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Deliveries</p>
                    <p class="text-2xl font-bold text-pink-600">0</p>
                </div>
                <div class="bg-pink-100 rounded-full p-3">
                    <i class="fas fa-check-circle text-pink-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Revenue</p>
                    <p class="text-2xl font-bold text-green-600">Rs. 0</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-money-bill-wave text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Active Staff</p>
                    <p class="text-2xl font-bold text-indigo-600">0</p>
                </div>
                <div class="bg-indigo-100 rounded-full p-3">
                    <i class="fas fa-users text-indigo-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Rating</p>
                    <p class="text-2xl font-bold text-yellow-600">4.8</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <i class="fas fa-star text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-lg mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('partner.zones.index') }}" class="bg-pink-500 hover:bg-pink-600 text-white text-center py-3 rounded-lg transition">
                    <i class="fas fa-plus-circle block text-xl mb-1"></i>
                    Manage Zones
                </a>
                <a href="{{ route('partner.staff.index') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white text-center py-3 rounded-lg transition">
                    <i class="fas fa-user-plus block text-xl mb-1"></i>
                    Add Staff
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-lg mb-4">Account Status</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">Account Type</span>
                    <span class="font-medium capitalize">{{ Auth::user()->user_type }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    <span class="font-medium capitalize text-{{ Auth::user()->verification_status === 'approved' ? 'green' : 'yellow' }}-600">
                        {{ Auth::user()->verification_status === 'approved' ? 'Active' : 'Pending Review' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Member Since</span>
                    <span class="font-medium">{{ Auth::user()->created_at ? Auth::user()->created_at->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection