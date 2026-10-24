@extends('layouts.app')

@section('title', 'Rider Fare Formula & Rate Rules - E-Commerce Delivery')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900">Rider Delivery Fare Rules</h1>
            <p class="text-xs text-slate-500 mt-0.5">Configure the transparent rule-based formula for rider payouts (zero price bidding).</p>
        </div>
        <a href="{{ route('domestic.ecommerce.riders.index') }}" class="px-3 py-1.5 text-xs font-bold bg-white border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-700 transition">
            Back to Riders
        </a>
    </div>

    <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-xs">
        <form method="POST" action="{{ route('domestic.ecommerce.riders.rates.update') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Base Distance (KM)</label>
                    <input type="number" step="0.5" name="base_distance_km" value="{{ old('base_distance_km', $rule->base_distance_km) }}" required
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500 font-bold">
                    <p class="text-[11px] text-slate-400 mt-1">Included distance for base fare (e.g. 3.0 KM).</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Base Fare (NPR)</label>
                    <input type="number" step="1" name="base_rate" value="{{ old('base_rate', $rule->base_rate) }}" required
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500 font-bold text-teal-700">
                    <p class="text-[11px] text-slate-400 mt-1">Starting payout per pickup (e.g. Rs. 80).</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Additional Per KM Rate (NPR / KM)</label>
                    <input type="number" step="1" name="additional_km_rate" value="{{ old('additional_km_rate', $rule->additional_km_rate) }}" required
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500 font-bold">
                    <p class="text-[11px] text-slate-400 mt-1">Added for every kilometer beyond base distance (e.g. Rs. 15/km).</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Weight Surcharge (NPR / KG above 1 KG)</label>
                    <input type="number" step="1" name="weight_surcharge_per_kg" value="{{ old('weight_surcharge_per_kg', $rule->weight_surcharge_per_kg) }}" required
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500 font-bold">
                    <p class="text-[11px] text-slate-400 mt-1">Extra per kilogram over 1 kg (e.g. Rs. 10/kg).</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">COD Handling Incentive (NPR)</label>
                    <input type="number" step="1" name="cod_handling_fee" value="{{ old('cod_handling_fee', $rule->cod_handling_fee) }}" required
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500 font-bold">
                    <p class="text-[11px] text-slate-400 mt-1">Paid to rider for collecting and remitting cash (e.g. Rs. 10).</p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl transition shadow-sm">
                    Save Fare Formula
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
