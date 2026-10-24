@extends('layouts.app')

@section('title', 'My Deliveries - Rider Cockpit')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900">My Deliveries & Active Jobs</h1>
            <p class="text-xs text-slate-500 mt-0.5">Track your ongoing delivery assignments and review completed jobs history.</p>
        </div>
        <a href="{{ route('rider.delivery.available') }}" class="px-4 py-2 bg-teal-600 hover:bg-teal-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
            <i class="fas fa-radar"></i> Browse Available Jobs
        </a>
    </div>

    <!-- Active Ongoing Deliveries -->
    <div class="space-y-3">
        <h3 class="text-xs font-black uppercase tracking-wider text-slate-500 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-teal-500 animate-ping"></span>
            Active In-Progress Deliveries ({{ count($activeDeliveries) }})
        </h3>

        @forelse($activeDeliveries as $active)
        <div class="p-5 rounded-2xl bg-white border-2 border-teal-500/40 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-2 flex-1">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-teal-50 text-teal-800 border border-teal-200">
                        {{ $active->master_awb }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase 
                        {{ $active->status === 'picked_up' ? 'bg-indigo-50 text-indigo-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ str_replace('_', ' ', $active->status) }}
                    </span>
                    <span class="text-xs text-slate-400">&bull; {{ $active->distance_km }} KM</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    <div>
                        <p class="text-[10px] font-bold uppercase text-slate-400">Pickup Origin (Seller)</p>
                        <p class="font-bold text-slate-800">{{ $active->pickup_name }} &bull; <span class="font-mono text-teal-700">{{ $active->pickup_phone }}</span></p>
                        <p class="text-slate-500 text-[11px]">{{ $active->pickup_address }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase text-slate-400">Delivery Destination (Customer)</p>
                        <p class="font-bold text-slate-800">{{ $active->delivery_name }} &bull; <span class="font-mono text-teal-700">{{ $active->delivery_phone }}</span></p>
                        <p class="text-slate-500 text-[11px]">{{ $active->delivery_address }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 border-t md:border-t-0 md:border-l border-slate-100 pt-3 md:pt-0 md:pl-6 justify-between md:justify-end flex-shrink-0">
                <div class="text-right">
                    <p class="text-[10px] uppercase font-bold text-slate-400">Your Earnings</p>
                    <p class="text-lg font-black text-teal-700 font-mono">Rs. {{ number_format($active->provider_fee, 2) }}</p>
                    @if($active->cod_amount > 0)
                        <p class="text-[10px] font-bold text-amber-600">Collect: Rs. {{ number_format($active->cod_amount) }}</p>
                    @endif
                </div>

                <a href="{{ route('rider.delivery.show', $active->id) }}" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-black text-xs rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <i class="fas fa-play"></i> Execute Delivery &rarr;
                </a>
            </div>
        </div>
        @empty
        <div class="p-6 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 text-xs">
            No deliveries currently in progress. Go to Available Jobs to claim a delivery!
        </div>
        @endforelse
    </div>

    <!-- Completed Deliveries History -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900 text-sm">Delivery History</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Master AWB</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Address</th>
                        <th class="px-4 py-3">COD Collected</th>
                        <th class="px-4 py-3">Earnings</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Completed At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($completedDeliveries as $completed)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-4 py-3 font-mono font-bold text-teal-700">{{ $completed->master_awb }}</td>
                        <td class="px-4 py-3 font-bold text-slate-800">{{ $completed->delivery_name }}</td>
                        <td class="px-4 py-3 text-[11px] text-slate-500 max-w-xs truncate">{{ $completed->delivery_address }}</td>
                        <td class="px-4 py-3 font-mono font-bold text-amber-600">
                            {{ $completed->cod_amount > 0 ? 'Rs. ' . number_format($completed->cod_amount) : 'Prepaid' }}
                        </td>
                        <td class="px-4 py-3 font-mono font-bold text-teal-700">
                            +Rs. {{ number_format($completed->provider_fee, 2) }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $completed->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                {{ ucfirst($completed->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-[10px] text-slate-400">
                            {{ $completed->delivered_at ? $completed->delivered_at->format('M d, H:i') : ($completed->failed_at ? $completed->failed_at->format('M d, H:i') : $completed->updated_at->format('M d, H:i')) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                            No completed deliveries yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($completedDeliveries->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $completedDeliveries->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
