@extends('layouts.app')

@section('title', 'Air Cargo Manifests | International Logistics')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden">
        <div class="absolute right-0 top-0 w-96 h-full opacity-10 pointer-events-none flex items-center justify-end pr-8">
            <i class="fas fa-file-invoice-dollar text-9xl"></i>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        <i class="fas fa-plane-departure mr-1"></i> Airline Consolidation Engine
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        MAWB Auto-Binding & Data Sheets
                    </span>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight">International Flight Manifests</h1>
                <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                    Consolidate bookings into outbound airline manifests, bind available MAWBs from the inventory pool, generate comprehensive agency-specific data sheets, and dispatch 1-click email pre-alerts.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('international.mawbs.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-medium border border-slate-700 shadow-sm transition">
                    <i class="fas fa-barcode text-sky-400"></i> MAWB Pool
                </a>
                <a href="{{ route('international.manifests.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fas fa-plus"></i> Build New Manifest
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-lg"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Manifests</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 text-lg">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
        </div>

        <div class="bg-amber-50/50 dark:bg-amber-950/20 rounded-2xl border border-amber-200 dark:border-amber-800/60 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Packaging / Pending</p>
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-300 mt-1">{{ number_format($stats['pending']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center text-amber-600 dark:text-amber-300 text-lg">
                    <i class="fas fa-box-open"></i>
                </div>
            </div>
        </div>

        <div class="bg-purple-50/50 dark:bg-purple-950/20 rounded-2xl border border-purple-200 dark:border-purple-800/60 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wider">In Flight / Transit</p>
                    <p class="text-2xl font-black text-purple-600 dark:text-purple-300 mt-1">{{ number_format($stats['in_transit']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center text-purple-600 dark:text-purple-300 text-lg">
                    <i class="fas fa-plane-flight"></i>
                </div>
            </div>
        </div>

        <div class="bg-emerald-50/50 dark:bg-emerald-950/20 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Hub Arrived / Notice</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-300 mt-1">{{ number_format($stats['received']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 dark:text-emerald-300 text-lg">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('international.manifests.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative">
                <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Manifest #, MAWB #, City..." class="pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 w-56 focus:ring-2 focus:ring-indigo-500">
            </div>

            <select name="hub_id" onchange="this.form.submit()" class="py-2 px-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                <option value="">-- All International Hubs --</option>
                @foreach($hubs as $hub)
                    <option value="{{ $hub->id }}" {{ request('hub_id') == $hub->id ? 'selected' : '' }}>{{ $hub->code }} - {{ $hub->name }}</option>
                @endforeach
            </select>

            <select name="agency_id" onchange="this.form.submit()" class="py-2 px-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                <option value="">-- All Handling Agencies --</option>
                @foreach($agencies as $ag)
                    <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()" class="py-2 px-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending / Created</option>
                <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Flight / Transit</option>
                <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Arrival Notice Filed</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>

            <button type="submit" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status', 'hub_id', 'agency_id']))
                <a href="{{ route('international.manifests.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-700">Clear</a>
            @endif
        </form>
    </div>

    <!-- Manifests Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Manifest Number</th>
                        <th class="px-5 py-3.5">Bound MAWB</th>
                        <th class="px-5 py-3.5">Destination Hub & Agency</th>
                        <th class="px-5 py-3.5">Flight & Date</th>
                        <th class="px-5 py-3.5">Shipments & Weight</th>
                        <th class="px-5 py-3.5">Agency Pre-alert</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($manifests as $manifest)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('international.manifests.show', $manifest->id) }}" class="font-mono font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1.5">
                                    <i class="fas fa-file-alt text-xs"></i>
                                    {{ $manifest->manifest_number }}
                                </a>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $manifest->created_at->format('M d, Y H:i') }}</p>
                            </td>

                            <td class="px-5 py-3.5 font-mono">
                                @if($manifest->mawb_number)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-sky-50 dark:bg-sky-950 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                                        <i class="fas fa-barcode text-[10px]"></i> {{ $manifest->mawb_number }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">No MAWB</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1">
                                    <i class="fas fa-globe-americas text-indigo-500"></i>
                                    {{ $manifest->hub->code ?? 'N/A' }} - {{ $manifest->hub->name ?? 'Direct' }}
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                                    <i class="fas fa-building text-amber-500 text-[10px]"></i>
                                    {{ $manifest->agency->name ?? 'Primary Hub Desk' }}
                                </div>
                            </td>

                            <td class="px-5 py-3.5 font-mono text-[11px]">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $manifest->flight_number ?? 'TBD' }}</div>
                                <div class="text-slate-400 text-[10px]">
                                    {{ $manifest->flight_date ? \Carbon\Carbon::parse($manifest->flight_date)->format('M d, Y') : 'Date Pending' }}
                                </div>
                            </td>

                            <td class="px-5 py-3.5">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $manifest->total_shipments }} pkts</span>
                                <div class="text-[10px] text-slate-500 font-mono">{{ number_format($manifest->total_weight, 2) }} kg</div>
                            </td>

                            <td class="px-5 py-3.5">
                                @if($manifest->agency_emails_sent_at)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        <i class="fas fa-check text-[9px]"></i> Dispatched
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                        <i class="fas fa-clock text-[9px]"></i> Ready to Send
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5">
                                @php
                                    $stBadge = [
                                        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                                        'in_transit' => 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300',
                                        'received' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                                        'completed' => 'bg-slate-100 text-slate-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $stBadge[$manifest->status] ?? 'bg-slate-100' }}">
                                    {{ str_replace('_', ' ', $manifest->status) }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('international.manifests.show', $manifest->id) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="View Flight Manifest">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('international.manifests.data-sheet', $manifest->id) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="View Full Data Sheet">
                                    <i class="fas fa-table"></i>
                                </a>
                                <a href="{{ route('international.manifests.data-sheet', [$manifest->id, 'export' => 'csv']) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Export CSV Data Sheet">
                                    <i class="fas fa-file-csv"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                                No international manifests found. Click "Build New Manifest" to consolidate shipments.
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
