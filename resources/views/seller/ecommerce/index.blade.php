@extends('layouts.seller')

@section('title', 'E-Commerce Deliveries - Seller Panel')

@section('content')
<div class="space-y-6">
    <!-- Header Hero Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 p-6 text-white border border-teal-800/40 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                    <i class="fas fa-boxes-packing text-teal-400"></i> E-Commerce Delivery Desk
                </div>
                <h1 class="text-xl md:text-2xl font-black tracking-tight">Manage E-Commerce Shipments</h1>
                <p class="text-xs text-slate-300 max-w-2xl">
                    Dispatch parcels directly to customers via local riders or schedule multi-leg domestic courier transport across Nepal.
                </p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('seller.ecommerce.direct') }}" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
                    <i class="fas fa-motorcycle"></i> Direct Rider Delivery
                </a>
                <a href="{{ route('seller.ecommerce.multileg') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-teal-300 border border-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-2">
                    <i class="fas fa-truck-moving"></i> Domestic Courier
                </a>
            </div>
        </div>
    </div>

    <!-- Deliveries Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">Dispatched Consignments Registry</h3>
            <span class="text-xs text-slate-500">Total: {{ $deliveries->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Master AWB</th>
                        <th class="px-4 py-3">Delivery Model</th>
                        <th class="px-4 py-3">Recipient & Destination</th>
                        <th class="px-4 py-3">Current Custody</th>
                        <th class="px-4 py-3">COD Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($deliveries as $delivery)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-4 py-3 font-mono font-bold text-teal-700">
                            {{ $delivery->master_awb }}
                        </td>
                        <td class="px-4 py-3">
                            @if($delivery->assignment_type === 'local_direct')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-800 border border-teal-200">
                                    <i class="fas fa-bolt text-teal-600"></i> Direct Rider
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-800 border border-sky-200">
                                    <i class="fas fa-network-wired text-sky-600"></i> Multi-Leg Courier
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-bold text-slate-800">{{ $delivery->delivery_name }}</p>
                            <p class="text-[10px] text-slate-500 truncate max-w-xs">{{ $delivery->delivery_address }} &bull; {{ $delivery->delivery_phone }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                <i class="fas fa-handshake text-slate-400 text-[9px]"></i> {{ $delivery->current_custody }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono font-bold {{ $delivery->cod_amount > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                            {{ $delivery->cod_amount > 0 ? 'Rs. ' . number_format($delivery->cod_amount, 2) : 'Prepaid' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold 
                                {{ in_array($delivery->status, ['completed']) ? 'bg-emerald-50 text-emerald-700' : (in_array($delivery->status, ['picked_up', 'in_transit']) ? 'bg-teal-50 text-teal-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('seller.ecommerce.show', $delivery->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition">
                                <i class="fas fa-eye"></i> View & OTP
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                            <i class="fas fa-boxes-packing text-3xl mb-2 text-slate-300"></i>
                            <p class="text-xs">No e-commerce deliveries dispatched yet.</p>
                            <div class="mt-3 flex items-center justify-center gap-2">
                                <a href="{{ route('seller.ecommerce.direct') }}" class="text-xs font-bold text-teal-600 hover:underline">
                                    Book First Direct Rider Delivery &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deliveries->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $deliveries->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
