@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Reports Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">View system reports and analytics</p>
        </div>

        <div class="p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Total Users</p>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_users'] ?? 0) }}</p>
                        </div>
                        <i class="fas fa-users text-blue-500 text-2xl"></i>
                    </div>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Total Revenue</p>
                            <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($stats['revenue_total'] ?? 0, 2) }}</p>
                        </div>
                        <i class="fas fa-money-bill-wave text-green-500 text-2xl"></i>
                    </div>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Total Shipments</p>
                            <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_shipments'] ?? 0) }}</p>
                        </div>
                        <i class="fas fa-truck text-purple-500 text-2xl"></i>
                    </div>
                </div>
                <div class="bg-orange-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Pending Users</p>
                            <p class="text-2xl font-bold text-orange-600">{{ number_format($stats['pending_users'] ?? 0) }}</p>
                        </div>
                        <i class="fas fa-clock text-orange-500 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                <a href="{{ route('admin.reports.shipments') }}" class="bg-gray-50 hover:bg-gray-100 rounded-lg p-4 text-center border border-gray-200 transition">
                    <i class="fas fa-truck text-2xl text-blue-600 block mb-2"></i>
                    <span class="font-medium text-gray-700">Shipment Reports</span>
                </a>
                <a href="{{ route('admin.reports.financial') }}" class="bg-gray-50 hover:bg-gray-100 rounded-lg p-4 text-center border border-gray-200 transition">
                    <i class="fas fa-chart-line text-2xl text-green-600 block mb-2"></i>
                    <span class="font-medium text-gray-700">Financial Reports</span>
                </a>
                <a href="{{ route('admin.reports.partners') }}" class="bg-gray-50 hover:bg-gray-100 rounded-lg p-4 text-center border border-gray-200 transition">
                    <i class="fas fa-handshake text-2xl text-purple-600 block mb-2"></i>
                    <span class="font-medium text-gray-700">Partner Reports</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection