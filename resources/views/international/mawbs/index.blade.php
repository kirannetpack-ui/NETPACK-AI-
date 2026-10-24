@extends('layouts.app')

@section('title', 'MAWB Inventory & Pre-fed Pool | International Logistics')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-sky-950 to-indigo-950 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden">
        <div class="absolute right-0 top-0 w-96 h-full opacity-10 pointer-events-none flex items-center justify-end pr-8">
            <i class="fas fa-plane-departure text-9xl"></i>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-500/20 text-sky-300 border border-sky-500/30">
                        <i class="fas fa-barcode mr-1"></i> Master Airway Bill (MAWB) Pool
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        Automatic Binding Engine Ready
                    </span>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight">MAWB Inventory Pool</h1>
                <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                    Pre-feed airline MAWB stock into the system. During flight manifest generation, the system displays all unused MAWBs or automatically binds the manifest to an available MAWB.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('international.manifests.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-medium border border-slate-700 shadow-sm transition">
                    <i class="fas fa-file-invoice text-indigo-400"></i> Manifests
                </a>
                <a href="{{ route('international.mawbs.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-sm font-semibold shadow-lg shadow-sky-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fas fa-plus"></i> Pre-Feed New MAWB
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

    @if(session('error'))
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 px-4 py-3 rounded-xl flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-lg"></i>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total MAWBs</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 text-lg">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>

        <div class="bg-emerald-50/50 dark:bg-emerald-950/20 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Unused / Ready</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-300 mt-1">{{ number_format($stats['unused']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 dark:text-emerald-300 text-lg">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="bg-blue-50/50 dark:bg-blue-950/20 rounded-2xl border border-blue-200 dark:border-blue-800/60 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wider">Assigned</p>
                    <p class="text-2xl font-black text-blue-600 dark:text-blue-300 mt-1">{{ number_format($stats['assigned']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-600 dark:text-blue-300 text-lg">
                    <i class="fas fa-clipboard-check"></i>
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
                    <i class="fas fa-plane"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('international.mawbs.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative">
                <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="MAWB #, airline, or flight..." class="pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 w-56 focus:ring-2 focus:ring-sky-500">
            </div>

            <select name="status" onchange="this.form.submit()" class="py-2 px-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-sky-500">
                <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                <option value="unused" {{ request('status') === 'unused' ? 'selected' : '' }}>Unused (Available)</option>
                <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned to Manifest</option>
                <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Flight / Transit</option>
                <option value="cleared" {{ request('status') === 'cleared' ? 'selected' : '' }}>Cleared</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>

            <select name="hub_id" onchange="this.form.submit()" class="py-2 px-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-sky-500">
                <option value="">-- All Hubs --</option>
                @foreach($hubs as $hub)
                    <option value="{{ $hub->id }}" {{ request('hub_id') == $hub->id ? 'selected' : '' }}>{{ $hub->code }} - {{ $hub->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-3.5 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status', 'hub_id']))
                <a href="{{ route('international.mawbs.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-700">Clear</a>
            @endif
        </form>
    </div>

    <!-- MAWB Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">MAWB Number</th>
                        <th class="px-5 py-3.5">Airline & Flight</th>
                        <th class="px-5 py-3.5">Route (Origin &rarr; Dest)</th>
                        <th class="px-5 py-3.5">Target Hub</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Bound Manifest</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($mawbs as $mawb)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-sky-50 dark:bg-sky-950 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-xs border border-sky-200 dark:border-sky-800">
                                    <i class="fas fa-barcode"></i>
                                </span>
                                <div>
                                    <span>{{ $mawb->mawb_number }}</span>
                                    <p class="text-[10px] text-slate-400 font-normal">Pre-fed: {{ $mawb->created_at->format('M d, Y') }}</p>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $mawb->airline_name }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">
                                    {{ $mawb->flight_number ?? 'Flexible Flight' }} 
                                    @if($mawb->flight_date)
                                        &bull; {{ \Carbon\Carbon::parse($mawb->flight_date)->format('M d, Y') }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-[11px]">
                                <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $mawb->origin_airport }}</span>
                                <i class="fas fa-arrow-right text-[9px] text-slate-400 mx-1"></i>
                                <span class="text-slate-700 dark:text-slate-300 font-bold">{{ $mawb->destination_airport ?? 'Hub Gateway' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($mawb->hub)
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                        {{ $mawb->hub->code }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Unassigned Hub</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @php
                                    $statusClasses = [
                                        'unused' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800',
                                        'assigned' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border border-blue-300 dark:border-blue-800',
                                        'in_transit' => 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 border border-purple-300 dark:border-purple-800',
                                        'cleared' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800',
                                        'completed' => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 border border-slate-300 dark:border-slate-700',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider {{ $statusClasses[$mawb->status] ?? 'bg-slate-100 text-slate-700' }}">
                                    @if($mawb->status === 'unused')
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                    @endif
                                    {{ str_replace('_', ' ', $mawb->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($mawb->assigned_manifest_id && $mawb->manifest)
                                    <a href="{{ route('international.manifests.show', $mawb->assigned_manifest_id) }}" class="font-mono text-indigo-600 dark:text-indigo-400 hover:underline font-bold flex items-center gap-1">
                                        <i class="fas fa-file-alt text-xs"></i> {{ $mawb->manifest->manifest_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400 italic">None (Pool Available)</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('international.mawbs.edit', $mawb->id) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-sky-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit MAWB">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($mawb->status === 'unused')
                                    <form action="{{ route('international.mawbs.destroy', $mawb->id) }}" method="POST" onsubmit="return confirm('Delete unused MAWB #{{ $mawb->mawb_number }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                No MAWBs found matching your filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
            {{ $mawbs->links() }}
        </div>
    </div>
</div>
@endsection
