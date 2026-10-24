@extends('layouts.app')

@section('title', 'Domestic Partner Rates')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Domestic Service Rates</h1>
            <p class="text-sm text-gray-500 mt-1">Submit pickup, inter-zone logistics, last-mile delivery, or complete door-to-door rates.</p>
        </div>
        <a href="{{ route('partner.rates.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">
            <i class="fas fa-plus mr-2"></i> Submit Rate
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif

    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 grid grid-cols-1 md:grid-cols-3 gap-3">
        <select name="status" class="rounded-lg border-gray-300">
            <option value="">All approval statuses</option>
            @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="service_type" class="rounded-lg border-gray-300">
            <option value="">All services</option>
            @foreach($services as $code => $name)
                <option value="{{ $code }}" @selected(request('service_type') === $code)>{{ $name }}</option>
            @endforeach
        </select>
        <button class="rounded-lg border border-teal-600 px-4 py-2 text-sm font-semibold text-teal-700 hover:bg-teal-50">Apply filters</button>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr><th class="px-4 py-3">Movement</th><th class="px-4 py-3">Service</th><th class="px-4 py-3">Weight</th><th class="px-4 py-3">Partner cost</th><th class="px-4 py-3">Approval</th><th class="px-4 py-3 text-right">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rates as $rate)
                        <tr>
                            <td class="px-4 py-4"><div class="font-semibold text-gray-900">{{ $rate->originZone?->zone_name ?? $rate->origin_city }}</div><div class="text-gray-500">to {{ $rate->destinationZone?->zone_name ?? $rate->destination_city }}</div><div class="mt-1 text-xs font-medium text-teal-700">{{ $rateTypes[$rate->rate_type] ?? str($rate->rate_type)->headline() }}</div></td>
                            <td class="px-4 py-4">{{ $rate->service_name }}</td>
                            <td class="px-4 py-4">{{ $rate->weight_from }}–{{ $rate->weight_to }} kg</td>
                            <td class="px-4 py-4 font-semibold">{{ $rate->currency }} {{ number_format($rate->calculateRate((float) $rate->weight_from)['total'], 2) }}</td>
                            <td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $rate->approval_status === 'approved' ? 'bg-green-100 text-green-800' : ($rate->approval_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ ucfirst($rate->approval_status) }}</span>@if($rate->rejection_reason)<p class="mt-2 max-w-xs text-xs text-red-600">{{ $rate->rejection_reason }}</p>@endif</td>
                            <td class="px-4 py-4 text-right"><a href="{{ route('partner.rates.edit', $rate) }}" class="font-semibold text-teal-700 hover:text-teal-900">Edit and resubmit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-gray-500">No rates submitted. Create an operating zone first, then submit a rate.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t px-4 py-3">{{ $rates->links() }}</div>
    </div>
</div>
@endsection
