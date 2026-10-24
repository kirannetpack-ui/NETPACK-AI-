@extends('layouts.agency')

@section('title', 'Agency Shipments | COURIER with NETPACK')

@section('content')
<div class="max-w-7xl mx-auto px-4 space-y-6">
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl shadow-xl p-6 text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black">Agency Consignments</h1>
            <p class="text-xs text-slate-300 mt-1">Consignments routed to this destination agency hub for customs clearance and last-mile delivery handover.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('agency.manifests.index') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold border border-slate-700">
                <i class="fas fa-plane-arrival mr-1"></i> Inbound Manifests
            </a>
            <a href="{{ route('agency.scan') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md">
                <i class="fas fa-qrcode mr-1"></i> Scan QR
            </a>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2">
        <a href="{{ route('agency.shipments.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold {{ !request('filter') ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-900 text-slate-600 border border-slate-200 dark:border-slate-800' }}">
            All Consignments
        </a>
        <a href="{{ route('agency.shipments.index', ['filter' => 'arrived']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold {{ request('filter') == 'arrived' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-900 text-slate-600 border border-slate-200 dark:border-slate-800' }}">
            Arrived at Hub
        </a>
        <a href="{{ route('agency.shipments.index', ['filter' => 'departed']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold {{ request('filter') == 'departed' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-900 text-slate-600 border border-slate-200 dark:border-slate-800' }}">
            Handed Over / Departed
        </a>
    </div>

    <!-- Shipments Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Tracking / HAWB</th>
                        <th class="px-5 py-3.5">MAWB #</th>
                        <th class="px-5 py-3.5">Consignee & Destination</th>
                        <th class="px-5 py-3.5">Weight</th>
                        <th class="px-5 py-3.5">Customs Mode</th>
                        <th class="px-5 py-3.5">Agency Milestone</th>
                        <th class="px-5 py-3.5">Arrived At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($shipments as $s)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3.5 font-mono">
                                <a href="{{ route('tracking.public', ['tracking_number' => $s->tracking_number]) }}" target="_blank" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                    {{ $s->tracking_number }}
                                    <i class="fas fa-external-link-alt text-[9px]"></i>
                                </a>
                                @if($s->hawb_number)
                                    <div class="text-[10px] text-slate-400">HAWB: {{ $s->hawb_number }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-mono font-bold text-sky-600 dark:text-sky-400">
                                {{ $s->mawb_number ?? ($s->mawb->mawb_number ?? 'N/A') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $s->receiver_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $s->receiver_city ?? '' }}, {{ $s->receiver_country ?? '' }}</div>
                            </td>
                            <td class="px-5 py-3.5 font-mono font-bold">
                                {{ number_format($s->actual_weight ?? 1.0, 2) }} kg
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ ($s->customs_mode ?? 'DDP') == 'DDP' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                                    {{ $s->customs_mode ?? 'DDP' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                                    {{ ucwords(str_replace('_', ' ', $s->agency_milestone ?? $s->status)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-[11px] font-mono">
                                {{ $s->arrived_at_agency ? \Carbon\Carbon::parse($s->arrived_at_agency)->format('M d, Y H:i') : 'Pending' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                No shipments found in this category.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
            {{ $shipments->links() }}
        </div>
    </div>
</div>
@endsection
