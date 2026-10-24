{{-- resources/views/admin/products/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - COURIER with NETPACK Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="np-system np-legacy-screen bg-gray-100">
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-6 py-3">
            <div class="flex justify-between items-center">
                <div class="text-xl font-bold text-teal-600">
                    COURIER with NETPACK Admin Panel
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-teal-600">Dashboard</a>
                    <a href="{{ route('grocery.box') }}" class="text-gray-600 hover:text-teal-600">Store</a>
                    <span>{{ auth()->user()->name }}</span>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-6 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Products Management</h1>
            <a href="{{ route('admin.products.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Weight</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($products ?? [] as $product)
                    <tr>
                        <td class="px-6 py-4">{{ $product->name }}</td>
                        <td class="px-6 py-4">{{ $product->sku }}</td>
                        <td class="px-6 py-4">{{ $product->weight_kg }} kg</td>
                        <td class="px-6 py-4">रू {{ number_format($product->price_npr, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($product->is_active)
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Active</span>
                            @else
                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteProduct({{ $product->id }})" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No products found. Click "Add Product" to get started.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
    function deleteProduct(id) {
        if (confirm('Are you sure?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/products/${id}`;
            form.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>
