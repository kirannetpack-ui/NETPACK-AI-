@extends('layouts.app')

@section('title', 'Seller Products')
@section('page-title', 'My Products')

@section('sidebar')
    <a href="{{ route('seller.dashboard') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-home w-5"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('seller.products') }}" class="sidebar-link active flex items-center space-x-3 px-4 py-3 text-sm text-white">
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
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">My Products</h2>
                <p class="text-gray-500 mt-1">Manage your product listings</p>
            </div>
            <a href="{{ route('ecommerce.seller.create') }}" class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i> Add Product
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-box-open text-4xl mb-2 block"></i>
            <p>No products yet</p>
            <a href="{{ route('ecommerce.seller.create') }}" class="text-teal-500 hover:underline">Add your first product</a>
        </div>
    </div>
</div>
@endsection