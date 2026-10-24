@extends('layouts.app')

@section('title', 'Shipments')
@section('page-title', '📦 Shipments')

@section('content')
<div class="max-w-7xl mx-auto">
    @php
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $isDomesticAdmin = $user->isDomesticAdmin();
        $isInternationalAdmin = $user->isInternationalAdmin();
        $isSeller = $user->isSeller();
        $isRider = $user->isRider();
        $isPartner = $user->isPartner();
        $isCustomer = $user->isCustomer();
    @endphp

    <!-- Role Banner -->
    <div class="mb-4 p-3 rounded-lg border 
        {{ $isSuperAdmin ? 'bg-purple-50 border-purple-200' : 
           ($isDomesticAdmin ? 'bg-blue-50 border-blue-200' : 
           ($isInternationalAdmin ? 'bg-indigo-50 border-indigo-200' : 
           ($isRider ? 'bg-yellow-50 border-yellow-200' : 
           ($isSeller ? 'bg-green-50 border-green-200' : 
           'bg-gray-50 border-gray-200')))) }}">
        <p class="text-sm 
            {{ $isSuperAdmin ? 'text-purple-700' : 
               ($isDomesticAdmin ? 'text-blue-700' : 
               ($isInternationalAdmin ? 'text-indigo-700' : 
               ($isRider ? 'text-yellow-700' : 
               ($isSeller ? 'text-green-700' : 
               'text-gray-700')))) }}">
            <i class="fas 
                {{ $isSuperAdmin ? 'fa-crown' : 
                   ($isDomesticAdmin ? 'fa-truck' : 
                   ($isInternationalAdmin ? 'fa-globe' : 
                   ($isRider ? 'fa-motorcycle' : 
                   ($isSeller ? 'fa-store' : 
                   'fa-user')))) }} mr-2"></i>
            @if($isSuperAdmin)
                👑 Super Admin: Viewing all shipments across all services
            @elseif($isDomesticAdmin)
                📦 Domestic Admin: Viewing Domestic & E-commerce shipments
            @elseif($isInternationalAdmin)
                🌍 International Admin: Viewing International shipments only
            @elseif($isRider)
                🛵 Rider: Viewing shipments assigned to you
            @elseif($isSeller)
                🏪 Seller: Viewing your shipments
            @elseif($isPartner)
                🤝 Partner: Viewing shipments in your zone
            @elseif($isCustomer)
                👤 Customer: Viewing your shipments
            @else
                📋 Viewing shipments
            @endif
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex flex-wrap justify-between items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">My Shipments</h1>
                <p class="text-sm text-gray-500 mt-1">Track and manage your shipments</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if(!$isRider && !$isPartner && !$isInternationalAdmin)
                    <a href="{{ route('shipments.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-plus mr-2"></i> New Shipment
                    </a>
                @endif
                @if($isSuperAdmin || $isDomesticAdmin || $isInternationalAdmin)
                    <a href="{{ route('shipments.export') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-file-export mr-2"></i> Export
                    </a>
                @endif
            </div>
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
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-600">Total Shipments</p>
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

            <!-- Search & Filter -->
            <form method="GET" class="flex flex-wrap gap-3 mb-6">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by tracking number, receiver..." 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <select name="status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">All Status</option>
                        <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>⚡ Ongoing (Active)</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="out_for_delivery" {{ request('status') === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <!-- Shipment Type Filter (for admins) -->
                @if($isSuperAdmin || $isDomesticAdmin || $isInternationalAdmin)
                    <div>
                        <select name="shipment_type" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">All Types</option>
                            <option value="domestic" {{ request('shipment_type') === 'domestic' ? 'selected' : '' }}>🏠 Domestic</option>
                            <option value="international" {{ request('shipment_type') === 'international' ? 'selected' : '' }}>🌍 International</option>
                            <option value="ecommerce" {{ request('shipment_type') === 'ecommerce' ? 'selected' : '' }}>🛒 E-commerce</option>
                        </select>
                    </div>
                @endif
                <div>
                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </div>
                @if(request('search') || request('status') || request('shipment_type'))
                    <div>
                        <a href="{{ route('shipments.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                            <i class="fas fa-undo mr-2"></i> Reset
                        </a>
                    </div>
                @endif
            </form>

            <!-- Shipments Table -->
            @if($shipments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Tracking #</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">HAWB</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Receiver</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Type</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Weight</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shipments as $shipment)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-mono text-sm font-semibold text-teal-700">{{ $shipment->tracking_number }}</td>
                                    <td class="py-3 px-4 font-mono text-xs text-slate-600">{{ $shipment->hawb_number ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 font-medium">{{ $shipment->receiver_name }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $shipment->shipment_type === 'international' ? 'bg-indigo-100 text-indigo-800' : 
                                               ($shipment->shipment_type === 'ecommerce' ? 'bg-pink-100 text-pink-800' : 
                                               'bg-blue-100 text-blue-800') }}">
                                            {{ ucfirst($shipment->shipment_type ?? 'Domestic') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">{{ number_format($shipment->actual_weight ?? $shipment->chargeable_weight ?? 0, 2) }} kg</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $shipment->status === 'delivered' ? 'bg-green-100 text-green-800' : 
                                               ($shipment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($shipment->status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                               ($shipment->status === 'in_transit' || $shipment->status === 'out_for_delivery' ? 'bg-purple-100 text-purple-800' : 
                                               'bg-blue-100 text-blue-800'))) }}">
                                            {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm">{{ $shipment->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-1.5">
                                            <!-- Details -->
                                            <a href="{{ route('shipments.show', $shipment->id) }}" 
                                               class="p-1.5 rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 hover:text-teal-800 transition" 
                                               title="View Consignment Details">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                            <!-- Radar Tracking -->
                                            <a href="{{ route('tracking.show', $shipment->tracking_number) }}" 
                                               class="p-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-800 transition" 
                                               title="Live Radar Tracking">
                                                <i class="fas fa-location-crosshairs text-xs"></i>
                                            </a>
                                            <!-- HAWB Copy View -->
                                            <a href="{{ $shipment->shipment_type === 'domestic' ? route('hawb.domestic', $shipment->id) : route('hawb.international', $shipment->id) }}" 
                                               target="_blank"
                                               class="p-1.5 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 hover:text-purple-800 transition" 
                                               title="View HAWB Copy">
                                                <i class="fas fa-file-invoice text-xs"></i>
                                            </a>
                                            <!-- Print HAWB Document -->
                                            <a href="{{ route('hawb.print', ['id' => $shipment->id, 'type' => ($shipment->shipment_type === 'domestic' ? 'domestic' : 'international')]) }}" 
                                               target="_blank"
                                               class="p-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 hover:text-slate-900 transition" 
                                               title="Print HAWB Document">
                                                <i class="fas fa-print text-xs"></i>
                                            </a>
                                            @if($shipment->status !== 'delivered' && $shipment->status !== 'cancelled' && ($isSuperAdmin || $isDomesticAdmin || $shipment->customer_id === auth()->id()))
                                                <a href="{{ route('shipments.edit', $shipment->id) }}" 
                                                   class="p-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 hover:text-amber-800 transition" 
                                                   title="Edit Consignment">
                                                    <i class="fas fa-edit text-xs"></i>
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
                    {{ $shipments->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-truck text-5xl text-gray-300 mb-4 block"></i>
                    <h3 class="text-lg font-semibold text-gray-700">No Shipments Found</h3>
                    <p class="text-gray-500 mt-2">
                        @if($isRider || $isPartner)
                            No shipments assigned to you yet.
                        @elseif($isInternationalAdmin)
                            No international shipments found.
                        @elseif($isDomesticAdmin)
                            No domestic or e-commerce shipments found.
                        @else
                            You haven't created any shipments yet.
                        @endif
                    </p>
                    @if(!$isRider && !$isPartner && !$isInternationalAdmin)
                        <a href="{{ route('shipments.create') }}" class="inline-block mt-4 bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-plus mr-2"></i> Create Your First Shipment
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection