@extends('layouts.app')

@section('title', 'Seller Settings')
@section('page-title', 'Settings')

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
    <a href="{{ route('seller.wallet') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-wallet w-5"></i>
        <span>Wallet</span>
    </a>
    <a href="{{ route('ecommerce.seller.create') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 text-sm text-teal-200/80 hover:text-white">
        <i class="fas fa-plus-circle w-5"></i>
        <span>Add Product</span>
    </a>
    <a href="{{ route('seller.settings') }}" class="sidebar-link active flex items-center space-x-3 px-4 py-3 text-sm text-white">
        <i class="fas fa-cog w-5"></i>
        <span>Settings</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-800">Seller Settings</h2>
        <p class="text-gray-500 mt-1">Manage your seller account settings</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-lg mb-4">Business Information</h3>
            <form action="#" method="POST">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                        <input type="text" placeholder="Your store name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Address</label>
                        <input type="text" placeholder="Business address" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PAN Number</label>
                        <input type="text" placeholder="PAN Number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </div>
                    <button type="submit" class="w-full bg-teal-500 hover:bg-teal-600 text-white font-semibold py-2 rounded-lg transition">
                        <i class="fas fa-save mr-2"></i> Update Business Info
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-lg mb-4">Bank Details</h3>
            <form action="{{ route('seller.update-bank') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                        <input type="text" name="bank_name" placeholder="Bank name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                        <input type="text" name="account_number" placeholder="Account number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Account Holder Name</label>
                        <input type="text" name="account_holder" placeholder="Account holder name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </div>
                    <button type="submit" class="w-full bg-teal-500 hover:bg-teal-600 text-white font-semibold py-2 rounded-lg transition">
                        <i class="fas fa-save mr-2"></i> Update Bank Details
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection