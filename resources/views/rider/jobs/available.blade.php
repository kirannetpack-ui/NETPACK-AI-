@extends('layouts.app')

@section('title', 'Available Delivery Jobs - Rider Cockpit')

@section('content')
<div class="space-y-6">
    <!-- Header Hero Banner -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 text-white border border-teal-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                <i class="fas fa-radar text-teal-400"></i> Dispatch Marketplace
            </div>
            <h1 class="text-xl md:text-2xl font-black">Available Delivery Jobs</h1>
            <p class="text-xs text-slate-300">
                Accept on-demand jobs in your area. Exact sender and recipient contact numbers are revealed immediately upon accepting.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right">
                <p class="text-[10px] uppercase font-bold text-slate-400">COD Headroom</p>
                <p class="text-base font-black text-teal-400">
                    Rs. {{ number_format(max(0, $rider->cod_limit - $rider->current_outstanding_cod)) }}
                </p>
            </div>
            <a href="{{ route('rider.delivery.my') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-teal-300 border border-slate-700 rounded-xl text-xs font-bold transition">
                My Active Deliveries
            </a>
        </div>
    </div>

    <!-- Available Job Cards -->
    <div class="space-y-4">
        @forelse($availableAssignments as $job)
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-teal-400 transition flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-2 flex-1">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-800">
                        {{ $job->master_awb }}
                    </span>
                    @if($job->assignment_type === 'local_direct')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-800 border border-teal-200">
                            Direct Express (Same-Day)
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-800 border border-sky-200">
                            Multi-Leg Delivery
                        </span>
                    @endif
                    <span class="text-xs text-slate-400">&bull; {{ $job->distance_km }} KM &bull; {{ $job->parcel_weight }} KG</span>
                </div>

                <!-- Route Overview (Privacy Protected: Areas Only) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-circle-dot text-teal-600 text-xs mt-0.5"></i>
                        <div>
                            <p class="font-bold text-slate-800">Pickup Area: {{ Str::limit($job->pickup_address, 40) }}</p>
                            <p class="text-[10px] text-slate-400">Sender: {{ $job->pickup_name }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-location-pin text-rose-600 text-xs mt-0.5"></i>
                        <div>
                            <p class="font-bold text-slate-800">Delivery Area: {{ Str::limit($job->delivery_address, 40) }}</p>
                            <p class="text-[10px] text-slate-400">Customer Phone: Hidden until accepted</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financials & Accept Action -->
            <div class="flex items-center gap-4 border-t md:border-t-0 md:border-l border-slate-100 pt-3 md:pt-0 md:pl-6 justify-between md:justify-end flex-shrink-0">
                <div class="text-right">
                    <p class="text-[10px] font-bold uppercase text-slate-400">Your Payout</p>
                    <p class="text-xl font-black text-teal-700 font-mono">Rs. {{ number_format($job->provider_fee, 2) }}</p>
                    @if($job->cod_amount > 0)
                        <p class="text-[10px] text-amber-600 font-bold">COD: Rs. {{ number_format($job->cod_amount) }}</p>
                    @else
                        <p class="text-[10px] text-slate-400 font-semibold">Prepaid Order</p>
                    @endif
                </div>

                <form method="POST" action="{{ route('rider.delivery.accept', $job->id) }}">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-500 text-white font-black text-xs rounded-xl transition shadow-sm flex items-center gap-1.5">
                        <i class="fas fa-hand-holding-box"></i> Accept Job
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="p-12 text-center bg-white rounded-2xl border border-slate-200 text-slate-400">
            <i class="fas fa-radar text-4xl mb-3 text-slate-300 animate-spin"></i>
            <h3 class="font-bold text-slate-700 text-sm">Scanning for Nearby Deliveries...</h3>
            <p class="text-xs mt-1">No new delivery jobs currently broadcasted in your radius. Please check back shortly.</p>
        </div>
        @endforelse
    </div>

    @if($availableAssignments->hasPages())
    <div>{{ $availableAssignments->links() }}</div>
    @endif
</div>
@endsection
