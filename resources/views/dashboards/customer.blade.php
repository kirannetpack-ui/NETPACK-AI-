@extends('layouts.app')

@section('title', 'Customer Dashboard')
@section('page-title', 'Customer Dashboard')

@section('sidebar')
    <a href="{{ route('customer.dashboard') }}" class="sidebar-link active flex items-center space-x-3 px-4 py-3 text-sm text-white">
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
    <a href="{{ route('client.settings') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-cog w-5"></i>
        <span>Settings</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="gradient-bg rounded-2xl p-6 text-white">
        <div class="flex flex-wrap justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold">Welcome back, {{ Auth::user()->name }}!</h2>
                <p class="text-teal-100 mt-1">Your one-stop solution for international & domestic shipping</p>
            </div>
            <div class="bg-white/20 rounded-xl px-4 py-2 mt-4 md:mt-0">
                <i class="fas fa-star text-yellow-400"></i>
                <span class="ml-1">Premium Member</span>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    @php
        $totalShipments = Auth::user()->shipmentsAsCustomer ? Auth::user()->shipmentsAsCustomer->count() : 0;
        $inTransit = Auth::user()->shipmentsAsCustomer ? Auth::user()->shipmentsAsCustomer->where('status', 'in_transit')->count() : 0;
        $delivered = Auth::user()->shipmentsAsCustomer ? Auth::user()->shipmentsAsCustomer->where('status', 'delivered')->count() : 0;
        $pending = Auth::user()->shipmentsAsCustomer ? Auth::user()->shipmentsAsCustomer->where('status', 'pending')->count() : 0;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-4 card-hover transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Shipments</p>
                    <p class="text-2xl font-bold text-teal-600">{{ $totalShipments }}</p>
                </div>
                <div class="bg-teal-100 rounded-full p-3">
                    <i class="fas fa-box text-teal-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 card-hover transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">In Transit</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $inTransit }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-truck text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 card-hover transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Delivered</p>
                    <p class="text-2xl font-bold text-green-600">{{ $delivered }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 card-hover transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $pending }}</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition">
            <h3 class="font-semibold text-lg mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('shipments.create') }}" class="bg-teal-500 hover:bg-teal-600 text-white text-center py-3 rounded-lg transition">
                    <i class="fas fa-plus-circle block text-xl mb-1"></i>
                    New Shipment
                </a>
                <a href="{{ route('tracking.page') }}" class="bg-blue-500 hover:bg-blue-600 text-white text-center py-3 rounded-lg transition">
                    <i class="fas fa-search block text-xl mb-1"></i>
                    Track Shipment
                </a>
                <a href="{{ route('grocery.box') }}" class="bg-green-500 hover:bg-green-600 text-white text-center py-3 rounded-lg transition">
                    <i class="fas fa-shopping-bag block text-xl mb-1"></i>
                    Grocery Box
                </a>
                <a href="{{ route('client.feedback') }}" class="bg-purple-500 hover:bg-purple-600 text-white text-center py-3 rounded-lg transition">
                    <i class="fas fa-comment block text-xl mb-1"></i>
                    Feedback
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition">
            <h3 class="font-semibold text-lg mb-4">Recent Activity</h3>
            @php
                $recentShipments = Auth::user()->shipmentsAsCustomer ? Auth::user()->shipmentsAsCustomer->take(5) : collect();
            @endphp
            @if($recentShipments->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($recentShipments as $shipment)
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <p class="font-medium text-sm">{{ $shipment->tracking_number ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst($shipment->status ?? 'Pending') }}</p>
                            </div>
                            <span class="text-xs text-gray-500">{{ $shipment->created_at ? $shipment->created_at->diffForHumans() : 'N/A' }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2 block"></i>
                    <p>No recent shipments found</p>
                    <a href="{{ route('shipments.create') }}" class="text-teal-500 hover:underline text-sm">Create your first shipment</a>
                </div>
            @endif
        </div>
    </div>

    <!-- Account Information -->
    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition">
        <h3 class="font-semibold text-lg mb-4">Account Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Full Name</p>
                <p class="font-medium">{{ Auth::user()->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p class="font-medium">{{ Auth::user()->email }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Phone</p>
                <p class="font-medium">{{ Auth::user()->phone ?? 'Not provided' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Account Type</p>
                <p class="font-medium capitalize">{{ Auth::user()->user_type ?? 'Customer' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection