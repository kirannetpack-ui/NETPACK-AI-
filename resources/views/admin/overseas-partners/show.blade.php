@extends('layouts.app')

@section('title', 'Overseas Partner Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Overseas Partner Details</h1>
                <p class="text-sm text-gray-500">View international partner information</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.overseas-partners.edit', $partner->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <a href="{{ route('admin.overseas-partners.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Back
                </a>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Company Name</p>
                    <p class="font-medium">{{ $partner->company_name ?? $partner->name }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Contact Person</p>
                    <p class="font-medium">{{ $partner->contact_person ?? $partner->name }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium">{{ $partner->email }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="font-medium">{{ $partner->phone }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Country</p>
                    <p class="font-medium">{{ $partner->country ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">City</p>
                    <p class="font-medium">{{ $partner->city ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Address</p>
                    <p class="font-medium">{{ $partner->address ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Margin Percentage</p>
                    <p class="font-medium">{{ $partner->margin_percentage ?? 0 }}%</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Status</p>
                    @if($partner->verification_status === 'approved')
                        <span class="text-green-600 font-medium">Active</span>
                    @elseif($partner->verification_status === 'pending')
                        <span class="text-yellow-600 font-medium">Pending</span>
                    @elseif($partner->verification_status === 'rejected')
                        <span class="text-red-600 font-medium">Rejected</span>
                    @else
                        <span class="text-gray-600 font-medium">{{ ucfirst($partner->verification_status ?? 'Unknown') }}</span>
                    @endif
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