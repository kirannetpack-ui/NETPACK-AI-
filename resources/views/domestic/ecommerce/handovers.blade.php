@extends('layouts.app')
@section('title', 'E-commerce handovers · NETPACK')
@section('page-title', 'E-commerce handovers')
@section('content')
<div class="max-w-6xl mx-auto space-y-5">
    <div class="np-page-intro">
        <p class="np-eyebrow">Domestic / E-commerce / Hub handovers</p>
        <h1>Hub handovers</h1>
        <p class="np-page-description">Confirm physical handovers between pickup, partner linehaul and final delivery. Record a location and transport reference for each linehaul movement.</p>
    </div>
    @forelse($legs as $leg)
        @php
            $chain = $assignments->get($leg->master_awb);
            $first = $chain->firstWhere('sequence', 1);
            $last = $chain->firstWhere('sequence', 3);
        @endphp
        <section class="np-section space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-mono">{{ $leg->master_awb }}</h2>
                <span class="text-xs text-slate-500">{{ $leg->partner?->company_name }} · {{ str_replace('_', ' ', $leg->status) }}</span>
            </div>
            <div class="grid md:grid-cols-3 gap-4 text-sm">
                <p>Origin pickup<br><span class="text-slate-500">{{ str_replace('_', ' ', $first?->status ?? 'pending') }}</span></p>
                <p>Linehaul<br><span class="text-slate-500">{{ $leg->pickup_address }} → {{ $leg->delivery_address }}</span></p>
                <p>Final delivery<br><span class="text-slate-500">{{ str_replace('_', ' ', $last?->status ?? 'pending') }}</span></p>
            </div>
            @if($first?->status === 'picked_up')
                <p class="text-sm bg-slate-50 p-3 rounded-lg">After receiving the parcel at the origin hub, give the pickup rider this handover code: <strong class="font-mono">{{ $first->delivery_otp }}</strong></p>
            @endif
            @if($leg->status === 'completed' && $last && !$last->pickup_otp_verified_at)
                <p class="text-sm bg-slate-50 p-3 rounded-lg">When releasing the parcel to the assigned destination rider, provide this pickup code: <strong class="font-mono">{{ $last->pickup_otp }}</strong></p>
            @endif
            @if(in_array($leg->status, ['arrived_pickup', 'in_transit']))
                <form method="POST" action="{{ route('domestic.handovers.store', $leg->id) }}" class="grid md:grid-cols-3 items-end gap-3 pt-3 border-t">
                    @csrf
                    <input type="hidden" name="event" value="{{ $leg->status === 'arrived_pickup' ? 'dispatched' : 'received' }}">
                    <div><label for="location-{{ $leg->id }}" class="block mb-1">Confirmed location</label><input id="location-{{ $leg->id }}" name="location" required class="w-full px-3 py-2 border" maxlength="255"></div>
                    <div><label for="notes-{{ $leg->id }}" class="block mb-1">Transport / handover reference</label><input id="notes-{{ $leg->id }}" name="notes" required minlength="5" maxlength="1000" class="w-full px-3 py-2 border"></div>
                    <button type="submit" class="px-4 py-2.5">{{ $leg->status === 'arrived_pickup' ? 'Confirm dispatch' : 'Confirm destination receipt' }}</button>
                </form>
            @endif
        </section>
    @empty
        <section class="np-section"><h2>No hub handovers waiting</h2><p class="text-sm text-slate-500 mt-2">Multi-leg e-commerce bookings appear here after they are created.</p></section>
    @endforelse
    {{ $legs->links() }}
</div>
@endsection
