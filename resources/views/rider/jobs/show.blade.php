@extends('layouts.app')

@section('title', "Delivery Execution: {$assignment->master_awb}")

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900">Delivery Execution Cockpit</h1>
            <p class="text-xs text-slate-500 mt-0.5">Master AWB: <span class="font-mono font-bold text-teal-700">{{ $assignment->master_awb }}</span></p>
        </div>
        <a href="{{ route('rider.delivery.my') }}" class="px-3 py-1.5 text-xs font-bold bg-white border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-700 transition">
            Back to Deliveries
        </a>
    </div>

    <!-- Status Progression Banner -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-between text-xs">
        <div>
            <span class="text-[10px] uppercase font-bold text-slate-400">Current Phase</span>
            <p class="font-black text-sm uppercase text-teal-800">{{ str_replace('_', ' ', $assignment->status) }}</p>
        </div>
        <div class="text-right">
            <span class="text-[10px] uppercase font-bold text-slate-400">Chain of Custody</span>
            <p class="font-bold text-slate-700">{{ $assignment->current_custody }}</p>
        </div>
    </div>

    <!-- PHASE 1: PICKUP VERIFICATION (If not yet picked up) -->
    @if(!$assignment->pickup_otp_verified_at)
    <div class="p-6 rounded-2xl bg-white border-2 border-teal-500 shadow-xs space-y-4">
        <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-teal-100 text-teal-700 font-bold flex items-center justify-center text-xs">1</span>
            <h3 class="font-bold text-sm text-slate-900">First-Mile Pickup Verification</h3>
        </div>

        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1">
            <p class="font-bold text-slate-800">Pickup Origin (Seller / Store):</p>
            <p class="text-slate-700"><i class="fas fa-store text-teal-600 mr-1"></i> {{ $assignment->pickup_name }}</p>
            <p class="text-slate-700"><i class="fas fa-phone text-teal-600 mr-1"></i> <a href="tel:{{ $assignment->pickup_phone }}" class="text-teal-700 font-bold underline">{{ $assignment->pickup_phone }}</a></p>
            <p class="text-slate-700"><i class="fas fa-location-dot text-teal-600 mr-1"></i> {{ $assignment->pickup_address }}</p>
        </div>

        <div class="pt-2">
            <form method="POST" action="{{ route('rider.delivery.verify-pickup', $assignment->id) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-800 uppercase mb-1">
                        Enter 6-Digit Pickup OTP (Provided by Seller) *
                    </label>
                    <input type="text" name="pickup_otp" maxlength="6" required placeholder="e.g. 123456"
                           class="w-full text-center tracking-widest text-2xl font-mono font-black border-2 border-teal-400 rounded-xl p-3 focus:outline-none focus:border-teal-600">
                </div>

                <button type="submit" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-black text-xs rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                    <i class="fas fa-box-check"></i> Verify OTP & Accept Parcel Custody
                </button>
            </form>
        </div>
    </div>
    @else
    <!-- PHASE 2: CUSTOMER DELIVERY (Picked Up -> Deliver) -->
    <div class="p-6 rounded-2xl bg-white border-2 border-emerald-500 shadow-xs space-y-4">
        <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-xs">2</span>
            <h3 class="font-bold text-sm text-slate-900">Customer Delivery Handover & COD</h3>
        </div>

        <!-- Destination Details -->
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-2">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase">Customer Name</p>
                <p class="font-black text-base text-slate-900">{{ $assignment->delivery_name }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase">Customer Phone (Call for Delivery)</p>
                <a href="tel:{{ $assignment->delivery_phone }}" class="text-teal-700 font-mono font-black text-base underline flex items-center gap-1.5">
                    <i class="fas fa-phone-volume"></i> {{ $assignment->delivery_phone }}
                </a>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase">Delivery Address</p>
                <p class="font-bold text-slate-800 text-xs">{{ $assignment->delivery_address }}</p>
            </div>
        </div>

        <!-- COD Collection Notice -->
        @if($assignment->cod_amount > 0)
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-xs flex items-center justify-between">
            <div>
                <p class="font-black text-sm uppercase text-amber-800">Cash on Delivery Required</p>
                <p class="text-[11px] text-amber-700">Collect this exact amount from customer before releasing parcel.</p>
            </div>
            <p class="text-2xl font-mono font-black text-amber-700">Rs. {{ number_format($assignment->cod_amount, 2) }}</p>
        </div>
        @else
        <div class="p-3 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 text-xs flex items-center gap-2">
            <i class="fas fa-circle-check text-teal-600"></i>
            <span class="font-bold">Prepaid Consignment: Do NOT collect cash from customer.</span>
        </div>
        @endif

        <!-- Delivery Completion Form -->
        <form method="POST" action="{{ route('rider.delivery.complete', $assignment->id) }}" enctype="multipart/form-data" class="space-y-4 pt-2">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1">
                    Enter 6-Digit Delivery OTP (Provided by Customer) *
                </label>
                <input type="text" name="delivery_otp" maxlength="6" required placeholder="Customer OTP"
                       class="w-full text-center tracking-widest text-2xl font-mono font-black border-2 border-emerald-400 rounded-xl p-3 focus:outline-none focus:border-emerald-600">
                <p class="text-[11px] text-slate-400 mt-1 text-center">Customer receives this OTP on their phone / tracking link.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Recipient Name (If different)</label>
                    <input type="text" name="recipient_name" placeholder="Receiver full name" value="{{ $assignment->delivery_name }}"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Proof of Delivery (POD) Photo</label>
                    <input type="file" name="pod_photo" accept="image/*"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2 bg-slate-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-emerald-600 file:text-white">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                <i class="fas fa-check-double"></i> Complete Delivery & Credit Earnings
            </button>
        </form>

        <!-- Report Failure Dropdown/Modal -->
        <div class="pt-4 border-t border-slate-100">
            <details class="group">
                <summary class="text-xs font-bold text-red-600 hover:text-red-700 cursor-pointer flex items-center gap-1.5">
                    <i class="fas fa-triangle-exclamation"></i> Customer Unavailable or Delivery Failed? Click to Report
                </summary>
                <form method="POST" action="{{ route('rider.delivery.fail', $assignment->id) }}" class="space-y-3 mt-3 p-4 bg-red-50/50 rounded-xl border border-red-200">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-red-800 mb-1">Failure Reason *</label>
                        <select name="failure_reason" required class="w-full text-xs border border-red-200 rounded-xl p-2 bg-white">
                            <option value="customer_unavailable">Customer Unavailable / Door Closed</option>
                            <option value="phone_unreachable">Phone Switched Off / Unreachable</option>
                            <option value="customer_refused">Customer Refused Parcel</option>
                            <option value="insufficient_cash">Insufficient COD Cash</option>
                            <option value="wrong_address">Wrong Address / Unable to Locate</option>
                            <option value="rescheduled">Customer Requested Reschedule</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-red-800 mb-1">Notes</label>
                        <textarea name="failure_notes" rows="2" placeholder="Explain failure situation..." class="w-full text-xs border border-red-200 rounded-xl p-2 bg-white"></textarea>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl transition">
                        Submit Failed Delivery Report
                    </button>
                </form>
            </details>
        </div>
    </div>
    @endif
</div>
@endsection
