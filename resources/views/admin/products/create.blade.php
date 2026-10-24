{{-- resources/views/admin/products/create.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - COURIER with NETPACK Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="np-system np-legacy-screen bg-gray-100">
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-6 py-3">
            <div class="flex justify-between items-center">
                <div class="text-xl font-bold text-teal-600">COURIER with NETPACK Admin</div>
                <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-teal-600">Dashboard</a>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-6 py-8">
        <h1 class="text-2xl font-bold mb-6">Add New Product</h1>
        
        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="{{ route('admin.products.store') }}">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Product Name *</label>
                        <input type="text" name="name" required class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">SKU *</label>
                        <input type="text" name="sku" required class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Weight (kg) *</label>
                        <input type="number" step="0.01" name="weight_kg" required class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Price (NPR) *</label>
                        <input type="number" step="0.01" name="price_npr" required class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Category</label>
                        <input type="text" name="category" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Origin Country</label>
                        <input type="text" name="origin_country" value="Nepal" class="w-full px-4 py-2 border rounded-lg">
                    </div>
<div>
    <label class="block text-sm font-medium mb-2">Product Image</label>
    <input type="file" name="image" accept="image/*" class="w-full px-4 py-2 border rounded-lg">
    @error('image')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2">Description</label>
                        <textarea name="description" rows="3" class="w-full px-4 py-2 border rounded-lg"></textarea>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700">
                        Save Product
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="ml-3 bg-gray-300 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-400">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
