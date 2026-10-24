@extends('layouts.app')

@section('title', 'Rate Management')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">International Rate Management</h1>
                <p class="text-sm text-gray-500 mt-1">Manage overseas partner rates and surcharges</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.rates.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                    <i class="fas fa-upload mr-2"></i> Upload Rate Sheet
                </a>
                <a href="{{ route('admin.rates.surcharges') }}" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition">
                    <i class="fas fa-map-marker-alt mr-2"></i> Remote Surcharges
                </a>
            </div>
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
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4">{{ $rate->overseasPartner->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $rate->country_from }}</td>
                                <td class="py-3 px-4">{{ $rate->country_to }}</td>
                                <td class="py-3 px-4">{{ $rate->weight_from }} - {{ $rate->weight_to }} kg</td>
                                <td class="py-3 px-4 font-medium">${{ $rate->rate_per_kg }}</td>
                                <td class="py-3 px-4">
                                    @if($rate->is_active)
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Active</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <button onclick="toggleRate({{ $rate->id }})" class="text-teal-600 hover:text-teal-800">
                                        <i class="fas fa-toggle-{{ $rate->is_active ? 'on' : 'off' }}"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.rates.destroy', $rate->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Delete this rate?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">
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

<script>
function toggleRate(id) {
    fetch(`/admin/rates/${id}/toggle`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        }
    }).then(response => location.reload());
}
</script>
@endsection