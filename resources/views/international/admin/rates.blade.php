@extends('layouts.app')

@section('title', 'International Rates')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">International Base Rates</h1>
                <p class="text-sm text-gray-500 mt-1">Manage international shipping rates</p>
            </div>
            <a href="{{ route('international.rates.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-upload mr-2"></i> Upload Rate Sheet
            </a>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Partner</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">From</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">To</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Weight Range</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Rate/Kg</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4">{{ $rate->overseasPartner->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $rate->country_from ?? 'Nepal' }}</td>
                                <td class="py-3 px-4">{{ $rate->country_to }}</td>
                                <td class="py-3 px-4">{{ $rate->weight_from }} - {{ $rate->weight_to }} kg</td>
                                <td class="py-3 px-4 font-medium">${{ number_format($rate->rate_per_kg, 2) }}</td>
                                <td class="py-3 px-4">
                                    @if($rate->is_active)
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Active</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-file-invoice text-4xl block mb-2"></i>
                                    No rates found. Upload a rate sheet to get started.
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