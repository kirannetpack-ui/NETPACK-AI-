@extends('layouts.app')

@section('title', 'Pre-Feed Airline MAWB | Netpack Logistics')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('international.mawbs.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 mb-1">
                <i class="fas fa-arrow-left"></i> Back to MAWB Pool
            </a>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Pre-Feed Airline MAWB</h1>
            <p class="text-sm text-slate-500">Deposit unused airline Master Airway Bills into the automated manifest pool.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-500 p-4 rounded-xl text-sm space-y-1">
            <p class="font-bold flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> Validation errors:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('international.mawbs.store') }}" method="POST" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">MAWB Number (11-Digit Standard) <span class="text-rose-500">*</span></label>
                <input type="text" name="mawb_number" value="{{ old('mawb_number') }}" required placeholder="e.g. 176-12345678, 157-98765432" class="w-full font-mono text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-sky-500">
                <p class="text-[11px] text-slate-400 mt-1">3-digit airline prefix + 8-digit serial</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">Airline Operating Carrier <span class="text-rose-500">*</span></label>
                <input type="text" name="airline_name" value="{{ old('airline_name') }}" required placeholder="e.g. Emirates Airlines, Qatar Airways, Singapore Airlines" class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-sky-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">Airline 2-Letter Code</label>
                <input type="text" name="airline_code" value="{{ old('airline_code') }}" placeholder="e.g. EK, QR, SQ, NZ" maxlength="10" class="w-full uppercase font-mono text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">Flight Number</label>
                <input type="text" name="flight_number" value="{{ old('flight_number') }}" placeholder="e.g. EK-565, QR-651" class="w-full font-mono text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">Scheduled Flight Date</label>
                <input type="date" name="flight_date" value="{{ old('flight_date') }}" class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">Origin Airport (Nepal Origin) <span class="text-rose-500">*</span></label>
                <input type="text" name="origin_airport" value="{{ old('origin_airport', 'KTM - Tribhuvan International Airport, Nepal') }}" required class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">Destination Airport</label>
                <input type="text" name="destination_airport" value="{{ old('destination_airport') }}" placeholder="e.g. DXB, LHR, SYD, AKL" class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">Designated International Hub</label>
                <select name="hub_id" class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    <option value="">-- Flexible / Unassigned Hub --</option>
                    @foreach($hubs as $hub)
                        <option value="{{ $hub->id }}" {{ old('hub_id') == $hub->id ? 'selected' : '' }}>
                            {{ $hub->code }} - {{ $hub->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">Operational Notes / Special Cargo Instructions</label>
            <textarea name="notes" rows="2" placeholder="Cargo allotment, pallet specifics, dry ice, temperature controlled..." class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('international.mawbs.index') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-sm font-semibold shadow-lg shadow-sky-600/30">
                <i class="fas fa-save mr-1"></i> Save to MAWB Pool
            </button>
        </div>
    </form>
</div>
@endsection
