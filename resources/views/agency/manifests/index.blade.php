@extends('layouts.agency')

@section('title', 'Inbound Flight Manifests | Agency Portal')

@section('content')
<div class="max-w-7xl mx-auto px-4 space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl shadow-xl p-6 text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    <i class="fas fa-plane-arrival mr-1"></i> Destination Inbound Clearance
                </span>
                <span class="text-slate-400 text-xs font-mono">Agency: {{ $agency->name ?? 'Hub Receiver' }} ({{ $agency->code ?? 'HUB' }})</span>
            </div>
            <h1 class="text-3xl font-black tracking-tight">Inbound Flight Cargo Manifests</h1>
            <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                Review flight consolidations dispatched from Nepal origin. File flight arrival notices by confirming whole manifest arrival or selecting partial arrivals with non-arrival packet remarks.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('agency.scan') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 transition">
                <i class="fas fa-qrcode"></i> Scan Box QR Code
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-lg"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Inbound Manifests Grid / Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-file-invoice text-indigo-500"></i> Dispatched Flight Manifests Awaiting Receipt
            </h3>
            <span class="text-xs text-slate-500 font-medium">Total: {{ $manifests->total() }} manifests</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Manifest Number</th>
                        <th class="px-5 py-3.5">Bound MAWB</th>
                        <th class="px-5 py-3.5">Airline & Flight</th>
                        <th class="px-5 py-3.5">Scheduled Date</th>
                        <th class="px-5 py-3.5">Packets & Weight</th>
                        <th class="px-5 py-3.5">Arrival Notice Status</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($manifests as $manifest)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs border border-indigo-200 dark:border-indigo-800">
                                    <i class="fas fa-plane-arrival"></i>
                                </span>
                                <div>
                                    <span>{{ $manifest->manifest_number }}</span>
                                    <p class="text-[10px] text-slate-400 font-normal">Dispatched: {{ $manifest->created_at->format('M d, Y') }}</p>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-mono">
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-sky-50 dark:bg-sky-950 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                                    {{ $manifest->mawb_number ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $manifest->mawb->airline_name ?? 'Airline' }}</div>
                                <div class="font-mono text-slate-400 text-[10px]">{{ $manifest->flight_number ?? 'Flight TBD' }}</div>
                            </td>
                            <td class="px-5 py-3.5 font-mono">
                                {{ $manifest->flight_date ? \Carbon\Carbon::parse($manifest->flight_date)->format('M d, Y') : 'Pending' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $manifest->total_shipments }} pkts</span>
                                <div class="text-[10px] text-slate-400 font-mono">{{ number_format($manifest->total_weight, 2) }} kg</div>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($manifest->status === 'received' || $manifest->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        <i class="fas fa-check-double mr-1"></i> Arrival Filed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 animate-pulse">
                                        <i class="fas fa-clock mr-1"></i> Pending Arrival Notice
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <a href="{{ route('agency.manifests.arrival-notice', $manifest->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                    <i class="fas fa-clipboard-check"></i> File Arrival Notice
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                No inbound manifests currently dispatched to this agency.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
            {{ $manifests->links() }}
        </div>
    </div>
</div>
@endsection
