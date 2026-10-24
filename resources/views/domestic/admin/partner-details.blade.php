@extends('layouts.app')

@section('title', 'Partner Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Partner Details</h1>
                <p class="text-sm text-gray-500 mt-1">View partner information</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.partners.edit', $partner->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <a href="{{ route('domestic.partners') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Back
                </a>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="font-medium">{{ $partner->name }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Company</p>
                    <p class="font-medium">{{ $partner->company_name ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium">{{ $partner->email }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="font-medium">{{ $partner->phone ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">City</p>
                    <p class="font-medium">{{ $partner->city ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">District</p>
                    <p class="font-medium">{{ $partner->district ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Province</p>
                    <p class="font-medium">{{ $partner->province ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Status</p>
                    <p class="font-medium">
                        @if($partner->verification_status === 'approved')
                            <span class="text-green-600">Active</span>
                        @elseif($partner->verification_status === 'pending')
                            <span class="text-yellow-600">Pending</span>
                        @elseif($partner->verification_status === 'suspended')
                            <span class="text-red-600">Suspended</span>
                        @else
                            <span class="text-gray-600">{{ ucfirst($partner->verification_status) }}</span>
                        @endif
                    </p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Shipments</p>
                    <p class="font-medium">{{ number_format($stats['total_shipments'] ?? 0) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Delivered Shipments</p>
                    <p class="font-medium">{{ number_format($stats['delivered_shipments'] ?? 0) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Pickups</p>
                    <p class="font-medium">{{ number_format($stats['total_pickups'] ?? 0) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Rates</p>
                    <p class="font-medium">{{ number_format($stats['total_rates'] ?? 0) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Zones</p>
                    <p class="font-medium">{{ number_format($stats['total_zones'] ?? 0) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Created At</p>
                    <p class="font-medium">{{ $partner->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Last Updated</p>
                    <p class="font-medium">{{ $partner->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection