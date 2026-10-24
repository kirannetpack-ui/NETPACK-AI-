@extends('layouts.app')

@section('title', 'Wallet')
@section('page-title', 'My Wallet')

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
    <a href="{{ route('client.wallet') }}" class="sidebar-link active flex items-center space-x-3 px-4 py-3 text-sm text-white">
        <i class="fas fa-wallet w-5"></i>
        <span>Wallet</span>
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
    <!-- Page Header -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-800">My Wallet</h2>
        <p class="text-gray-500 mt-1">Manage your balance and transaction history</p>
    </div>

    <!-- Wallet Balance -->
    <div class="gradient-bg rounded-2xl p-6 text-white">
        <div class="flex flex-wrap justify-between items-center">
            <div>
                <p class="text-teal-200 text-sm">Available Balance</p>
                <h2 class="text-4xl font-bold mt-1">Rs. 0.00</h2>
                <p class="text-teal-200/70 text-sm mt-1">Last updated: Today</p>
            </div>
            <div class="flex space-x-3 mt-4 md:mt-0">
                <button class="bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i> Add Funds
                </button>
                <button class="bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-lg transition">
                    <i class="fas fa-arrow-right mr-2"></i> Withdraw
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-gray-500 text-sm">Total Deposits</p>
            <p class="text-2xl font-bold text-green-600">Rs. 0.00</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-gray-500 text-sm">Total Withdrawals</p>
            <p class="text-2xl font-bold text-red-600">Rs. 0.00</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-gray-500 text-sm">Total Transactions</p>
            <p class="text-2xl font-bold text-blue-600">0</p>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-lg mb-4">Recent Transactions</h3>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox text-4xl mb-2 block"></i>
            <p>No transactions yet</p>
            <p class="text-sm">Your transaction history will appear here</p>
        </div>
    </div>
</div>
@endsection