@extends('layouts.app')

@section('title', 'Seller Earnings')
@section('page-title', 'My Earnings')

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
    <a href="{{ route('seller.earnings') }}" class="sidebar-link active flex items-center space-x-3 px-4 py-3 text-sm text-white">
        <i class="fas fa-money-bill-wave w-5"></i>
        <span>Earnings</span>
    </a>
    <a href="{{ route('seller.wallet') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
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
        <h2 class="text-2xl font-bold text-gray-800">My Earnings</h2>
        <p class="text-gray-500 mt-1">Track your sales and commission earnings</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-gray-500 text-sm">Total Earnings</p>
            <p class="text-2xl font-bold text-green-600">Rs. 0.00</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-gray-500 text-sm">This Month</p>
            <p class="text-2xl font-bold text-blue-600">Rs. 0.00</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-gray-500 text-sm">Pending Payout</p>
            <p class="text-2xl font-bold text-yellow-600">Rs. 0.00</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-lg mb-4">Earnings Breakdown</h3>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-chart-bar text-4xl mb-2 block"></i>
            <p>No earnings data available</p>
            <p class="text-sm">Your earnings will appear once you start selling</p>
        </div>
    </div>
</div>
@endsection