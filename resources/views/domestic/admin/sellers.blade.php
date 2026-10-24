@extends('layouts.app')

@section('title', 'Sellers')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">E-commerce Sellers</h1>
            <p class="text-sm text-gray-500 mt-1">Manage sellers and their stores</p>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Seller</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Store</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Email</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Products</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Orders</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sellers as $seller)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4">
                                    <div class="font-medium">{{ $seller->name }}</div>
                                </td>
                                <td class="py-3 px-4">{{ $seller->business_name ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm">{{ $seller->email }}</td>
                                <td class="py-3 px-4">{{ $seller->products ? $seller->products->count() : 0 }}</td>
                                <td class="py-3 px-4">{{ $seller->orders ? $seller->orders->count() : 0 }}</td>
                                <td class="py-3 px-4">
                                    @if($seller->verification_status === 'approved')
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Active</span>
                                    @elseif($seller->verification_status === 'pending')
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium">Pending</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">{{ ucfirst($seller->verification_status) }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('domestic.sellers.show', $seller->id) }}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-store text-4xl block mb-2"></i>
                                    No sellers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $sellers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection