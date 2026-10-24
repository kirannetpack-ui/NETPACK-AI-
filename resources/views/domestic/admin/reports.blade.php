@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Domestic & E-commerce Reports</h1>
            <p class="text-sm text-gray-500 mt-1">View combined reports</p>
        </div>

        <div class="p-6">
            <!-- Domestic Stats -->
            <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Domestic Services</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total Shipments</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($reports['domestic_shipments'] ?? 0) }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Revenue</p>
                    <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($reports['domestic_revenue'] ?? 0, 2) }}</p>
                </div>
            </div>

            <!-- E-commerce Stats -->
            <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">E-commerce Services</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-purple-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total Orders</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($reports['ecommerce_orders'] ?? 0) }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Revenue</p>
                    <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($reports['ecommerce_revenue'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-pink-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Products</p>
                    <p class="text-2xl font-bold text-pink-600">{{ number_format($reports['ecommerce_products'] ?? 0) }}</p>
                </div>
            </div>

            <!-- Top Products -->
            <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Top Products</h3>
            <div class="overflow-x-auto mb-6">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Product</th>
                            <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Category</th>
                            <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports['top_products'] ?? [] as $product)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3">{{ $product->name }}</td>
                                <td class="py-2 px-3">{{ $product->category }}</td>
                                <td class="py-2 px-3">{{ number_format($product->sales_count ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-gray-500">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Top Sellers -->
            <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Top Sellers</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Seller</th>
                            <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Store</th>
                            <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports['top_sellers'] ?? [] as $seller)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3">{{ $seller->name }}</td>
                                <td class="py-2 px-3">{{ $seller->business_name ?? 'N/A' }}</td>
                                <td class="py-2 px-3">{{ number_format($seller->orders_count ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-gray-500">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection