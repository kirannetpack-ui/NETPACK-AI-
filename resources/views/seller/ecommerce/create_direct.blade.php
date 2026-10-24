@extends('layouts.seller')

@section('title', 'Book Direct Rider Delivery - Same-Day Express')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- 3-Tier Global Dispatch Desks Banner -->
    <div class="bg-slate-900 text-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-800 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Global Dispatch Desks</span>
                <h2 class="text-base font-bold text-white mt-0.5">Select Shipment Service Tier</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('shipments.create', ['shipment_type' => 'ecommerce']) }}" class="px-3 py-1.5 rounded-xl bg-teal-500/20 text-teal-300 hover:bg-teal-500/30 text-xs font-bold transition border border-teal-500/30 flex items-center gap-1.5">
                    <i class="fas fa-boxes-packing text-teal-400"></i>
                    <span>Unified Booking Console &rarr;</span>
                </a>
                <a href="{{ route('rates.inquiry') }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 text-xs font-bold transition border border-amber-500/30 flex items-center gap-1.5">
                    <i class="fas fa-calculator text-amber-400"></i>
                    <span>Rates</span>
                </a>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            <!-- 1. Rider Delivery (Active here) -->
            <div class="p-3.5 rounded-2xl bg-emerald-600 text-white font-bold text-xs flex items-center gap-3 shadow-sm border border-emerald-500">
                <div class="w-8 h-8 rounded-xl bg-emerald-700/80 flex items-center justify-center text-sm">
                    <i class="fas fa-motorcycle"></i>
                </div>
                <div>
                    <p class="leading-tight">1. Rider Delivery</p>
                    <p class="text-[10px] font-normal text-emerald-100">Local City E-Commerce</p>
                </div>
                <span class="ml-auto text-[9px] bg-white/20 px-2 py-0.5 rounded-full uppercase tracking-wider">Current</span>
            </div>

            <!-- 2. Domestic Courier -->
            <a href="{{ route('shipments.create', ['shipment_type' => 'domestic']) }}" 
               class="p-3.5 rounded-2xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-white font-semibold text-xs flex items-center gap-3 transition border border-slate-700/80">
                <div class="w-8 h-8 rounded-xl bg-slate-700 flex items-center justify-center text-sm text-teal-400">
                    <i class="fas fa-truck-fast"></i>
                </div>
                <div>
                    <p class="leading-tight">2. Domestic Courier</p>
                    <p class="text-[10px] font-normal text-slate-400">Nepal 7 Provinces</p>
                </div>
            </a>

            <!-- 3. International Air Cargo -->
            <a href="{{ route('shipments.create', ['shipment_type' => 'international']) }}" 
               class="p-3.5 rounded-2xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-white font-semibold text-xs flex items-center gap-3 transition border border-slate-700/80">
                <div class="w-8 h-8 rounded-xl bg-slate-700 flex items-center justify-center text-sm text-sky-400">
                    <i class="fas fa-plane-departure"></i>
                </div>
                <div>
                    <p class="leading-tight">3. International Cargo</p>
                    <p class="text-[10px] font-normal text-slate-400">Worldwide Air Freight</p>
                </div>
            </a>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900">Direct Rider Dispatch Desk</h1>
            <p class="text-xs text-slate-500 mt-0.5">Instant same-day delivery directly from Seller &rarr; Local Rider &rarr; Customer.</p>
        </div>
        <a href="{{ route('seller.ecommerce.index') }}" class="px-3 py-1.5 text-xs font-bold bg-white border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-700 transition">
            Back to Registry
        </a>
    </div>

    <!-- Rate Formula Card -->
    <div class="p-4 rounded-2xl bg-teal-50/80 border border-teal-200 flex items-center justify-between text-xs text-teal-800">
        <div>
            <span class="font-black uppercase text-[10px] tracking-wider bg-teal-200 text-teal-900 px-2 py-0.5 rounded">Pricing Standard</span>
            <p class="mt-1">
                Base Fare: <strong>Rs. {{ number_format($rateRule->base_rate) }}</strong> (up to {{ $rateRule->base_distance_km }} KM) &bull;
                Additional: <strong>Rs. {{ number_format($rateRule->additional_km_rate) }}/KM</strong> &bull;
                COD Handling: <strong>Rs. {{ number_format($rateRule->cod_handling_fee) }}</strong>
            </p>
        </div>
        <i class="fas fa-motorcycle text-2xl text-teal-500"></i>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <form method="POST" action="{{ route('seller.ecommerce.direct.store') }}" class="space-y-6">
            @csrf

            <!-- Pickup Details (Prefilled with Seller Info) -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 border-b pb-2 flex items-center gap-2">
                    <i class="fas fa-store text-teal-600"></i> 1. Pickup Origin (Your Store / Warehouse)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Store / Contact Name</label>
                        <input type="text" name="pickup_name" value="{{ old('pickup_name', $seller->business_name ?? $seller->name) }}" required
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:outline-none focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Pickup Mobile</label>
                        <input type="text" name="pickup_phone" value="{{ old('pickup_phone', $seller->phone ?? '') }}" required
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:outline-none focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Pickup Address / Area</label>
                        <input type="text" name="pickup_address" value="{{ old('pickup_address', $seller->address ?? 'Kathmandu') }}" required
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:outline-none focus:border-teal-500">
                    </div>
                </div>
            </div>

            <!-- Customer Destination Details -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 border-b pb-2 flex items-center gap-2">
                    <i class="fas fa-location-dot text-teal-600"></i> 2. Customer Destination
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Customer Full Name *</label>
                        <input type="text" name="delivery_name" value="{{ old('delivery_name') }}" required placeholder="e.g. Suman Thapa"
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Customer Mobile Number *</label>
                        <input type="text" name="delivery_phone" value="{{ old('delivery_phone') }}" required placeholder="e.g. 9841000000"
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Delivery Street Address / Landmark *</label>
                        <input type="text" name="delivery_address" value="{{ old('delivery_address') }}" required placeholder="e.g. Baneshwor, near Eye Hospital"
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                    </div>
                </div>
            </div>

            <!-- Package Specifications & COD -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 border-b pb-2 flex items-center gap-2">
                    <i class="fas fa-box text-teal-600"></i> 3. Parcel & Payment Specifications
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Est. Distance (KM) *</label>
                        <input type="number" step="0.5" name="distance_km" value="{{ old('distance_km', 5.0) }}" required
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500 font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Parcel Weight (KG) *</label>
                        <input type="number" step="0.5" name="parcel_weight" value="{{ old('parcel_weight', 1.0) }}" required
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500 font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">COD Amount to Collect (NPR)</label>
                        <input type="number" step="1" name="cod_amount" value="{{ old('cod_amount', 0) }}"
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500 font-bold text-amber-600">
                        <p class="text-[10px] text-slate-400 mt-0.5">Leave 0 if customer prepaid</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Required Vehicle</label>
                        <select name="vehicle_type" class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                            <option value="motorcycle">Motorcycle / Scooter</option>
                            <option value="bicycle">Bicycle (Ultra Local)</option>
                            <option value="car">Car / Van (Bulk Parcel)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <div class="text-xs text-slate-500 flex items-center gap-1.5">
                    <i class="fas fa-shield-alt text-teal-600"></i>
                    <span>Secure Handover: You will receive a 6-digit Pickup OTP upon booking.</span>
                </div>
                <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center gap-2">
                    <i class="fas fa-bolt"></i> Dispatch Direct Rider
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
