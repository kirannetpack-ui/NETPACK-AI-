@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Product Details</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $product->name }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('seller.products.edit', $product->id) }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <a href="{{ route('seller.products.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <!-- Product Images -->
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h3 class="font-semibold text-gray-700 mb-2">Images</h3>
                        @if($product->images && count($product->images) > 0)
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($product->images as $image)
                                    <div class="border rounded-lg overflow-hidden h-32">
                                        <img src="{{ asset('storage/' . $image) }}" alt="Product image" class="w-full h-full object-cover">
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-image text-4xl block mb-2"></i>
                                <p>No images uploaded</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <div class="border rounded-lg p-4">
                        <h3 class="font-semibold text-gray-700 mb-2">Product Information</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between border-b py-1">
                                <dt class="text-gray-500">Name</dt>
                                <dd class="font-medium">{{ $product->name }}</dd>
                            </div>
                            <div class="flex justify-between border-b py-1">
                                <dt class="text-gray-500">SKU</dt>
                                <dd class="font-mono text-sm">{{ $product->sku }}</dd>
                            </div>
                            <div class="flex justify-between border-b py-1">
                                <dt class="text-gray-500">Category</dt>
                                <dd>{{ $product->category }}</dd>
                            </div>
                            <div class="flex justify-between border-b py-1">
                                <dt class="text-gray-500">Price</dt>
                                <dd class="font-bold text-teal-600">Rs. {{ number_format($product->price_npr, 2) }}</dd>
                            </div>
                            <div class="flex justify-between border-b py-1">
                                <dt class="text-gray-500">Stock</dt>
                                <dd>{{ $product->stock_quantity }}</dd>
                            </div>
                            <div class="flex justify-between border-b py-1">
                                <dt class="text-gray-500">Status</dt>
                                <dd>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium 
                                        {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between border-b py-1">
                                <dt class="text-gray-500">Created</dt>
                                <dd>{{ $product->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                            <div class="flex justify-between py-1">
                                <dt class="text-gray-500">Last Updated</dt>
                                <dd>{{ $product->updated_at->diffForHumans() }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="border rounded-lg p-4">
                        <h3 class="font-semibold text-gray-700 mb-2">Description</h3>
                        <p class="text-gray-600">{{ $product->description ?? 'No description provided.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection