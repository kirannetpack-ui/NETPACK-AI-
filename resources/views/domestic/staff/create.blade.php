@extends('layouts.app')

@section('title', 'Create Domestic Staff - COURIER with NETPACK')
@section('page-title', 'Create Domestic Staff')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900 flex items-center gap-2">
                <i class="fas fa-truck text-teal-600"></i>
                <span>Create Domestic Operations Staff</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Staff created here will only see the Domestic section and open the Domestic Dashboard upon login.</p>
        </div>
        <a href="{{ route('domestic.staff.index') }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">
            &larr; Back to Staff List
        </a>
    </div>

    <!-- Alert Box Explaining Strict Scope -->
    <div class="p-4 rounded-2xl bg-teal-50/70 border border-teal-200 text-teal-950 text-xs space-y-1">
        <div class="font-bold flex items-center gap-1.5 text-teal-900">
            <i class="fas fa-shield-halved text-teal-600"></i>
            <span>Department Isolation Notice</span>
        </div>
        <p class="text-slate-600">
            This staff member's credentials will be locked to <strong>Nepal Domestic Logistics & E-Commerce</strong>. When they log in, they will automatically be routed to the Domestic Dashboard and sidebar with access to provincial hubs, sortation manifests, scan desks, and merchant orders.
        </p>
    </div>

    <!-- Creation Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <form action="{{ route('domestic.staff.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Binod Thapa (Pokhara Hub Lead)"
                       class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500 outline-none">
                @error('name') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address *</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. binod.domestic@netpack.test"
                       class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500 outline-none font-mono">
                @error('email') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Mobile Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. 9841000000"
                           class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500 outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Specialized Sub-Scope</label>
                    <select name="service_scope" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500 outline-none bg-white">
                        <option value="domestic" selected>Nepal Domestic Logistics (7 Provinces & Linehauls)</option>
                        <option value="ecommerce">E-Commerce & Rider Delivery Fleet</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Operational Role Title</label>
                <input type="text" name="role" value="{{ old('role', 'Hub Sortation Officer') }}" placeholder="e.g. Regional Scan Incharge"
                       class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500 outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password *</label>
                    <input type="password" name="password" required placeholder="Minimum 8 characters"
                           class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500 outline-none font-mono">
                    @error('password') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm Password *</label>
                    <input type="password" name="password_confirmation" required placeholder="Repeat password"
                           class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500 outline-none font-mono">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('domestic.staff.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold uppercase tracking-wider shadow-sm transition flex items-center gap-2">
                    <i class="fas fa-check"></i>
                    <span>Create & Activate Staff Account</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
