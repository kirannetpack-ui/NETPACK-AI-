@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Products</h1>
            <p class="text-sm text-gray-500 mt-1">Manage all products</p>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Search -->
            <form method="GET" class="flex gap-2 mb-4">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search products..." 
                       class="flex-1 border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Product</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">SKU</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Seller</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Price</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Stock</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4">
                                    <div class="font-medium">{{ $product->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $product->category }}</div>
                                </td>
                                <td class="py-3 px-4 text-sm font-mono">{{ $product->sku }}</td>
                                <td class="py-3 px-4">{{ $product->user->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4 font-medium">Rs. {{ number_format($product->price_npr, 2) }}</td>
                                <td class="py-3 px-4">{{ $product->stock_quantity }}</td>
                                <td class="py-3 px-4">
                                    @if($product->is_active)
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Active</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <a href="#" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-box text-4xl block mb-2"></i>
                                    No products found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection