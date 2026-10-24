@extends('layouts.app')

@section('title', "Tracking Master AWB: {$primaryAssignment->master_awb}")

@section('content')
@php
    $maskedPhone = preg_replace('/(\d{3})\d{4}(\d{3})/', '$1****$2', $primaryAssignment->delivery_phone);
    $allCompleted = $assignments->every(fn($a) => $a->status === 'completed');
    $isFailed = $assignments->contains(fn($a) => $a->status === 'failed');
    $isDirect = $assignments->count() === 1 && $assignments->first()->assignment_type === 'local_direct';
@endphp

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between text-xs">
        <a href="{{ route('tracking.page') }}" class="text-teal-600 hover:text-teal-800 font-bold flex items-center gap-1.5 transition">
            <i class="fas fa-arrow-left"></i> Track Another Consignment
        </a>
        <span class="font-mono text-slate-400">Universal Consignment Registry</span>
    </div>

    <!-- Tracking Header Card -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 text-white border border-teal-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                    {{ $isDirect ? 'Direct Same-Day Express' : 'Multi-Leg Domestic Courier' }}
                </span>
                @if($allCompleted)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-400 text-slate-950">
                        Delivered
                    </span>
                @elseif($isFailed)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500 text-white">
                        Delivery Exception
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-400 text-slate-950 animate-pulse">
                        In Motion
                    </span>
                @endif
            </div>
            <h1 class="text-xl md:text-2xl font-black font-mono tracking-wide text-white">{{ $primaryAssignment->master_awb }}</h1>
            <p class="text-xs text-slate-300 mt-0.5">
                Current Custody: <strong class="text-teal-300">{{ $currentLeg->current_custody }}</strong>
            </p>
        </div>

        <div class="text-right bg-slate-950/60 p-4 rounded-xl border border-teal-700/60 flex-shrink-0">
            <p class="text-[10px] uppercase font-bold text-slate-400">Payment Status</p>
            @if($primaryAssignment->cod_amount > 0)
                <p class="text-lg font-black text-amber-400 font-mono">Rs. {{ number_format($primaryAssignment->cod_amount, 2) }}</p>
                <p class="text-[10px] text-amber-300 font-semibold">Cash On Delivery (Collect at Doorstep)</p>
            @else
                <p class="text-lg font-black text-emerald-400 font-mono">PREPAID</p>
                <p class="text-[10px] text-emerald-300 font-semibold">No Cash Required</p>
            @endif
        </div>
    </div>

    <!-- Milestone Progression Stepper -->
    <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-xs space-y-6">
        <h3 class="text-sm font-bold text-slate-900 border-b pb-2 flex items-center gap-2">
            <i class="fas fa-route text-teal-600"></i> Master Consignment Progression
        </h3>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-center text-xs">
            <!-- Step 1: Booked -->
            <div class="p-3 rounded-xl bg-slate-50 border border-teal-200">
                <div class="w-8 h-8 mx-auto rounded-full bg-teal-600 text-white flex items-center justify-center font-bold mb-1.5 shadow-sm">
                    <i class="fas fa-box"></i>
                </div>
                <p class="font-bold text-slate-900">Order Placed</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $primaryAssignment->created_at->format('M d, H:i') }}</p>
            </div>

            <!-- Step 2: Rider Assigned -->
            @php $isAssigned = !empty($currentLeg->rider_profile_id) || $currentLeg->status !== 'assigned'; @endphp
            <div class="p-3 rounded-xl {{ $isAssigned ? 'bg-slate-50 border border-teal-200' : 'bg-slate-50/50 opacity-50' }}">
                <div class="w-8 h-8 mx-auto rounded-full {{ $isAssigned ? 'bg-teal-600 text-white' : 'bg-slate-300 text-slate-600' }} flex items-center justify-center font-bold mb-1.5 shadow-sm">
                    <i class="fas fa-motorcycle"></i>
                </div>
                <p class="font-bold text-slate-900">Rider Assigned</p>
                <p class="text-[10px] text-slate-400 mt-0.5">
                    {{ $currentLeg->riderProfile ? $currentLeg->riderProfile->full_name : 'Broadcasting' }}
                </p>
            </div>

            <!-- Step 3: Picked Up -->
            @php $isPickedUp = !empty($primaryAssignment->picked_up_at) || $allCompleted; @endphp
            <div class="p-3 rounded-xl {{ $isPickedUp ? 'bg-slate-50 border border-teal-200' : 'bg-slate-50/50 opacity-50' }}">
                <div class="w-8 h-8 mx-auto rounded-full {{ $isPickedUp ? 'bg-teal-600 text-white' : 'bg-slate-300 text-slate-600' }} flex items-center justify-center font-bold mb-1.5 shadow-sm">
                    <i class="fas fa-handshake"></i>
                </div>
                <p class="font-bold text-slate-900">Picked Up</p>
                <p class="text-[10px] text-slate-400 mt-0.5">
                    {{ $primaryAssignment->picked_up_at ? $primaryAssignment->picked_up_at->format('M d, H:i') : 'Pending OTP' }}
                </p>
            </div>

            <!-- Step 4: Out for Delivery / Hub Linehaul -->
            @php $inTransit = in_array($currentLeg->status, ['in_transit', 'arrived_pickup', 'arrived_destination']) || $allCompleted; @endphp
            <div class="p-3 rounded-xl {{ $inTransit ? 'bg-slate-50 border border-teal-200' : 'bg-slate-50/50 opacity-50' }}">
                <div class="w-8 h-8 mx-auto rounded-full {{ $inTransit ? 'bg-teal-600 text-white' : 'bg-slate-300 text-slate-600' }} flex items-center justify-center font-bold mb-1.5 shadow-sm">
                    <i class="fas fa-truck-fast"></i>
                </div>
                <p class="font-bold text-slate-900">{{ $isDirect ? 'Out for Delivery' : 'In Transit Linehaul' }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $inTransit ? 'Active' : 'Queued' }}</p>
            </div>

            <!-- Step 5: Delivered -->
            <div class="p-3 rounded-xl {{ $allCompleted ? 'bg-emerald-50 border border-emerald-300' : 'bg-slate-50/50 opacity-50' }}">
                <div class="w-8 h-8 mx-auto rounded-full {{ $allCompleted ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600' }} flex items-center justify-center font-bold mb-1.5 shadow-sm">
                    <i class="fas fa-check-double"></i>
                </div>
                <p class="font-bold text-slate-900">Delivered</p>
                <p class="text-[10px] text-slate-400 mt-0.5">
                    {{ $allCompleted && $assignments->last()->delivered_at ? $assignments->last()->delivered_at->format('M d, H:i') : 'Estimated Today' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Multi-Leg Journey Breakdown (If Hybrid Delivery) -->
    @if(!$isDirect && $assignments->count() > 1)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 border-b pb-2 flex items-center gap-2">
            <i class="fas fa-network-wired text-teal-600"></i> Hybrid Multi-Leg Network Legs
        </h3>
        
        <div class="space-y-3 text-xs">
            @foreach($assignments as $leg)
            <div class="p-3 rounded-xl border {{ $leg->status === 'completed' ? 'border-emerald-200 bg-emerald-50/40' : ($leg->id === $currentLeg->id ? 'border-teal-400 bg-teal-50/40' : 'border-slate-200 bg-slate-50/60') }} flex items-center justify-between">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-[10px] px-2 py-0.5 rounded bg-slate-200 text-slate-800">
                            Leg {{ $leg->sequence }}: {{ ucwords(str_replace('_', ' ', $leg->assignment_type)) }}
                        </span>
                        <span class="font-bold text-slate-800">
                            {{ $leg->pickup_name }} &rarr; {{ $leg->delivery_name }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500">
                        Provider: 
                        <strong class="text-slate-700">
                            @if($leg->provider_type === 'rider' && $leg->riderProfile)
                                {{ $leg->riderProfile->full_name }} (Verified Independent Rider)
                            @elseif($leg->provider_type === 'domestic_partner' && $leg->partner)
                                {{ $leg->partner->company_name ?? $leg->partner->name }} (Domestic Courier Partner)
                            @else
                                {{ ucfirst($leg->provider_type) }}
                            @endif
                        </strong>
                    </p>
                </div>

                <div class="text-right flex-shrink-0">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $leg->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ strtoupper(str_replace('_', ' ', $leg->status)) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Origin & Destination Details (Customer Privacy Protected) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-2">
            <h4 class="font-bold uppercase tracking-wider text-slate-700 border-b pb-2 flex items-center gap-1.5">
                <i class="fas fa-store text-teal-600"></i> Dispatch Origin (Merchant)
            </h4>
            <p class="text-slate-500">Merchant Store: <strong class="text-slate-900">{{ $primaryAssignment->pickup_name }}</strong></p>
            <p class="text-slate-500">Origin Location: <strong class="text-slate-900">{{ $primaryAssignment->pickup_address }}</strong></p>
            <p class="text-slate-500">Consignment Weight: <strong class="text-slate-900 font-mono">{{ $primaryAssignment->parcel_weight }} KG</strong></p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-2">
            <h4 class="font-bold uppercase tracking-wider text-slate-700 border-b pb-2 flex items-center gap-1.5">
                <i class="fas fa-location-dot text-teal-600"></i> Destination Recipient
            </h4>
            <p class="text-slate-500">Recipient Name: <strong class="text-slate-900">{{ $primaryAssignment->delivery_name }}</strong></p>
            <p class="text-slate-500">Contact Number: <strong class="text-slate-900 font-mono">{{ $maskedPhone }}</strong> (Masked for privacy)</p>
            <p class="text-slate-500">Delivery Address: <strong class="text-slate-900">{{ $primaryAssignment->delivery_address }}</strong></p>
        </div>
    </div>
</div>
@endsection
