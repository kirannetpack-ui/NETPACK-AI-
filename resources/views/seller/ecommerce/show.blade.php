@extends('layouts.seller')

@section('title', "Consignment Details: {$assignment->master_awb}")

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900">Consignment Dispatch Dossier</h1>
            <p class="text-xs text-slate-500 mt-0.5">Master AWB: <span class="font-mono font-bold text-teal-700">{{ $assignment->master_awb }}</span></p>
        </div>
        <a href="{{ route('seller.ecommerce.index') }}" class="px-3 py-1.5 text-xs font-bold bg-white border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-700 transition">
            Back to Registry
        </a>
    </div>

    <!-- Prominent Pickup OTP Card (Show to Rider) -->
    @if(!$assignment->pickup_otp_verified_at)
    <div class="p-6 rounded-2xl bg-gradient-to-r from-teal-900 via-slate-900 to-teal-950 text-white border border-teal-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                <i class="fas fa-key text-teal-400"></i> Secure Pickup Handover
            </span>
            <h3 class="text-lg font-black mt-1">Provide This OTP to the Rider Upon Pickup</h3>
            <p class="text-xs text-slate-300 mt-0.5">
                The rider must enter this 6-digit verification code to confirm handover into their custody.
            </p>
        </div>
        <div class="text-center md:text-right bg-slate-950/60 p-4 rounded-xl border border-teal-700/60 flex-shrink-0">
            <p class="text-[10px] uppercase font-bold text-teal-400 tracking-widest">Pickup OTP Code</p>
            <p class="text-3xl font-mono font-black text-white tracking-widest mt-0.5">{{ $assignment->pickup_otp }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Single-use token</p>
        </div>
    </div>
    @else
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs text-emerald-800">
        <div class="flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
            <div>
                <p class="font-bold">Pickup OTP Verified & Parcel Handed Over</p>
                <p class="text-[11px] text-emerald-700">Verified at: {{ $assignment->pickup_otp_verified_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
        <span class="font-mono text-xs font-bold px-2 py-1 rounded bg-emerald-200 text-emerald-900">In Rider Custody</span>
    </div>
    @endif

    <!-- Consignment Details Grid -->
    <section class="np-section space-y-3">
        <h2>Recipient handover link</h2>
        <p class="text-sm text-slate-600">Share this private link directly with the recipient. It expires in 48 hours and shows their delivery code after pickup. This link has not been sent automatically.</p>
        <label for="recipient-handover-link" class="sr-only">Private recipient handover link</label>
        <input id="recipient-handover-link" type="text" readonly value="{{ $recipientLink }}" class="w-full px-3 py-2 text-xs border rounded-lg" onclick="this.select()">
    </section>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- 1. Recipient & Destination -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-3">
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700 border-b pb-2 flex items-center gap-1.5">
                <i class="fas fa-user text-teal-600"></i> Customer Destination Details
            </h4>
            <div class="space-y-1 text-xs">
                <p class="text-slate-500">Recipient Name: <strong class="text-slate-900">{{ $assignment->delivery_name }}</strong></p>
                <p class="text-slate-500">Contact Phone: <strong class="text-slate-900 font-mono">{{ $assignment->delivery_phone }}</strong></p>
                <p class="text-slate-500">Delivery Address: <strong class="text-slate-900">{{ $assignment->delivery_address }}</strong></p>
                <p class="text-slate-500">Parcel Weight: <strong class="text-slate-900">{{ $assignment->parcel_weight }} KG</strong></p>
                <p class="text-slate-500">
                    COD to Collect: 
                    <strong class="font-mono {{ $assignment->cod_amount > 0 ? 'text-amber-600' : 'text-slate-700' }}">
                        {{ $assignment->cod_amount > 0 ? 'Rs. ' . number_format($assignment->cod_amount, 2) : 'Prepaid (Rs. 0)' }}
                    </strong>
                </p>
            </div>
        </div>

        <!-- 2. Assigned Rider & Chain of Custody -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-3">
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700 border-b pb-2 flex items-center gap-1.5">
                <i class="fas fa-motorcycle text-teal-600"></i> Assigned Delivery Provider
            </h4>
            @if($assignment->riderProfile)
            <div class="space-y-1 text-xs">
                <p class="text-slate-500">Rider Name: <strong class="text-slate-900">{{ $assignment->riderProfile->full_name }}</strong></p>
                <p class="text-slate-500">Rider Code: <span class="font-mono font-bold text-teal-700">{{ $assignment->riderProfile->rider_code }}</span></p>
                <p class="text-slate-500">Mobile: <strong class="text-slate-900 font-mono">{{ $assignment->riderProfile->mobile }}</strong></p>
                <p class="text-slate-500">Vehicle: <strong class="uppercase text-slate-800">{{ $assignment->riderProfile->vehicle_type }}</strong> ({{ $assignment->riderProfile->vehicle_number ?? 'Plate N/A' }})</p>
                <p class="text-slate-500">Current Custody: <span class="font-semibold text-teal-800 bg-teal-50 px-2 py-0.5 rounded">{{ $assignment->current_custody }}</span></p>
            </div>
            @else
            <div class="p-4 rounded-xl bg-slate-50 text-center text-slate-500 text-xs">
                <i class="fas fa-satellite-dish text-teal-600 text-lg mb-1 animate-spin"></i>
                <p class="font-semibold">Broadcasting to Available Nearby Riders...</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Rider details will appear as soon as a verified provider accepts.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- 3. Rating & Performance Review (When Completed) -->
    @if($assignment->status === 'completed' && $assignment->riderProfile)
        @php
            $existingRating = \App\Models\RiderRating::where('assignment_id', $assignment->id)->where('rated_by_user_id', auth()->id())->first();
        @endphp
        <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-xs space-y-4">
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-800 border-b pb-2 flex items-center gap-2">
                <i class="fas fa-star text-amber-500"></i> Rider Performance Rating
            </h4>

            @if($existingRating)
                <div class="p-4 rounded-xl bg-amber-50/50 border border-amber-200 text-xs flex items-center justify-between">
                    <div>
                        <p class="font-bold text-amber-900">You rated this rider: {{ $existingRating->overall_rating }} / 5 Stars</p>
                        @if($existingRating->feedback)
                            <p class="text-slate-600 mt-1 italic">"{{ $existingRating->feedback }}"</p>
                        @endif
                    </div>
                    <span class="text-amber-500 text-lg font-black font-mono">
                        {{ str_repeat('★', $existingRating->overall_rating) }}
                    </span>
                </div>
            @else
                <p class="text-xs text-slate-500">
                    How was your experience with Rider <strong>{{ $assignment->riderProfile->full_name }}</strong>? Your feedback directly updates their platform Trust Score.
                </p>

                <form method="POST" action="{{ route('seller.ecommerce.rate', $assignment->id) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Overall Rating *</label>
                            <select name="overall_rating" required class="w-full border border-slate-200 rounded-xl p-2 bg-white font-bold text-amber-700">
                                <option value="5">5 ★★★★★ (Exceptional)</option>
                                <option value="4">4 ★★★★☆ (Good)</option>
                                <option value="3">3 ★★★☆☆ (Average)</option>
                                <option value="2">2 ★★☆☆☆ (Poor)</option>
                                <option value="1">1 ★☆☆☆☆ (Terrible)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Professionalism</label>
                            <select name="professionalism" class="w-full border border-slate-200 rounded-xl p-2 bg-white">
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Fair</option>
                                <option value="2">2 - Poor</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Timeliness</label>
                            <select name="timeliness" class="w-full border border-slate-200 rounded-xl p-2 bg-white">
                                <option value="5">5 - On Time</option>
                                <option value="4">4 - Minor Delay</option>
                                <option value="3">3 - Delayed</option>
                                <option value="2">2 - Very Late</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Parcel Handling</label>
                            <select name="parcel_handling" class="w-full border border-slate-200 rounded-xl p-2 bg-white">
                                <option value="5">5 - Perfect Care</option>
                                <option value="4">4 - Handled Well</option>
                                <option value="2">2 - Rough Handling</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Communication</label>
                            <select name="communication" class="w-full border border-slate-200 rounded-xl p-2 bg-white">
                                <option value="5">5 - Polite & Clear</option>
                                <option value="4">4 - Good</option>
                                <option value="2">2 - Poor Communication</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Remarks / Feedback (Optional)</label>
                        <textarea name="feedback" rows="2" placeholder="Share specific details about this delivery..."
                                  class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500"></textarea>
                    </div>

                    <button type="submit" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl transition shadow-sm">
                        Submit Rider Rating
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>
@endsection
