@extends('layouts.app')

@section('title', 'Pickups')

@section('content')
<div class="bg-white rounded-xl shadow-sm">
    <div class="px-6 py-4 border-b">
        <h1 class="text-xl font-semibold text-gray-800">Pickup Requests</h1>
    </div>
    
    <div class="p-6">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm">ID</th>
                        <th class="px-4 py-3 text-left text-sm">Seller</th>
                        <th class="px-4 py-3 text-left text-sm">Pickup</th>
                        <th class="px-4 py-3 text-left text-sm">Delivery</th>
                        <th class="px-4 py-3 text-left text-sm">Status</th>
                        <th class="px-4 py-3 text-left text-sm">Date</th>
                        <th class="px-4 py-3 text-left text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pickups as $pickup)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3">#{{ $pickup->id }}</td>
                        <td class="px-4 py-3">{{ $pickup->seller->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">{{ $pickup->pickup_district }}</td>
                        <td class="px-4 py-3">{{ $pickup->delivery_district }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($pickup->status == 'delivered') bg-green-100 text-green-800
                                @elseif($pickup->status == 'picked_up') bg-blue-100 text-blue-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $pickup->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $pickup->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.pickups.show', $pickup->id) }}" class="text-teal-600">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No pickup requests found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $pickups->links() }}
    </div>
</div>
@endsection