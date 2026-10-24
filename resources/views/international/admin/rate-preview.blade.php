@extends('layouts.app')

@section('title', 'Rate Preview')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Rate Preview</h1>
                <p class="text-sm text-gray-500 mt-1">Review and confirm rates before importing</p>
            </div>
            <div class="flex gap-2">
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                    <i class="fas fa-file mr-1"></i> {{ $fileName ?? 'No file' }}
                </span>
                @if(isset($columnMapping) && count($columnMapping) > 0)
                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                    <i class="fas fa-check-circle mr-1"></i> {{ count($columnMapping) }} columns detected
                </span>
                @endif
            </div>
        </div>

        <div class="p-6">
            <!-- Column Mapping Summary -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Total Records</p>
                    <p class="text-lg font-bold text-blue-600">{{ count($parsedData ?? []) }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Countries/Zone Groups</p>
                    <p class="text-lg font-bold text-green-600">{{ count(array_unique(array_column($parsedData ?? [], 'country'))) }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Zone Groups</p>
                    <p class="text-lg font-bold text-purple-600">{{ count(array_filter($parsedData ?? [], function($item) { return isset($item['is_zone']) && $item['is_zone']; })) }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Individual Countries</p>
                    <p class="text-lg font-bold text-yellow-600">{{ count(array_filter($parsedData ?? [], function($item) { return !isset($item['is_zone']) || !$item['is_zone']; })) }}</p>
                </div>
            </div>

            <!-- Column Mapping Display -->
            @if(isset($columnMapping) && count($columnMapping) > 0)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-6">
                <h4 class="font-medium text-blue-800 text-sm mb-2">📊 Column Mapping Detected</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($columnMapping as $key => $index)
                        <span class="px-2 py-1 bg-white rounded border border-blue-200 text-xs">
                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong> → Column {{ $index + 1 }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Data Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">#</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Country</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Zone/Group</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Weight From (kg)</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Weight To (kg)</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Rate/Kg (USD)</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parsedData ?? [] as $index => $row)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4">{{ $index + 1 }}</td>
                                <td class="py-3 px-4">
                                    <span class="font-medium">{{ $row['country'] ?? 'N/A' }}</span>
                                    @if(isset($row['country_code']))
                                        <span class="text-xs text-gray-500">({{ $row['country_code'] }})</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if(isset($row['is_zone']) && $row['is_zone'])
                                        <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">
                                            <i class="fas fa-layer-group mr-1"></i> {{ $row['zone'] ?? 'Zone' }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">Individual</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ $row['weight_from'] ?? 0 }}</td>
                                <td class="py-3 px-4">{{ $row['weight_to'] ?? '∞' }}</td>
                                <td class="py-3 px-4 font-bold text-teal-600">${{ number_format($row['rate_per_kg'] ?? 0, 2) }}</td>
                                <td class="py-3 px-4">
                                    @if(isset($row['rate_per_kg']) && $row['rate_per_kg'] > 0)
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-check-circle text-green-500 text-xs mr-1"></i> Valid
                                        </span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-exclamation-circle text-red-500 text-xs mr-1"></i> Invalid
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-file text-4xl block mb-2"></i>
                                    No data found in the file. Please check the file format.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex gap-3 pt-4 border-t">
                @if(count($parsedData ?? []) > 0)
                    <form method="POST" action="{{ route('international.rates.import') }}" class="inline">
                        @csrf
                        <input type="hidden" name="partner_id" value="{{ $partnerId ?? '' }}">
                        <input type="hidden" name="service_type" value="{{ $serviceType ?? 'standard' }}">
                        <input type="hidden" name="effective_from" value="{{ $effectiveFrom ?? date('Y-m-d') }}">
                        <input type="hidden" name="effective_to" value="{{ $effectiveTo ?? '' }}">
                        <input type="hidden" name="rates" value="{{ json_encode($parsedData ?? []) }}">
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition" onclick="return confirm('Import {{ count($parsedData ?? []) }} rates?')">
                            <i class="fas fa-check mr-2"></i> Import All Valid Rates
                        </button>
                    </form>
                    <button onclick="window.location.reload()" class="bg-yellow-600 text-white px-6 py-2 rounded-lg hover:bg-yellow-700 transition">
                        <i class="fas fa-sync mr-2"></i> Re-parse File
                    </button>
                @endif
                <a href="{{ route('international.rates.create') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>
@endsection