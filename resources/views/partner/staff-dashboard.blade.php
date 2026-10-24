{{-- resources/views/partner/staff-dashboard.blade.php --}}
@extends('layouts.partner')

@section('title', 'Staff Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl shadow-lg p-6 mb-8 text-white">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Welcome, {{ $staff->name }}! 👋</h1>
                <p class="text-purple-100 mt-1">{{ ucfirst($staff->position) }} at {{ $partner->name }}</p>
            </div>
            <div class="text-center">
                <i class="fas fa-qrcode text-4xl opacity-75"></i>
                <p class="text-sm mt-1">Scan QR to update status</p>
            </div>
        </div>
    </div>
    
    <!-- Staff Permissions Card -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-3">Your Permissions</h3>
        <div class="flex flex-wrap gap-3">
            @if($staff->can_scan_arrival)
                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                    <i class="fas fa-check-circle mr-1"></i> Can Mark Arrival
                </span>
            @endif
            @if($staff->can_scan_departure)
                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">
                    <i class="fas fa-check-circle mr-1"></i> Can Mark Departure
                </span>
            @endif
            @if($staff->can_scan_delivery)
                <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm">
                    <i class="fas fa-check-circle mr-1"></i> Can Mark Delivery
                </span>
            @endif
            @if($staff->can_add_notes)
                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">
                    <i class="fas fa-check-circle mr-1"></i> Can Add Notes
                </span>
            @endif
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Today's Scans</p>
                    <p class="text-2xl font-bold text-teal-600">{{ $stats['today_scans'] }}</p>
                </div>
                <i class="fas fa-qrcode text-3xl text-teal-500"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Arrived at Partner</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['pending_deliveries'] }}</p>
                </div>
                <i class="fas fa-inbox text-3xl text-purple-500"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Out for Delivery</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['out_for_delivery'] }}</p>
                </div>
                <i class="fas fa-truck text-3xl text-orange-500"></i>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <a href="{{ route('partner.staff.scan') }}" class="bg-gradient-to-r from-teal-600 to-emerald-600 rounded-xl p-6 text-white text-center hover:shadow-lg transition">
            <i class="fas fa-qrcode text-3xl mb-2"></i>
            <h3 class="text-lg font-bold">Scan QR Code</h3>
            <p class="text-teal-100 text-sm mt-1">Update shipment status by scanning QR</p>
        </a>
        <a href="{{ route('partner.staff.deliveries') }}" class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl p-6 text-white text-center hover:shadow-lg transition">
            <i class="fas fa-list text-3xl mb-2"></i>
            <h3 class="text-lg font-bold">View Deliveries</h3>
            <p class="text-purple-100 text-sm mt-1">Track all your assigned deliveries</p>
        </a>
    </div>
    
    <!-- Today's Deliveries -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="font-bold text-lg flex items-center gap-2">
                <i class="fas fa-calendar-day text-teal-600"></i>
                <span>Today's Deliveries</span>
            </h2>
        </div>
        <div class="divide-y">
            @forelse($todaysDeliveries as $delivery)
            <div class="px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-medium">Order #{{ $delivery->id }}</p>
                        <p class="text-sm text-gray-500">{{ $delivery->pickup_address }} → {{ $delivery->delivery_address }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($delivery->status == 'delivered') bg-green-100 text-green-800
                            @elseif($delivery->status == 'out_for_delivery') bg-blue-100 text-blue-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                        </span>
                        @if($delivery->status != 'delivered')
                        <a href="{{ route('partner.staff.scan') }}" class="block text-teal-600 text-sm mt-1">Scan to update →</a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-500">No deliveries scheduled for today</div>
            @endforelse
        </div>
    </div>
</div>
@endsection