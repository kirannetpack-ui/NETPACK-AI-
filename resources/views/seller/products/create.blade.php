@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Add New Product</h1>
                <p class="text-sm text-gray-500 mt-1">Create a new product listing</p>
            </div>
            <a href="{{ route('seller.products.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>

        <div class="p-6">
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('seller.products.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea name="description" rows="3" 
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Category <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">Select Category</option>
                            <option value="Groceries" {{ old('category') === 'Groceries' ? 'selected' : '' }}>🛒 Groceries</option>
                            <option value="Beverages" {{ old('category') === 'Beverages' ? 'selected' : '' }}>🥤 Beverages</option>
                            <option value="Snacks" {{ old('category') === 'Snacks' ? 'selected' : '' }}>🍿 Snacks</option>
                            <option value="Utilities" {{ old('category') === 'Utilities' ? 'selected' : '' }}>🔧 Utilities</option>
                            <option value="Electronics" {{ old('category') === 'Electronics' ? 'selected' : '' }}>📱 Electronics</option>
                            <option value="Clothing" {{ old('category') === 'Clothing' ? 'selected' : '' }}>👕 Clothing</option>
                            <option value="Home" {{ old('category') === 'Home' ? 'selected' : '' }}>🏠 Home</option>
                            <option value="Other" {{ old('category') === 'Other' ? 'selected' : '' }}>📦 Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">SKU <span class="text-red-500">*</span></label>
                        <input type="text" name="sku" value="{{ old('sku') }}" required 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <p class="text-xs text-gray-500 mt-1">Unique product identifier</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Price (NPR) <span class="text-red-500">*</span></label>
                        <input type="number" name="price_npr" step="0.01" value="{{ old('price_npr') }}" required 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Stock Quantity <span class="text-red-500">*</span></label>
                        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" required 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Product Images</label>
                        <input type="file" name="images[]" multiple accept="image/*" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <p class="text-xs text-gray-500 mt-1">Upload up to 5 images (JPG, PNG, WEBP)</p>
                    </div>

                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            <span class="text-sm font-medium">Active (visible to customers)</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t mt-6">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-save mr-2"></i> Create Product
                    </button>
                    <a href="{{ route('seller.products.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection