@extends('layouts.app')

@section('title', 'Remote Area Surcharges')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Remote Area Surcharges</h1>
                <p class="text-sm text-gray-500 mt-1">Manage remote and extended area surcharges</p>
            </div>
            <a href="{{ route('international.surcharges.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-upload mr-2"></i> Upload Surcharges
            </a>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Partner</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Country</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">City</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Area</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Surcharge</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($surcharges as $surcharge)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4">{{ $surcharge->overseasPartner->company_name ?? $surcharge->overseasPartner->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $surcharge->country }}</td>
                                <td class="py-3 px-4">{{ $surcharge->city ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $surcharge->area_name }}</td>
                                <td class="py-3 px-4">
                                    @if($surcharge->surcharge_amount > 0)
                                        ${{ number_format($surcharge->surcharge_amount, 2) }}
                                    @else
                                        {{ number_format($surcharge->surcharge_percentage, 1) }}%
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($surcharge->is_active)
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-circle text-green-500 text-xs mr-1"></i> Active
                                        </span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-circle text-red-500 text-xs mr-1"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('admin.rates.toggle', $surcharge->id) }}" class="inline" onsubmit="return confirm('{{ $surcharge->is_active ? 'Deactivate' : 'Activate' }} this surcharge?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-{{ $surcharge->is_active ? 'red' : 'green' }}-600 hover:text-{{ $surcharge->is_active ? 'red' : 'green' }}-800" title="{{ $surcharge->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas fa-{{ $surcharge->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.rates.surcharges.destroy', $surcharge->id) }}" class="inline" onsubmit="return confirm('Delete this surcharge?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-map-marker-alt text-4xl block mb-2"></i>
                                    No surcharges found. Upload a surcharge file to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $surcharges->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
