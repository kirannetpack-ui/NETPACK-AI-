@extends('layouts.app')

@section('title', 'Partner Charges')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Partner Charges</h1>
                <p class="text-sm text-gray-500 mt-1">Review and verify partner charges for shipments</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.partner-charges.export') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-file-export mr-2"></i> Export
                </a>
            </div>
        </div>

        <div class="p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Total Charges</p>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total']) }}</p>
                        </div>
                        <i class="fas fa-file-invoice text-blue-500 text-2xl"></i>
                    </div>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Pending Review</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pending']) }}</p>
                        </div>
                        <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                    </div>
                </div>
                <div class="bg-red-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Disputed</p>
                            <p class="text-2xl font-bold text-red-600">{{ number_format($stats['disputed']) }}</p>
                        </div>
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                    </div>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Approved</p>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($stats['approved']) }}</p>
                        </div>
                        <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select name="status" class="w-full border rounded-lg px-3 py-2">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="disputed" {{ request('status') === 'disputed' ? 'selected' : '' }}>Disputed</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Partner</label>
                    <select name="partner_id" class="w-full border rounded-lg px-3 py-2">
                        <option value="">All Partners</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 w-full">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                </div>
            </form>

            <!-- Charges Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">#</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Shipment</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Partner</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Total Charge</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Difference</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Submitted</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($charges as $charge)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4">{{ $charge->id }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-medium">{{ $charge->shipment_reference ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $charge->shipment->tracking_number ?? 'N/A' }}</div>
                                </td>
                                <td class="py-3 px-4">{{ $charge->partner->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4 font-bold">Rs. {{ number_format($charge->total_charge, 2) }}</td>
                                <td class="py-3 px-4">
                                    @if($charge->charge_difference)
                                        <span class="text-{{ $charge->charge_difference > 0 ? 'red' : 'green' }}-600">
                                            {{ $charge->charge_difference > 0 ? '+' : '' }}{{ number_format($charge->charge_difference, 2) }}
                                            ({{ number_format($charge->charge_percentage_difference, 1) }}%)
                                        </span>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $charge->status_color }}-100 text-{{ $charge->status_color }}-800">
                                        {{ $charge->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-sm">{{ $charge->submitted_at ? $charge->submitted_at->format('M d, Y') : 'N/A' }}</td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('admin.partner-charges.show', $charge->id) }}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-file-invoice text-4xl block mb-2"></i>
                                    No partner charges found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $charges->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection