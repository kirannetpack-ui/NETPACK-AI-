@extends('layouts.app')

@section('title', "Rider KYC Dossier: {$rider->full_name} ({$rider->rider_code})")

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('domestic.ecommerce.riders.index') }}" class="hover:text-teal-600 font-medium">Direct Rider Network</a>
            <span>/</span>
            <span class="text-slate-800 font-bold">{{ $rider->rider_code }}</span>
        </div>
        <a href="{{ route('domestic.ecommerce.riders.index') }}" class="px-3 py-1.5 text-xs font-bold bg-white border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-700 transition flex items-center gap-1.5">
            <i class="fas fa-arrow-left"></i> Back to Riders
        </a>
    </div>

    <!-- Rider Dossier Header Card -->
    <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-teal-600 to-emerald-500 text-white flex items-center justify-center text-2xl font-black shadow-sm flex-shrink-0">
                {{ strtoupper(substr($rider->full_name, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-black text-slate-900">{{ $rider->full_name }}</h2>
                    <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">{{ $rider->rider_code }}</span>
                    @if($rider->verification_status === 'verified')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            <i class="fas fa-check-circle text-[9px]"></i> KYC Verified
                        </span>
                    @elseif($rider->verification_status === 'pending')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                            <i class="fas fa-clock text-[9px]"></i> Pending Review
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    <i class="fas fa-phone text-slate-400 mr-1"></i> {{ $rider->mobile }} &bull;
                    <i class="fas fa-envelope text-slate-400 mr-1 ml-2"></i> {{ $rider->email ?? 'No email' }} &bull;
                    <i class="fas fa-location-dot text-slate-400 mr-1 ml-2"></i> {{ $rider->district ?? 'Kathmandu' }}, {{ $rider->province ?? 'Bagmati' }}
                </p>
                <div class="flex items-center gap-3 mt-2 text-xs">
                    <span class="text-slate-600 font-semibold">
                        Vehicle: <strong class="uppercase text-slate-800">{{ $rider->vehicle_type }}</strong> ({{ $rider->vehicle_number ?? 'N/A' }})
                    </span>
                    <span class="text-slate-400">&bull;</span>
                    <span class="text-slate-600 font-semibold">
                        Affiliation: 
                        @if($rider->affiliation && $rider->affiliation !== 'none')
                            <span class="text-sky-600 font-bold uppercase">{{ $rider->affiliation }}</span> (Informational)
                        @else
                            <span class="text-slate-800 font-bold">Independent / Freelancer</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="flex items-center gap-3 border-t md:border-t-0 md:border-l border-slate-100 pt-3 md:pt-0 md:pl-6">
            <div class="text-right">
                <p class="text-[11px] font-semibold text-slate-400">Current COD Limit</p>
                <p class="text-lg font-black text-teal-700">Rs. {{ number_format($rider->cod_limit) }}</p>
                <p class="text-[10px] uppercase font-mono text-slate-500">Tier: {{ $rider->cod_level }}</p>
            </div>
            <div class="text-right pl-4 border-l border-slate-100">
                <p class="text-[11px] font-semibold text-slate-400">Outstanding COD</p>
                <p class="text-lg font-black {{ $rider->current_outstanding_cod > 0 ? 'text-amber-600' : 'text-slate-700' }}">
                    Rs. {{ number_format($rider->current_outstanding_cod) }}
                </p>
                <p class="text-[10px] text-slate-400">{{ $rider->total_completed_deliveries }} completed</p>
            </div>
        </div>
    </div>

    <!-- KYC Action Panel (If Pending or Updating) -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-teal-900 to-slate-900 text-white border border-teal-800/60 shadow-sm">
        <h3 class="font-bold text-sm text-teal-300 uppercase tracking-wider mb-2">
            <i class="fas fa-shield-halved mr-1"></i> Admin Verification & COD Limit Authority
        </h3>
        <p class="text-xs text-slate-300 mb-4 max-w-2xl">
            Review the uploaded credentials below. To verify this rider, select an initial COD authorization tier. Once approved, the rider can immediately receive broadcast deliveries.
        </p>

        <form method="POST" action="{{ route('domestic.ecommerce.riders.verify', $rider->id) }}" class="flex flex-wrap items-center gap-3">
            @csrf
            <div>
                <label class="block text-[10px] font-bold text-slate-300 uppercase mb-1">COD Authorization Level</label>
                <select name="cod_level" class="px-3 py-2 text-xs bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-teal-400 font-semibold">
                    <option value="level_1" {{ $rider->cod_level === 'level_1' ? 'selected' : '' }}>Level 1 — Rs. 5,000 (New Verified Rider)</option>
                    <option value="level_2" {{ $rider->cod_level === 'level_2' ? 'selected' : '' }}>Level 2 — Rs. 20,000 (Proven 50+ Deliveries)</option>
                    <option value="level_3" {{ $rider->cod_level === 'level_3' ? 'selected' : '' }}>Level 3 — Rs. 50,000 (Trusted High-Volume)</option>
                    <option value="level_0" {{ $rider->cod_level === 'level_0' ? 'selected' : '' }}>Level 0 — Rs. 0 (Prepaid Orders Only)</option>
                </select>
            </div>

            <div class="pt-5 flex items-center gap-2">
                <button type="submit" class="px-5 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-black text-xs transition shadow-sm flex items-center gap-1.5">
                    <i class="fas fa-badge-check"></i> Approve & Verify Rider
                </button>
            </div>
        </form>
    </div>

    <!-- Credentials & Documents Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 1. Identity & Citizenship -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700 border-b pb-2 flex items-center justify-between">
                <span>Citizenship / ID</span>
                <span class="text-teal-600 font-mono">{{ $rider->citizenship_number ?? 'Not Provided' }}</span>
            </h4>
            <div class="space-y-3">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500 mb-1">Citizenship Front</p>
                    @if($rider->citizenship_front_path)
                        <a href="{{ route('rider.documents.show', ['rider' => $rider->id, 'field' => 'citizenship_front_path']) }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-200 hover:opacity-90 transition max-h-36">
                            <img src="{{ route('rider.documents.show', ['rider' => $rider->id, 'field' => 'citizenship_front_path']) }}" alt="Citizenship Front" class="w-full h-36 object-cover">
                        </a>
                    @else
                        <div class="p-4 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center text-slate-400 text-xs">
                            No front image uploaded
                        </div>
                    @endif
                </div>

                <div>
                    <p class="text-[11px] font-semibold text-slate-500 mb-1">Citizenship Back</p>
                    @if($rider->citizenship_back_path)
                        <a href="{{ route('rider.documents.show', ['rider' => $rider->id, 'field' => 'citizenship_back_path']) }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-200 hover:opacity-90 transition max-h-36">
                            <img src="{{ route('rider.documents.show', ['rider' => $rider->id, 'field' => 'citizenship_back_path']) }}" alt="Citizenship Back" class="w-full h-36 object-cover">
                        </a>
                    @else
                        <div class="p-4 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center text-slate-400 text-xs">
                            No back image uploaded
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 2. Vehicle & Driving License -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700 border-b pb-2 flex items-center justify-between">
                <span>Driving License</span>
                <span class="text-teal-600 font-mono">{{ $rider->driving_license_number ?? 'Not Provided' }}</span>
            </h4>
            <div class="space-y-3">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500 mb-1">License Photo</p>
                    @if($rider->driving_license_doc_path)
                        <a href="{{ route('rider.documents.show', ['rider' => $rider->id, 'field' => 'driving_license_doc_path']) }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-200 hover:opacity-90 transition max-h-36">
                            <img src="{{ route('rider.documents.show', ['rider' => $rider->id, 'field' => 'driving_license_doc_path']) }}" alt="License Photo" class="w-full h-36 object-cover">
                        </a>
                    @else
                        <div class="p-4 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center text-slate-400 text-xs">
                            No license image uploaded
                        </div>
                    @endif
                </div>

                <div>
                    <p class="text-[11px] font-semibold text-slate-500 mb-1">Vehicle Bluebook / Registration</p>
                    @if($rider->vehicle_registration_doc_path)
                        <a href="{{ route('rider.documents.show', ['rider' => $rider->id, 'field' => 'vehicle_registration_doc_path']) }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-200 hover:opacity-90 transition max-h-36">
                            <img src="{{ route('rider.documents.show', ['rider' => $rider->id, 'field' => 'vehicle_registration_doc_path']) }}" alt="Vehicle Registration" class="w-full h-36 object-cover">
                        </a>
                    @else
                        <div class="p-4 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center text-slate-400 text-xs">
                            No vehicle document uploaded
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. Profile Selfie & Remittance -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700 border-b pb-2 flex items-center justify-between">
                <span>Selfie & Payout Info</span>
                <span class="text-slate-400">Verification</span>
            </h4>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 mb-1">Rider Selfie</p>
                @if($rider->selfie_photo_path)
                    <a href="{{ route('rider.documents.show', ['rider' => $rider->id, 'field' => 'selfie_photo_path']) }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-200 hover:opacity-90 transition max-h-36">
                        <img src="{{ route('rider.documents.show', ['rider' => $rider->id, 'field' => 'selfie_photo_path']) }}" alt="Rider Selfie" class="w-full h-36 object-cover">
                    </a>
                @else
                    <div class="p-4 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center text-slate-400 text-xs">
                        No selfie uploaded
                    </div>
                @endif
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1">
                <p class="font-bold text-slate-800">Earnings Payout Account:</p>
                <p class="text-slate-600">Bank: <span class="font-semibold">{{ $rider->bank_name ?? 'N/A' }}</span></p>
                <p class="text-slate-600">Account: <span class="font-mono">{{ $rider->bank_account_number ?? 'N/A' }}</span></p>
                <p class="text-slate-600">eSewa: <span class="font-mono">{{ $rider->esewa_id ?? 'N/A' }}</span></p>
                <p class="text-slate-600">Khalti: <span class="font-mono">{{ $rider->khalti_id ?? 'N/A' }}</span></p>
            </div>
        </div>
    </div>
</div>
@endsection
