@extends('layouts.app')

@section('title', 'My Products')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">📦 My Products</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your products</p>
            </div>
            <a href="{{ route('seller.products.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-plus mr-2"></i> Add Product
            </a>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Search & Filter -->
            <form method="GET" class="flex flex-wrap gap-4 mb-6">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search products..." 
                           class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <select name="status" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </div>
                @if(request('search') || request('status'))
                    <div>
                        <a href="{{ route('seller.products.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                            <i class="fas fa-undo mr-2"></i> Reset
                        </a>
                    </div>
                @endif
            </form>

            <!-- Products Grid -->
            @if($products->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($products as $product)
                        <div class="border rounded-lg overflow-hidden hover:shadow-lg transition bg-white">
                            <div class="h-40 bg-gray-100 flex items-center justify-center">
                                @if($product->images && count($product->images) > 0)
                                    <img src="{{ asset('storage/' . $product->images[0]) }}" 
                                         alt="{{ $product->name }}" 
                                         class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-box text-4xl text-gray-400"></i>
                                @endif
                            </div>
                            <div class="p-4">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-semibold text-gray-800 truncate">{{ $product->name }}</h3>
                                    <span class="px-2 py-1 text-xs rounded-full font-medium 
                                        {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">SKU: {{ $product->sku }}</p>
                                <p class="text-lg font-bold text-teal-600 mt-2">Rs. {{ number_format($product->price_npr, 2) }}</p>
                                <p class="text-sm text-gray-500">Stock: {{ $product->stock_quantity }}</p>
                                <div class="flex gap-2 mt-3 pt-3 border-t">
                                    <a href="{{ route('seller.products.edit', $product->id) }}" 
                                       class="text-teal-600 hover:text-teal-800 text-sm">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                    <a href="{{ route('seller.products.show', $product->id) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                    <form method="POST" action="{{ route('seller.products.toggle-status', $product->id) }}" class="inline">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="text-{{ $product->is_active ? 'red' : 'green' }}-600 hover:text-{{ $product->is_active ? 'red' : 'green' }}-800 text-sm">
                                            <i class="fas fa-{{ $product->is_active ? 'times' : 'check' }} mr-1"></i>
                                            {{ $product->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-box-open text-5xl text-gray-300 mb-4 block"></i>
                    <h3 class="text-lg font-semibold text-gray-700">No Products Found</h3>
                    <p class="text-gray-500 mt-2">Start adding your products to sell.</p>
                    <a href="{{ route('seller.products.create') }}" class="inline-block mt-4 bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-plus mr-2"></i> Add Your First Product
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection