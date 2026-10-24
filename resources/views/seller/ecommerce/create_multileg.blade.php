@extends('layouts.seller')

@section('title', 'Book Multi-Leg Domestic Courier - Inter-City Logistics')

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
                <a href="{{ route('shipments.create', ['shipment_type' => 'domestic']) }}" class="px-3 py-1.5 rounded-xl bg-teal-500/20 text-teal-300 hover:bg-teal-500/30 text-xs font-bold transition border border-teal-500/30 flex items-center gap-1.5">
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
            <!-- 1. Rider Delivery -->
            <a href="{{ route('shipments.create', ['shipment_type' => 'ecommerce']) }}" 
               class="p-3.5 rounded-2xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-white font-semibold text-xs flex items-center gap-3 transition border border-slate-700/80">
                <div class="w-8 h-8 rounded-xl bg-slate-700 flex items-center justify-center text-sm text-amber-400">
                    <i class="fas fa-motorcycle"></i>
                </div>
                <div>
                    <p class="leading-tight">1. Rider Delivery</p>
                    <p class="text-[10px] font-normal text-slate-400">Local City E-Commerce</p>
                </div>
            </a>

            <!-- 2. Domestic Courier (Active Multi-Leg here) -->
            <div class="p-3.5 rounded-2xl bg-teal-600 text-white font-bold text-xs flex items-center gap-3 shadow-sm border border-teal-500">
                <div class="w-8 h-8 rounded-xl bg-teal-700/80 flex items-center justify-center text-sm">
                    <i class="fas fa-truck-fast"></i>
                </div>
                <div>
                    <p class="leading-tight">2. Domestic Courier</p>
                    <p class="text-[10px] font-normal text-teal-100">Multi-Leg Partner Network</p>
                </div>
                <span class="ml-auto text-[9px] bg-white/20 px-2 py-0.5 rounded-full uppercase tracking-wider">Current</span>
            </div>

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
            <h1 class="text-xl font-black text-slate-900">Multi-Leg Domestic Dispatch Desk</h1>
            <p class="text-xs text-slate-500 mt-0.5">Nationwide inter-district delivery connecting First-Mile Rider &rarr; Hub &rarr; Domestic Partner Linehaul &rarr; Destination Rider &rarr; Customer.</p>
        </div>
        <a href="{{ route('seller.ecommerce.index') }}" class="px-3 py-1.5 text-xs font-bold bg-white border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-700 transition">
            Back to Registry
        </a>
    </div>

    <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-xs">
        <form method="POST" action="{{ route('seller.ecommerce.multileg.store') }}" class="space-y-6">
            @csrf

            <!-- 1. Destination City & Province -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 border-b pb-2 flex items-center gap-2">
                    <i class="fas fa-map-location-dot text-sky-600"></i> 1. Destination Regional Hub & City
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Destination City *</label>
                        <select name="destination_city" required class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500 font-semibold">
                            <option value="">Select Destination City</option>
                            <option value="Pokhara">Pokhara (Gandaki Province Gateway)</option>
                            <option value="Biratnagar">Biratnagar (Koshi Province Gateway)</option>
                            <option value="Birgunj">Birgunj (Madhesh Province Gateway)</option>
                            <option value="Butwal">Butwal / Bhairahawa (Lumbini Gateway)</option>
                            <option value="Chitwan">Bharatpur / Chitwan</option>
                            <option value="Nepalgunj">Nepalgunj (Banke Gateway)</option>
                            <option value="Dhangadhi">Dhangadhi (Sudurpashchim Gateway)</option>
                            <option value="Surkhet">Birendranagar / Surkhet (Karnali Gateway)</option>
                            <option value="Itahari">Itahari / Dharan</option>
                            <option value="Hetauda">Hetauda</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Domestic Partner Linehaul (Optional)</label>
                        <select name="partner_id" class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500">
                            <option value="">Auto-Assign Fastest Domestic Courier Partner</option>
                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}">{{ $partner->company_name }} ({{ $partner->city ?? 'National' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- 2. Customer Delivery Details -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 border-b pb-2 flex items-center gap-2">
                    <i class="fas fa-user-check text-sky-600"></i> 2. Recipient Customer Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Customer Full Name *</label>
                        <input type="text" name="delivery_name" value="{{ old('delivery_name') }}" required placeholder="e.g. Ramesh Gurung"
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Customer Mobile *</label>
                        <input type="text" name="delivery_phone" value="{{ old('delivery_phone') }}" required placeholder="e.g. 9856000000"
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Delivery Street Address *</label>
                        <input type="text" name="delivery_address" value="{{ old('delivery_address') }}" required placeholder="e.g. Lakeside Ward 6"
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500">
                    </div>
                </div>
            </div>

            <!-- 3. Parcel & COD -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 border-b pb-2 flex items-center gap-2">
                    <i class="fas fa-box text-sky-600"></i> 3. Parcel Weight & COD Collection
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Parcel Gross Weight (KG) *</label>
                        <input type="number" step="0.5" name="parcel_weight" value="{{ old('parcel_weight', 1.0) }}" required
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500 font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">COD Amount to Collect (NPR)</label>
                        <input type="number" step="1" name="cod_amount" value="{{ old('cod_amount', 0) }}"
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500 font-bold text-amber-600">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <div class="text-xs text-slate-500 flex items-center gap-1.5">
                    <i class="fas fa-network-wired text-sky-600"></i>
                    <span>First-Mile rider will pick up from your store and deliver to origin gateway for intercity linehaul.</span>
                </div>
                <button type="submit" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center gap-2">
                    <i class="fas fa-truck-moving"></i> Dispatch Multi-Leg Consignment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
