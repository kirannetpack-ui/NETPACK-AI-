@extends('layouts.app')

@section('title', 'International Operations Staff - COURIER with NETPACK')
@section('page-title', 'International Operations Staff')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 text-white border border-indigo-500/30 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 font-mono">
                    ✈️ Service 1 Operations Team
                </span>
                <span class="text-xs text-slate-400">&bull; Scoped strictly to International Air Freight</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight">International Operations Staff</h1>
            <p class="text-xs text-slate-300 mt-1 max-w-xl">
                Staff members created here only have permission to view and access the International section (Gateway Hubs, MAWB Pools, Flight Manifests, and Agency Desks).
            </p>
        </div>
        <div>
            <a href="{{ route('international.staff.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-teal-600 hover:from-indigo-500 hover:to-teal-500 text-white font-bold text-xs shadow-md transition transform active:scale-95">
                <i class="fas fa-user-plus"></i>
                <span>Create International Staff</span>
            </a>
        </div>
    </div>

    <!-- Staff Registry Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-black text-sm text-slate-900">Active International Staff Members ({{ $staff->total() }})</h3>
            <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                SCOPE: INTERNATIONAL ONLY
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Staff Name & Email</th>
                        <th class="px-6 py-3">Assigned Service Scope</th>
                        <th class="px-6 py-3">Default Dashboard</th>
                        <th class="px-6 py-3">Phone</th>
                        <th class="px-6 py-3">Created On</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($staff as $s)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-3.5">
                                <div class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($s->name, 0, 1)) }}
                                    </div>
                                    <span>{{ $s->name }}</span>
                                </div>
                                <p class="text-slate-500 text-[11px] ml-9 font-mono">{{ $s->email }}</p>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <i class="fas fa-plane-departure text-[10px]"></i> International Air Freight
                                </span>
                            </td>
                            <td class="px-6 py-3.5 font-mono text-[11px] text-slate-600">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">/international/dashboard</span>
                            </td>
                            <td class="px-6 py-3.5 font-mono text-[11px] text-slate-600">
                                {{ $s->phone ?? '—' }}
                            </td>
                            <td class="px-6 py-3.5 text-slate-500 text-[11px]">
                                {{ $s->created_at ? $s->created_at->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <form action="{{ route('international.staff.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this staff member?');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-[11px] font-bold transition">
                                        <i class="fas fa-trash-can mr-1"></i> Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="fas fa-users text-3xl mb-2 block text-slate-300"></i>
                                <p class="text-sm font-semibold text-slate-600">No international operations staff registered yet.</p>
                                <p class="text-xs text-slate-400 mt-1">Create staff members to manage air cargo manifests and overseas arrivals.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($staff->hasPages())
            <div class="px-6 py-3 border-t border-slate-100">
                {{ $staff->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
