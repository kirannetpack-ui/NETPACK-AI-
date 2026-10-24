@extends('layouts.app')

@section('title', 'Financial Reports')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Financial Reports</h1>
                <p class="text-sm text-gray-500 mt-1">View financial summary and revenue breakdown</p>
            </div>
            <a href="{{ route('admin.reports') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>

        <div class="p-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total Revenue</p>
                    <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($summary['total_revenue'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Shipping Cost</p>
                    <p class="text-2xl font-bold text-blue-600">Rs. {{ number_format($summary['total_shipping_cost'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Handling Fee</p>
                    <p class="text-2xl font-bold text-purple-600">Rs. {{ number_format($summary['total_handling_fee'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-orange-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Insurance Fee</p>
                    <p class="text-2xl font-bold text-orange-600">Rs. {{ number_format($summary['total_insurance_fee'] ?? 0, 2) }}</p>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Monthly Revenue</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Month</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Revenue</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monthlyRevenue as $revenue)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3">{{ date('F Y', mktime(0, 0, 0, $revenue->month, 1, $revenue->year)) }}</td>
                                    <td class="py-2 px-3 font-medium">Rs. {{ number_format($revenue->total, 2) }}</td>
                                    <td class="py-2 px-3">
                                        <span class="text-sm text-gray-500">{{ $revenue->total > 0 ? '✅' : '❌' }}</span>
                                    </td>
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
</div>
@endsection