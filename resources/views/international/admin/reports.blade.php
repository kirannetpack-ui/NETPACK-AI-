@extends('layouts.app')

@section('title', 'International Reports')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">International Reports</h1>
            <p class="text-sm text-gray-500 mt-1">View international service reports and analytics</p>
        </div>

        <div class="p-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Total Shipments</p>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($reports['total_shipments'] ?? 0) }}</p>
                        </div>
                        <i class="fas fa-ship text-blue-500 text-2xl"></i>
                    </div>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Total Revenue</p>
                            <p class="text-2xl font-bold text-green-600">${{ number_format($reports['total_revenue'] ?? 0, 2) }}</p>
                        </div>
                        <i class="fas fa-dollar-sign text-green-500 text-2xl"></i>
                    </div>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Countries Served</p>
                            <p class="text-2xl font-bold text-purple-600">{{ count($reports['shipments_by_country'] ?? []) }}</p>
                        </div>
                        <i class="fas fa-globe text-purple-500 text-2xl"></i>
                    </div>
                </div>
                <div class="bg-orange-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Avg Revenue/Shipment</p>
                            <p class="text-2xl font-bold text-orange-600">
                                ${{ number_format(($reports['total_shipments'] ?? 0) > 0 ? ($reports['total_revenue'] ?? 0) / ($reports['total_shipments'] ?? 1) : 0, 2) }}
                            </p>
                        </div>
                        <i class="fas fa-chart-line text-orange-500 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Shipments by Country -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Shipments by Country</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Country</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Shipments</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Percentage</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total = $reports['shipments_by_country']->sum('count') ?? 0;
                            @endphp
                            @forelse($reports['shipments_by_country'] ?? [] as $country)
                                @php
                                    $percentage = $total > 0 ? ($country->count / $total) * 100 : 0;
                                @endphp
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-medium">{{ $country->receiver_country }}</td>
                                    <td class="py-3 px-4">{{ number_format($country->count) }}</td>
                                    <td class="py-3 px-4">{{ number_format($percentage, 1) }}%</td>
                                    <td class="py-3 px-4">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-teal-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-gray-500">No country data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Monthly Revenue</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Month</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Revenue</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Trend</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $previousMonth = null;
                            @endphp
                            @forelse($reports['monthly_revenue'] ?? [] as $revenue)
                                @php
                                    $monthName = date('F Y', mktime(0, 0, 0, $revenue->month, 1, $revenue->year));
                                    $trend = $previousMonth ? (($revenue->total - $previousMonth) / $previousMonth) * 100 : 0;
                                    $trendColor = $trend > 0 ? 'text-green-600' : ($trend < 0 ? 'text-red-600' : 'text-gray-500');
                                    $previousMonth = $revenue->total;
                                @endphp
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-medium">{{ $monthName }}</td>
                                    <td class="py-3 px-4 font-bold">${{ number_format($revenue->total, 2) }}</td>
                                    <td class="py-3 px-4">
                                        @if($trend != 0)
                                            <span class="{{ $trendColor }}">
                                                {{ $trend > 0 ? '↑' : '↓' }} {{ number_format(abs($trend), 1) }}%
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-gray-500">No monthly revenue data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Export -->
            <div class="mt-6 pt-4 border-t flex gap-3">
                <button class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                    <i class="fas fa-file-export mr-2"></i> Export Report
                </button>
                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-file-pdf mr-2"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>
@endsection