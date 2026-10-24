@extends('layouts.app')

@section('title', 'Seller Wallet')
@section('page-title', 'My Wallet')

@section('sidebar')
    <a href="{{ route('seller.dashboard') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-home w-5"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('seller.products') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-boxes w-5"></i>
        <span>Products</span>
    </a>
    <a href="{{ route('ecommerce.seller.orders') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-shopping-cart w-5"></i>
        <span>Orders</span>
    </a>
    <a href="{{ route('seller.earnings') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-money-bill-wave w-5"></i>
        <span>Earnings</span>
    </a>
    <a href="{{ route('seller.wallet') }}" class="sidebar-link active flex items-center space-x-3 px-4 py-3 text-sm text-white">
        <i class="fas fa-wallet w-5"></i>
        <span>Wallet</span>
    </a>
    <a href="{{ route('ecommerce.seller.create') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-plus-circle w-5"></i>
        <span>Add Product</span>
    </a>
    <a href="{{ route('seller.settings') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-cog w-5"></i>
        <span>Settings</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-800">My Wallet</h2>
        <p class="text-gray-500 mt-1">Manage your seller wallet and payments</p>
    </div>

    <div class="gradient-bg rounded-2xl p-6 text-white">
        <div class="flex flex-wrap justify-between items-center">
            <div>
                <p class="text-teal-200 text-sm">Available Balance</p>
                <h2 class="text-4xl font-bold mt-1">Rs. 0.00</h2>
            </div>
            <button class="bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-lg transition">
                <i class="fas fa-arrow-right mr-2"></i> Withdraw Funds
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-lg mb-4">Payment History</h3>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-history text-4xl mb-2 block"></i>
            <p>No payment history</p>
            <p class="text-sm">Your payment history will appear here</p>
        </div>
    </div>
</div>
@endsection