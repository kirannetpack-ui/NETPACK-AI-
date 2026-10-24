@extends('layouts.app')

@section('title', 'Manifests')
@section('page-title', '📦 Manifests')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Domestic Manifests</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your manifests and shipments</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('domestic.manifests.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                    <i class="fas fa-plus mr-2"></i> New Manifest
                </a>
                <a href="{{ route('domestic.manifests.pods') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-file-signature mr-2"></i> PODs
                </a>
            </div>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-600">Total Manifests</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border-l-4 border-purple-500">
                    <p class="text-sm text-gray-600">In Transit</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['in_transit'] ?? 0 }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-500">
                    <p class="text-sm text-gray-600">Delivered</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['delivered'] ?? 0 }}</p>
                </div>
            </div>

            <!-- Search -->
            <form method="GET" class="flex flex-wrap gap-3 mb-6">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by manifest number, origin, destination..." 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <select name="status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                        <option value="dispatched" {{ request('status') === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </div>
                @if(request('search') || request('status'))
                    <div>
                        <a href="{{ route('domestic.manifests.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                            <i class="fas fa-undo mr-2"></i> Reset
                        </a>
                    </div>
                @endif
            </form>

            <!-- Manifests Table -->
            @if($manifests->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Manifest #</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Load Type</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Bags</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Shipments</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Partner</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($manifests as $manifest)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-mono text-sm">{{ $manifest->manifest_number }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $manifest->load_type === 'consolidated' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ ucfirst(str_replace('_', ' ', $manifest->load_type)) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">{{ $manifest->total_bags }}</td>
                                    <td class="py-3 px-4">{{ $manifest->total_shipments }}</td>
                                    <td class="py-3 px-4">{{ $manifest->partner->name ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $manifest->status_badge }}">
                                            {{ $manifest->status_label }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm">{{ $manifest->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('domestic.manifests.show', $manifest->id) }}" 
                                           class="text-blue-600 hover:text-blue-800" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $manifests->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-boxes text-5xl text-gray-300 mb-4 block"></i>
                    <h3 class="text-lg font-semibold text-gray-700">No Manifests Found</h3>
                    <p class="text-gray-500 mt-2">Create your first manifest to start shipping.</p>
                    <a href="{{ route('domestic.manifests.create') }}" class="inline-block mt-4 bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-plus mr-2"></i> Create New Manifest
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection