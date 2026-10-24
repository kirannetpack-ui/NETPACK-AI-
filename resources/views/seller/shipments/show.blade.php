@extends('layouts.seller')

@section('title', "Consignment {$shipment->tracking_number}")
@section('page-title', "Consignment #{$shipment->tracking_number}")

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-100 text-teal-800 border border-teal-200">
                    {{ strtoupper($shipment->shipment_type ?? 'Express') }}
                </span>
                @if(!empty($shipment->hawb_number))
                    <span class="font-mono text-xs text-teal-700 font-bold bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                        HAWB: {{ $shipment->hawb_number }}
                    </span>
                @endif
            </div>
            <h1 class="text-2xl font-black text-slate-900 font-mono">{{ $shipment->formatted_tracking_number }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Created on {{ $shipment->created_at?->format('M d, Y, h:i A') }}</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('tracking.hawb.print', $shipment->tracking_number) }}" target="_blank" class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-black text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <i class="fas fa-print"></i>
                <span>Print {{ ($shipment->shipment_type === 'domestic' || $shipment->shipment_type === 'ecommerce') ? 'Waybill' : 'HAWB' }}</span>
            </a>
            <a href="{{ route('tracking.search', ['tracking' => $shipment->tracking_number]) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition flex items-center gap-1.5">
                <i class="fas fa-satellite-dish"></i>
                <span>Live Tracking</span>
            </a>
            <a href="{{ route('seller.shipments') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                Back to Registry
            </a>
        </div>
    </div>

    <!-- Status & Telemetry Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Current Status</span>
            <span class="inline-block mt-1 px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $shipment->status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                {{ str_replace('_', ' ', $shipment->status ?? 'in_transit') }}
            </span>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Chargeable Weight</span>
            <p class="text-xl font-black text-slate-900 mt-0.5 font-mono">
                {{ number_format((float)($shipment->chargeable_weight ?: $shipment->actual_weight ?: 1), 2) }} KG
            </p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Service SLA</span>
            <p class="text-sm font-black text-slate-800 mt-1 uppercase font-mono">
                {{ $shipment->service_type ?? 'Standard' }}
            </p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">COD Collectable</span>
            <p class="text-xl font-black text-amber-600 mt-0.5 font-mono">
                {{ !empty($shipment->is_cod) && $shipment->cod_amount > 0 ? 'Rs. ' . number_format((float)$shipment->cod_amount, 2) : 'Prepaid (Rs. 0)' }}
            </p>
        </div>
    </div>

    <!-- Origin & Destination Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Shipper / Origin -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs space-y-3">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fas fa-store text-teal-600"></i> Sender / Origin Counter
            </h3>
            <div class="space-y-1.5 text-xs text-slate-600">
                <p><span class="text-slate-400">Sender Name:</span> <strong class="text-slate-900">{{ $shipment->sender_name }}</strong></p>
                <p><span class="text-slate-400">Phone:</span> <strong class="text-slate-900 font-mono">{{ $shipment->sender_phone }}</strong></p>
                <p><span class="text-slate-400">Origin Station:</span> <strong class="text-slate-900">{{ $shipment->sender_city ?? 'Kathmandu' }}, Nepal</strong></p>
                <p><span class="text-slate-400">Address:</span> <span class="text-slate-800">{{ $shipment->sender_address }}</span></p>
            </div>
        </div>

        <!-- Consignee / Destination -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs space-y-3">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fas fa-location-dot text-emerald-600"></i> Recipient Destination
            </h3>
            <div class="space-y-1.5 text-xs text-slate-600">
                <p><span class="text-slate-400">Recipient Name:</span> <strong class="text-slate-900">{{ $shipment->receiver_name }}</strong></p>
                <p><span class="text-slate-400">Phone:</span> <strong class="text-slate-900 font-mono">{{ $shipment->receiver_phone }}</strong></p>
                <p><span class="text-slate-400">Destination:</span> <strong class="text-slate-900">{{ $shipment->destination_district ?? $shipment->receiver_city ?? 'Destination' }}, {{ $shipment->receiver_country ?? 'Nepal' }}</strong></p>
                <p><span class="text-slate-400">Delivery Address:</span> <span class="text-slate-800">{{ $shipment->receiver_address }}</span></p>
            </div>
        </div>
    </div>
</div>
@endsection
