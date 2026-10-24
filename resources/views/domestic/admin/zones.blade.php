@extends('layouts.app')

@section('title', 'Delivery Zones')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Delivery Zones</h1>
                <p class="text-sm text-gray-500 mt-1">Manage delivery zones</p>
            </div>
            <a href="{{ route('domestic.zones.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
    <i class="fas fa-plus mr-2"></i> Add Zone
            </a>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Zone Name</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Partner</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Type</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zones as $zone)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-medium">{{ $zone->zone_name }}</td>
                                <td class="py-3 px-4">{{ $zone->partner->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium 
                                        {{ $zone->zone_type === 'urban' ? 'bg-blue-100 text-blue-800' : 
                                           ($zone->zone_type === 'semi_urban' ? 'bg-green-100 text-green-800' : 
                                           ($zone->zone_type === 'rural' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($zone->zone_type === 'hilly' ? 'bg-orange-100 text-orange-800' : 
                                           'bg-purple-100 text-purple-800'))) }}">
                                        {{ ucfirst(str_replace('_', ' ', $zone->zone_type)) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($zone->is_active)
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Active</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('domestic.zones.edit', $zone->id) }}" class="text-teal-600 hover:text-teal-800">
    <i class="fas fa-edit"></i>

                                        </a>
                                        <form method="POST" action="{{ route('domestic.zones.destroy', $zone->id) }}" class="inline" onsubmit="return confirm('Delete this zone?')">

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
                                <td colspan="5" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-map text-4xl block mb-2"></i>
                                    No zones found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $zones->links() }}
            </div>
        </div>
    </div>
</div>
@endsection