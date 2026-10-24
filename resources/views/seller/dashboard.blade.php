@extends('layouts.seller')

@section('title', 'Merchant Operations Dashboard')
@section('page-title', 'Merchant Operations Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Welcome & Fast Dispatch Header -->
    <div class="np-dashboard-hero relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-950 via-slate-900 to-teal-950 p-6 sm:p-8 text-white border border-emerald-500/20 shadow-xl">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-widest">
                        <i class="fas fa-shop text-[9px] mr-1"></i> Merchant workspace
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        <i class="fas fa-circle-check text-[9px] mr-1 text-emerald-400"></i> Verified Seller
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    Welcome back, {{ Auth::user()->business_name ?? Auth::user()->name }}!
                </h1>
                <p class="text-sm text-slate-300 max-w-xl">
                    Dispatch customer parcels across local riders, domestic Nepal provinces, and international air cargo with recorded shipment updates.
                </p>
            </div>

            <!-- Fast Action Booking CTA Buttons -->
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <a href="{{ route('seller.orders.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold text-xs shadow-lg shadow-emerald-900/40 transition transform hover:-translate-y-0.5">
                    <i class="fas fa-plus-circle text-sm"></i>
                    <span>Book Shipment</span>
                </a>
                <a href="{{ route('rates.inquiry') }}" target="_blank"
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-amber-300 font-semibold text-xs border border-slate-700 transition">
                    <i class="fas fa-calculator text-sm"></i>
                    <span>Rate Calculator</span>
                </a>
                <a href="{{ route('tracking.page') }}" target="_blank"
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 font-semibold text-xs border border-slate-700 transition">
                    <i class="fas fa-satellite-dish text-sm text-emerald-400"></i>
                    <span>Shipment tracking</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Core KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total E-Commerce Orders -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">E-Commerce Orders</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($orderStats['total'] ?? 0) }}</p>
                    <div class="flex items-center gap-2 mt-2 text-[11px]">
                        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-bold border border-amber-200">
                            {{ $orderStats['pending'] ?? 0 }} Pending
                        </span>
                        <span class="text-slate-400">
                            {{ $orderStats['completed'] ?? 0 }} Completed
                        </span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-shopping-bag"></i>
                </div>
            </div>
        </div>

        <!-- 2. Active Shipments Radar -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Courier & Cargo</p>
                    <p class="text-2xl sm:text-3xl font-black text-indigo-600 mt-1">{{ number_format($shipmentStats['total'] ?? 0) }}</p>
                    <div class="flex items-center gap-2 mt-2 text-[11px]">
                        <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-bold border border-indigo-200">
                            {{ $shipmentStats['active'] ?? 0 }} In Transit
                        </span>
                        <span class="text-slate-400">
                            {{ $shipmentStats['delivered'] ?? 0 }} Delivered
                        </span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-truck-fast"></i>
                </div>
            </div>
        </div>

        <!-- 3. Total Revenue / Sales -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Sales / Revenue</p>
                    <p class="text-2xl sm:text-3xl font-black text-emerald-600 mt-1">Rs. {{ number_format($earnings['total']['amount'] ?? 0, 2) }}</p>
                    <p class="text-[11px] text-slate-500 mt-2 font-medium">
                        <i class="fas fa-calendar-week text-emerald-500 mr-1"></i> Rs. {{ number_format($weekEarnings ?? 0, 0) }} this week
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-money-bill-trend-up"></i>
                </div>
            </div>
        </div>

        <!-- 4. Available Wallet Balance -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Available Wallet</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Rs. {{ number_format($balance ?? 0, 2) }}</p>
                    <div class="flex items-center gap-2 mt-2 text-[11px]">
                        <a href="{{ route('seller.withdraw') }}" class="font-bold text-teal-600 hover:text-teal-700 hover:underline">
                            Request Payout &rarr;
                        </a>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 3-TIER GLOBAL SHIPMENT DISPATCH CHANNELS -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fas fa-paper-plane text-emerald-600"></i>
                    <span>Global Shipment Booking Channels</span>
                </h3>
                <p class="text-xs text-slate-500">Select delivery mode or calculate live rates before booking</p>
            </div>
            <a href="{{ route('rates.inquiry') }}" target="_blank" class="text-xs font-bold text-emerald-600 hover:underline flex items-center gap-1">
                <i class="fas fa-calculator"></i>
                <span>Check All Tariff Rates &rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Tier 1: Rider Delivery (Local City E-Commerce) -->
            <a href="{{ route('shipments.create', ['shipment_type' => 'ecommerce']) }}" 
               class="p-5 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-emerald-50/30 hover:border-emerald-500 transition group block">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-lg group-hover:scale-110 transition">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800">
                        Intra-City
                    </span>
                </div>
                <h4 class="font-bold text-slate-900 text-sm group-hover:text-emerald-700 transition">Rider Delivery</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Same-day city motorbike delivery with live GPS telemetry and Cash on Delivery (COD) collection.
                </p>
                <div class="mt-3 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs font-bold text-emerald-600">
                    <span>Book Rider Dispatch</span>
                    <i class="fas fa-arrow-right text-[11px] group-hover:translate-x-1 transition"></i>
                </div>
            </a>

            <!-- Tier 2: Domestic Courier (Nepal Inter-District) -->
            <a href="{{ route('shipments.create', ['shipment_type' => 'domestic']) }}" 
               class="p-5 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-teal-50/30 hover:border-teal-500 transition group block">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center text-lg group-hover:scale-110 transition">
                        <i class="fas fa-truck-fast"></i>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-teal-100 text-teal-800">
                        7 Provinces
                    </span>
                </div>
                <h4 class="font-bold text-slate-900 text-sm group-hover:text-teal-700 transition">Domestic Courier</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Inter-district road & air courier across Nepal's 77 districts with branch depot drop and doorstep delivery.
                </p>
                <div class="mt-3 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs font-bold text-teal-600">
                    <span>Book Domestic Courier</span>
                    <i class="fas fa-arrow-right text-[11px] group-hover:translate-x-1 transition"></i>
                </div>
            </a>

            <!-- Tier 3: International Air Cargo (Global Export) -->
            <a href="{{ route('shipments.create', ['shipment_type' => 'international']) }}" 
               class="p-5 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-sky-50/30 hover:border-sky-500 transition group block">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center text-lg group-hover:scale-110 transition">
                        <i class="fas fa-plane-departure"></i>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-sky-100 text-sky-800">
                        Worldwide
                    </span>
                </div>
                <h4 class="font-bold text-slate-900 text-sm group-hover:text-sky-700 transition">International Air Cargo</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Global air freight to Dubai, UK, Australia, USA & beyond with export customs clearance and HAWBs.
                </p>
                <div class="mt-3 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs font-bold text-sky-600">
                    <span>Quote & Book Air Cargo</span>
                    <i class="fas fa-arrow-right text-[11px] group-hover:translate-x-1 transition"></i>
                </div>
            </a>
        </div>
    </div>

    <!-- Live Order & Shipments Stream -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Orders & Consignments -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Recent Dispatches & Orders</h3>
                    <p class="text-xs text-slate-500">Live order fulfillment with riders, regional depots, and cargo hubs</p>
                </div>
                <a href="{{ route('seller.orders') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                    View Registry &rarr;
                </a>
            </div>

            <div class="p-4">
                @php
                    $sellerRecentOrders = \App\Models\Order::where('seller_id', Auth::id())
                        ->with(['rider', 'client'])
                        ->latest()
                        ->take(6)
                        ->get();
                @endphp

                @if($sellerRecentOrders->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach($sellerRecentOrders as $order)
                            <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/80 rounded-xl px-3 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center font-bold text-xs text-slate-700">
                                        <i class="fas fa-cube text-slate-500"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-xs text-slate-900">#{{ $order->order_number }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                                {{ $order->status === 'delivered' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                                   ($order->status === 'out_for_delivery' || $order->status === 'in_transit' ? 'bg-blue-50 text-blue-700 border border-blue-200' :
                                                   ($order->status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-700')) }}">
                                                {{ str_replace('_', ' ', $order->status) }}
                                            </span>
                                            @if($order->payment_method === 'cod')
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800">COD</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            <i class="fas fa-user text-[10px] mr-1 text-slate-400"></i> {{ $order->customer_name ?? $order->client?->name ?? 'Customer' }}
                                            @if($order->customer_phone)
                                                • {{ $order->customer_phone }}
                                            @endif
                                            @if($order->shipping_address)
                                                • <span class="text-slate-400">{{ Str::limit($order->shipping_address, 25) }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 sm:text-right">
                                    <div>
                                        <p class="font-bold font-mono text-sm text-slate-900">Rs. {{ number_format($order->total_amount, 2) }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $order->created_at->diffForHumans() }}</p>
                                    </div>
                                    <a href="{{ route('seller.orders.show', $order->id) }}" 
                                       class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 text-xs font-bold transition">
                                        Details
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10">
                        <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400 mb-3 text-xl">
                            <i class="fas fa-cart-arrow-down"></i>
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm">No Dispatches Yet</h4>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-4">Start by booking your first customer dispatch via rider, domestic courier, or international air cargo.</p>
                        <a href="{{ route('seller.orders.create') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition">
                            Book First Shipment
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Side: Wallet & COD Payout Settlement Info -->
        <div class="space-y-6">
            <!-- Digital Settlement Card -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-950 rounded-2xl p-6 text-white border border-slate-800 shadow-md space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-widest">Merchant Wallet & COD</span>
                    <i class="fas fa-shield-halved text-slate-500"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Withdrawable Balance</p>
                    <p class="text-2xl font-black font-mono text-white mt-0.5">Rs. {{ number_format($balance ?? 0, 2) }}</p>
                </div>
                <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Pending COD Clearance</span>
                    <span class="font-mono font-bold text-amber-400">Rs. {{ number_format($pendingBalance ?? 0, 2) }}</span>
                </div>
                <a href="{{ route('seller.withdraw') }}" class="block w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-center font-bold text-xs transition shadow-md shadow-emerald-950">
                    Request Fast Payout
                </a>
            </div>

            <!-- COD Remittance Account Details Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-slate-900 text-sm">COD Settlement Account</h4>
                    <a href="{{ route('seller.settings') }}" class="text-xs text-emerald-600 font-bold hover:underline">Edit &rarr;</a>
                </div>
                @php
                    $sellerUser = Auth::user();
                @endphp
                @if($sellerUser->bank_name || $sellerUser->account_number)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1">
                        <p class="font-bold text-slate-800">{{ $sellerUser->bank_name ?? 'Bank Account' }}</p>
                        <p class="font-mono text-slate-600">A/C: {{ $sellerUser->account_number ?? '••••••••' }}</p>
                        <p class="text-[11px] text-slate-500">Holder: {{ $sellerUser->account_holder_name ?? $sellerUser->name }}</p>
                    </div>
                @else
                    <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl text-xs text-amber-800 space-y-1">
                        <p class="font-bold"><i class="fas fa-triangle-exclamation mr-1"></i> No Bank / QR Linked</p>
                        <p class="text-[11px] text-amber-700">Add your bank account, Banking QR, eSewa or Khalti details to receive automatic COD settlements.</p>
                        <a href="{{ route('seller.settings') }}" class="inline-block mt-1 font-bold text-amber-900 underline text-[11px]">
                            Set Payout Account &rarr;
                        </a>
                    </div>
                @endif
                <div class="text-[11px] text-slate-400">
                    <i class="fas fa-lock text-[10px] text-emerald-600 mr-1"></i>
                    <span>All COD collections settled directly to your verified account.</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection