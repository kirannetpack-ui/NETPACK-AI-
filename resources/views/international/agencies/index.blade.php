@extends('layouts.app')

@section('title', 'Partner Agencies | International Logistics')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden">
        <div class="absolute right-0 top-0 w-96 h-full opacity-10 pointer-events-none flex items-center justify-end pr-8">
            <i class="fas fa-building text-9xl"></i>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        <i class="fas fa-handshake mr-1"></i> Destination Hub Partners
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                        Multiple Agencies per Hub
                    </span>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight">International Partner Agencies</h1>
                <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                    Agencies operating within destination hubs (Dubai, UK, Australia, NZ, etc.) that process flight arrivals, perform customs clearance, and execute last-mile handover.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('international.hubs.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-medium border border-slate-700 transition">
                    <i class="fas fa-globe text-indigo-400"></i> Manage Hubs
                </a>
                <a href="{{ route('international.agencies.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-sm font-semibold shadow-lg shadow-amber-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fas fa-plus"></i> Add New Agency
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

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Filter By Hub:</span>
            <a href="{{ route('international.agencies.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ !request('hub_id') ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                All Hubs
            </a>
            @foreach(\App\Models\OverseasHub::orderBy('sort_order')->get() as $h)
                <a href="{{ route('international.agencies.index', ['hub_id' => $h->id]) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('hub_id') == $h->id ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                    {{ $h->code }} ({{ $h->name }})
                </a>
            @endforeach
        </div>
        <div class="text-xs text-slate-500 font-medium">
            Showing <span class="font-bold text-slate-800 dark:text-slate-200">{{ $agencies->total() }}</span> registered agencies
        </div>
    </div>

    <!-- Agencies Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($agencies as $agency)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition flex flex-col justify-between overflow-hidden">
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 flex items-center justify-center font-black text-amber-600 dark:text-amber-400 text-sm shadow-inner">
                                {{ $agency->code ?? 'AGC' }}
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white leading-tight">
                                    {{ $agency->name }}
                                </h3>
                                <p class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                                    <i class="fas fa-map-marker-alt text-rose-500"></i> {{ $agency->city ?? $agency->country ?? 'Hub Location' }}
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $agency->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600' }}">
                            {{ $agency->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <!-- Hub Tags -->
                    <div class="mb-3 space-y-1">
                        <p class="font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Gateway Hubs (Country & Airport):</p>
                        <div class="flex flex-wrap gap-1.5">
                            @php
                                $agencyHubs = $agency->hubs->isNotEmpty() ? $agency->hubs : ($agency->hub ? collect([$agency->hub]) : collect());
                            @endphp
                            @forelse($agencyHubs as $hub)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 text-xs font-semibold text-indigo-700 dark:text-indigo-300" title="{{ $hub->airport_name ?? $hub->address }}">
                                    <i class="fas fa-plane-departure text-indigo-500 text-[10px]"></i>
                                    <span class="font-mono text-[10px] font-bold">{{ $hub->hub_code }}</span> {{ $hub->country }}
                                </span>
                            @empty
                                <span class="text-xs text-slate-400 italic">No assigned hub</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Notification Emails (Air-Cargo Dispatch List) -->
                    <div class="space-y-1 py-2 border-t border-slate-100 dark:border-slate-800 text-xs">
                        <p class="font-semibold text-slate-500 uppercase tracking-wider text-[10px]">Auto-Dispatch Email Targets:</p>
                        @php
                            $emailList = $agency->notification_email_list ?? $agency->notification_emails ?? [];
                            if (empty($emailList) && $agency->email) {
                                $emailList = [$agency->email];
                            }
                        @endphp
                        <div class="flex flex-wrap gap-1">
                            @forelse($emailList as $em)
                                <span class="px-2 py-0.5 rounded-md text-[11px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono border border-slate-200 dark:border-slate-700 flex items-center gap-1">
                                    <i class="fas fa-envelope text-slate-400 text-[9px]"></i> {{ $em }}
                                </span>
                            @empty
                                <span class="text-amber-500 text-[11px] italic">No dispatch emails configured</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Staff & Telemetry Info -->
                    <div class="pt-2 text-xs text-slate-500 flex items-center justify-between border-t border-slate-100 dark:border-slate-800">
                        <span>Agency Staff / Scanners:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">
                            {{ $agency->staff_count ?? $agency->staff->count() }} Registered
                        </span>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                    <a href="{{ route('international.agencies.format-settings', $agency->id) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center gap-1" title="Customize Manifest & Data Sheet Formats">
                        <i class="fas fa-table text-emerald-500"></i> Format Settings
                    </a>
                    <div class="flex items-center gap-1.5">
                        <form action="{{ route('international.agencies.reset-password', $agency->id) }}" method="POST" onsubmit="return confirm('Generate a new random password for {{ addslashes($agency->name) }}?');" class="inline">
                            @csrf
                            <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-slate-200 dark:hover:bg-slate-700 transition" title="Reset Password">
                                <i class="fas fa-key"></i>
                            </button>
                        </form>
                        <a href="{{ route('international.agencies.edit', $agency->id) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-200 dark:hover:bg-slate-700 transition" title="Edit Agency">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('international.agencies.destroy', $agency->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this Agency?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-200 dark:hover:bg-slate-700 transition" title="Delete Agency">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-12 text-center">
                <i class="fas fa-building text-4xl text-slate-400 mb-3"></i>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">No Partner Agencies Found</h3>
                <p class="text-sm text-slate-500 mt-1">Add agencies to connect hubs with local arrival desks and last-mile dispatch.</p>
                <div class="mt-4">
                    <a href="{{ route('international.agencies.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-sm font-semibold">
                        <i class="fas fa-plus"></i> Add First Agency
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $agencies->links() }}
    </div>
</div>
@endsection
