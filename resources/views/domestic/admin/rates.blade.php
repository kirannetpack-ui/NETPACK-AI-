@extends('layouts.app')

@section('title', 'Domestic Rates')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Domestic Rates</h1>
                <p class="text-sm text-gray-500 mt-1">Manage domestic delivery rates</p>
            </div>
            <a href="{{ route('domestic.rates.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
    <i class="fas fa-plus mr-2"></i> Add Rate
            </a>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Partner</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Service</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">From Zone</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">To Zone</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Weight Range</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Rate</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4">{{ $rate->partner->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">
                                    <span class="font-medium">{{ $rate->service_name }}</span>
                                    <span class="text-xs text-gray-500 block">{{ ucfirst($rate->service_type) }}</span>
                                </td>
                                <td class="py-3 px-4">{{ $rate->originZone->zone_name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $rate->destinationZone->zone_name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $rate->weight_from }} - {{ $rate->weight_to }} kg</td>
                                <td class="py-3 px-4 font-medium">Rs. {{ number_format($rate->base_rate, 2) }}</td>
                                <td class="py-3 px-4">
                                    @if($rate->is_active)
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Active</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('domestic.rates.edit', $rate->id) }}" class="text-teal-600 hover:text-teal-800">
    <i class="fas fa-edit"></i>
                                        </a>
                                       <form method="POST" action="{{ route('domestic.rates.destroy', $rate->id) }}" class="inline" onsubmit="return confirm('Delete this rate?')">

                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-money-bill-wave text-4xl block mb-2"></i>
                                    No rates found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $rates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection