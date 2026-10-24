@extends('layouts.app')

@section('title', 'Transit Points')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Transit Points</h1>
                <p class="text-sm text-gray-500 mt-1">Manage overseas transit points and hubs</p>
            </div>
            <a href="{{ route($routePrefix.'.transit-points.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-plus mr-2"></i> Add Transit Point
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

            <!-- Filter by Partner -->
            <form method="GET" class="mb-6 flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium mb-1">Filter by Partner</label>
                    <select name="partner_id" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" onchange="this.form.submit()">
                        <option value="">All Partners</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if(request('partner_id'))
                    <a href="{{ route($routePrefix.'.transit-points.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                        Clear Filter
                    </a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">#</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Partner</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Name</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Type</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Location</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Country</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Mandatory</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transitPoints as $point)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4">{{ $loop->iteration }}</td>
                                <td class="py-3 px-4">{{ $point->partner->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4 font-medium">{{ $point->name }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $point->type_color }}-100 text-{{ $point->type_color }}-800">
                                        {{ $point->type_icon }} {{ $point->type_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">{{ $point->location }}</td>
                                <td class="py-3 px-4">{{ $point->country }}</td>
                                <td class="py-3 px-4">
                                    @if($point->is_mandatory)
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-star text-red-500 text-xs mr-1"></i> Yes
                                        </span>
                                    @else
                                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-medium">No</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($point->is_active)
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
                                        <a href="{{ route($routePrefix.'.transit-points.edit', $point->id) }}" class="text-teal-600 hover:text-teal-800" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route($routePrefix.'.transit-points.toggle', $point->id) }}" class="inline" onsubmit="return confirm('{{ $point->is_active ? 'Deactivate' : 'Activate' }} this transit point?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-{{ $point->is_active ? 'red' : 'green' }}-600 hover:text-{{ $point->is_active ? 'red' : 'green' }}-800" title="{{ $point->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas fa-{{ $point->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route($routePrefix.'.transit-points.destroy', $point->id) }}" class="inline" onsubmit="return confirm('Delete this transit point?')">
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
                                <td colspan="9" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-map-pin text-4xl block mb-2"></i>
                                    No transit points found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transitPoints->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
