@extends('layouts.app')

@section('title', 'COD Settlements')
@section('page-title', '💰 COD Settlements')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">💰 COD Settlements</h1>
                <p class="text-sm text-gray-500 mt-1">Manage Cash on Delivery settlements</p>
            </div>
            <a href="{{ route('admin.cod-settlements.export') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-file-export mr-2"></i> Export Report
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

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total Settlements</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total'] ?? 0) }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pending'] ?? 0) }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Processing</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['processing'] ?? 0) }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($stats['completed'] ?? 0) }}</p>
                </div>
                <div class="bg-teal-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total Amount</p>
                    <p class="text-2xl font-bold text-teal-600">Rs. {{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" class="flex flex-wrap gap-3 mb-6">
                <div>
                    <select name="status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                </div>
                <div>
                    <a href="{{ route('admin.cod-settlements.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                        <i class="fas fa-undo mr-2"></i> Reset
                    </a>
                </div>
            </form>

            <!-- Table -->
            @if($settlements->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Settlement</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Order</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Seller</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">COD Amount</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settlements as $settlement)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-mono text-sm">#{{ $settlement->id }}</td>
                                    <td class="py-3 px-4">#{{ $settlement->order->order_number ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">{{ $settlement->seller->name ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 font-medium">Rs. {{ number_format($settlement->cod_amount, 2) }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $settlement->status_badge }}">
                                            {{ $settlement->status_label }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm">{{ $settlement->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex gap-2">
                                            <a href="{{ route('admin.cod-settlements.show', $settlement->id) }}" 
                                               class="text-blue-600 hover:text-blue-800" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($settlement->settlement_status !== 'completed')
                                                <a href="{{ route('admin.cod-settlements.show', $settlement->id) }}" 
                                                   class="text-teal-600 hover:text-teal-800" title="Update Status">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $settlements->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-money-bill-wave text-5xl text-gray-300 mb-4 block"></i>
                    <h3 class="text-lg font-semibold text-gray-700">No COD Settlements</h3>
                    <p class="text-gray-500 mt-2">No COD settlements found in the system.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection