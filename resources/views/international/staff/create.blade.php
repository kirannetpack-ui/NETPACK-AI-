@extends('layouts.app')

@section('title', 'Create International Staff - COURIER with NETPACK')
@section('page-title', 'Create International Staff')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900 flex items-center gap-2">
                <i class="fas fa-plane-departure text-indigo-600"></i>
                <span>Create International Operations Staff</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Staff created here will only see the International section and open the International Dashboard upon login.</p>
        </div>
        <a href="{{ route('international.staff.index') }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">
            &larr; Back to Staff List
        </a>
    </div>

    <!-- Alert Box Explaining Strict Scope -->
    <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200 text-indigo-950 text-xs space-y-1">
        <div class="font-bold flex items-center gap-1.5 text-indigo-900">
            <i class="fas fa-shield-halved text-indigo-600"></i>
            <span>Department Isolation Notice</span>
        </div>
        <p class="text-slate-600">
            This staff member's credentials will be locked to the <strong>International Air Freight Service</strong>. When they log in, they will automatically be routed to the International Dashboard and sidebar with access to hubs, MAWBs, and overseas inbound scans.
        </p>
    </div>

    <!-- Creation Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <form action="{{ route('international.staff.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Suresh Shrestha (Cargo Lead)"
                       class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none">
                @error('name') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address *</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. suresh.international@netpack.test"
                       class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none font-mono">
                @error('email') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Mobile Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. 9851000000"
                           class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Operational Role Title</label>
                    <input type="text" name="role" value="{{ old('role', 'International Manifest Officer') }}" placeholder="e.g. Air Export Supervisor"
                           class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password *</label>
                    <input type="password" name="password" required placeholder="Minimum 8 characters"
                           class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none font-mono">
                    @error('password') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm Password *</label>
                    <input type="password" name="password_confirmation" required placeholder="Repeat password"
                           class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none font-mono">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('international.staff.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider shadow-sm transition flex items-center gap-2">
                    <i class="fas fa-check"></i>
                    <span>Create & Activate Staff Account</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
