@extends('layouts.app')

@section('title', 'International Shipment - ' . ($shipment->hawb_number ?? $shipment->tracking_number))

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header / Actions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center h-10 w-10 rounded-lg bg-teal-100 text-teal-700">
                        <i class="fas fa-plane-departure text-lg"></i>
                    </span>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">
                            International Shipment Details
                        </h1>
                        <p class="text-sm text-gray-500">
                            HAWB: <strong class="font-mono text-teal-700">{{ $shipment->hawb_number ?? 'Not assigned' }}</strong>
                            &nbsp;·&nbsp;
                            Tracking: <strong class="font-mono text-gray-800">{{ $shipment->tracking_number }}</strong>
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('hawb.international', $shipment->id) }}" target="_blank"
                   class="inline-flex items-center gap-2 bg-teal-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-teal-700 transition shadow-sm">
                    <i class="fas fa-print"></i> Generate / Print HAWB
                </a>
                <a href="{{ route('tracking.show', $shipment->tracking_number) }}" target="_blank"
                   class="inline-flex items-center gap-2 bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-slate-900 transition shadow-sm">
                    <i class="fas fa-search-location"></i> Public Tracker
                </a>
                <a href="{{ route('international.shipments') }}"
                   class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-200 transition">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3">
            <i class="fas fa-circle-check text-emerald-600 text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Specs Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Current Status</p>
            <p class="mt-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                    {{ $shipment->status === 'delivered' ? 'bg-emerald-100 text-emerald-800' :
                       (in_array($shipment->status, ['in_transit', 'customs_clearance', 'out_for_delivery']) ? 'bg-blue-100 text-blue-800' :
                       ($shipment->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800')) }}">
                    {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                </span>
            </p>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Service</p>
            <p class="mt-2 text-sm font-bold text-gray-900">{{ strtoupper($shipment->service_type ?? 'Express') }}</p>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Chargeable Weight</p>
            <p class="mt-2 text-sm font-bold text-gray-900">{{ number_format($shipment->chargeable_weight ?? $shipment->actual_weight ?? $shipment->weight ?? 0, 2) }} kg</p>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Package Type</p>
            <p class="mt-2 text-sm font-bold text-gray-900">{{ ucfirst($shipment->package_type ?? 'Parcel') }}</p>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Destination</p>
            <p class="mt-2 text-sm font-bold text-gray-900">{{ $shipment->receiver_city }}, {{ $shipment->receiver_country }}</p>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Overseas Partner</p>
            <p class="mt-2 text-sm font-bold text-gray-900">{{ $shipment->overseasPartner->name ?? 'NETPACK Global Hub' }}</p>
        </div>
    </div>

    <!-- Shipper & Consignee Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Shipper / Sender -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-2 mb-4 border-b pb-3">
                <i class="fas fa-paper-plane text-teal-600"></i>
                <h2 class="text-base font-bold text-gray-800 uppercase tracking-wide">Shipper / Sender (Nepal)</h2>
            </div>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-gray-400">Name</dt>
                    <dd class="font-semibold text-gray-900">{{ $shipment->sender_name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Contact Phone</dt>
                    <dd class="font-medium text-gray-800">{{ $shipment->sender_phone ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Address</dt>
                    <dd class="text-gray-700">{{ $shipment->sender_address ?? '' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Origin Facility</dt>
                    <dd class="text-gray-700">{{ $shipment->sender_city ?? 'Kathmandu' }}, {{ $shipment->sender_country ?? 'Nepal' }}</dd>
                </div>
            </dl>
        </div>

        <!-- Consignee / Receiver -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-2 mb-4 border-b pb-3">
                <i class="fas fa-location-dot text-blue-600"></i>
                <h2 class="text-base font-bold text-gray-800 uppercase tracking-wide">Consignee / Destination Receiver</h2>
            </div>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-gray-400">Recipient Name</dt>
                    <dd class="font-semibold text-gray-900">{{ $shipment->receiver_name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Contact Phone</dt>
                    <dd class="font-medium text-gray-800">{{ $shipment->receiver_phone ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Delivery Address</dt>
                    <dd class="text-gray-700">{{ $shipment->receiver_address ?? '' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">City / State / Postal Code / Country</dt>
                    <dd class="font-medium text-gray-800">
                        {{ $shipment->receiver_city }}, {{ $shipment->receiver_state ?? '' }} {{ $shipment->receiver_postal_code ?? '' }}, {{ $shipment->receiver_country }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Admin International Gateway Hub & Carrier Routing Assignment (Post-Booking Definition) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 text-white flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-teal-500/20 border border-teal-500/40 text-teal-300 flex items-center justify-center text-base font-bold">
                    <i class="fas fa-network-wired"></i>
                </span>
                <div>
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <span>International Gateway Routing & Carrier Definition</span>
                        <span class="text-[10px] uppercase font-black tracking-wider px-2 py-0.5 rounded-full bg-teal-500/20 text-teal-300 border border-teal-500/30">
                            Admin Only
                        </span>
                    </h2>
                    <p class="text-xs text-slate-300 mt-0.5">
                        Client booked for: <strong class="text-teal-300 font-semibold">{{ ($shipment->service_type ?? '') === 'express' ? '⚡ Priority Express Service (3–4 Days)' : '🌍 Economy Air Cargo Service (6–8 Days)' }}</strong>
                        &nbsp;·&nbsp; Destination: <strong class="text-white font-semibold">{{ $shipment->receiver_country }}</strong>
                    </p>
                </div>
            </div>

            <!-- Current Routing Summary Badge -->
            <div class="flex items-center gap-2 text-xs">
                @if(($shipment->service_type ?? '') === 'express')
                    <span class="px-3 py-1 rounded-lg bg-white/10 border border-white/15 text-slate-200">
                        Carrier: <strong class="text-teal-300 font-mono">{{ $shipment->last_mile_carrier_name ?? 'Pending Assignment' }}</strong>
                    </span>
                @else
                    <span class="px-3 py-1 rounded-lg bg-white/10 border border-white/15 text-slate-200">
                        Hub: <strong class="text-teal-300 font-mono">{{ $shipment->currentHub->code ?? 'Pending' }}</strong>
                    </span>
                    <span class="px-3 py-1 rounded-lg bg-white/10 border border-white/15 text-slate-200">
                        Customs: <strong class="text-teal-300 font-mono">{{ $shipment->customs_mode ?? 'DDP' }}</strong>
                    </span>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('international.shipments.update-routing', $shipment->id) }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            @if(($shipment->service_type ?? '') === 'express')
                {{-- Express Service Configuration (DHL / UPS / FedEx / SF) --}}
                <div class="p-4 rounded-xl border border-sky-200 bg-sky-50/50 space-y-4">
                    <div class="flex items-center justify-between border-b border-sky-100 pb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-sky-900 flex items-center gap-1.5">
                            <i class="fas fa-plane-departure text-sky-600"></i> Express Carrier & Handover Allocation
                        </span>
                        <span class="text-[11px] text-sky-700">Client chosen service: Priority Express</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Booked Express Carrier *</label>
                            <select name="last_mile_carrier_name" class="w-full border border-sky-300 rounded-lg px-3 py-2 text-sm bg-white font-semibold focus:ring-2 focus:ring-sky-500" required>
                                <option value="DHL Express Worldwide" {{ ($shipment->last_mile_carrier_name ?? '') === 'DHL Express Worldwide' || ($shipment->last_mile_carrier_name ?? '') === 'DHL' ? 'selected' : '' }}>🟡 DHL Express Worldwide</option>
                                <option value="UPS Worldwide Express" {{ ($shipment->last_mile_carrier_name ?? '') === 'UPS Worldwide Express' || ($shipment->last_mile_carrier_name ?? '') === 'UPS' ? 'selected' : '' }}>🟤 UPS Worldwide Express / Saver</option>
                                <option value="FedEx International Priority" {{ ($shipment->last_mile_carrier_name ?? '') === 'FedEx International Priority' || ($shipment->last_mile_carrier_name ?? '') === 'FEDEX' ? 'selected' : '' }}>🟣 FedEx International Priority</option>
                                <option value="SF Express International" {{ ($shipment->last_mile_carrier_name ?? '') === 'SF Express International' || ($shipment->last_mile_carrier_name ?? '') === 'SF' ? 'selected' : '' }}>🔴 SF Express International</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Carrier AWB / External Tracking #</label>
                            <input type="text" name="last_mile_tracking_number" value="{{ $shipment->last_mile_tracking_number }}" placeholder="e.g. 1234567890 (DHL Waybill)"
                                   class="w-full border border-sky-300 rounded-lg px-3 py-2 text-sm bg-white font-mono focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Booking Confirmation Status</label>
                            <select name="status" class="w-full border border-sky-300 rounded-lg px-3 py-2 text-sm bg-white font-bold focus:ring-2 focus:ring-sky-500">
                                <option value="confirmed" {{ $shipment->status === 'confirmed' || $shipment->status === 'pending' ? 'selected' : '' }}>✅ Confirm Booking</option>
                                <option value="processing" {{ $shipment->status === 'processing' ? 'selected' : '' }}>📦 Preparing Shipment</option>
                                <option value="in_transit" {{ $shipment->status === 'in_transit' ? 'selected' : '' }}>✈️ In Transit</option>
                            </select>
                        </div>
                    </div>
                </div>
            @else
                {{-- Economy Air Cargo Configuration (Hub, Agency, Customs, Last-Mile Courier) --}}
                <div class="p-4 rounded-xl border border-indigo-200 bg-indigo-50/30 space-y-4">
                    <div class="flex items-center justify-between border-b border-indigo-100 pb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-900 flex items-center gap-1.5">
                            <i class="fas fa-network-wired text-indigo-600"></i> International Hub, Agency & Last-Mile Allocation
                        </span>
                        <span class="text-[11px] text-indigo-700">Client chosen service: Economy Air Cargo</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Designated International Hub</label>
                            <select name="current_hub_id" id="admin_routing_hub_id" onchange="filterAdminAgencies()" class="w-full border border-indigo-300 rounded-lg px-3 py-2 text-sm bg-white font-semibold focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Direct Flight / No International Hub --</option>
                                @if(!empty($hubs))
                                    @foreach($hubs as $h)
                                        <option value="{{ $h->id }}" data-mode="{{ $h->mode_type }}" data-code="{{ $h->code }}" {{ (int)$shipment->current_hub_id === (int)$h->id ? 'selected' : '' }}>
                                            {{ $h->code }} - {{ $h->name }} ({{ $h->country ?? 'Global' }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <p class="text-[10px] text-gray-400 mt-1">DXB (Middle East/USA/CAN), LHR (UK/EU), SYD (AUS), AKL (NZ)</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Partner Receiving Agency</label>
                            <select name="current_agency_id" id="admin_routing_agency_id" class="w-full border border-indigo-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Central Hub Desk / Auto --</option>
                                @if(!empty($agencies))
                                    @foreach($agencies as $ag)
                                        <option value="{{ $ag->id }}" data-hub="{{ $ag->hub_id }}" {{ (int)$shipment->current_agency_id === (int)$ag->id ? 'selected' : '' }}>
                                            {{ $ag->name }} ({{ $ag->code }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <p class="text-[10px] text-gray-400 mt-1">Inbound destination handling desk</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Customs Clearance Mode</label>
                            <select name="customs_mode" id="admin_routing_customs_mode" class="w-full border border-indigo-300 rounded-lg px-3 py-2 text-sm bg-white font-bold focus:ring-2 focus:ring-indigo-500">
                                <option value="DDP" {{ ($shipment->customs_mode ?? 'DDP') === 'DDP' ? 'selected' : '' }}>DDP (Delivered Duty Paid)</option>
                                <option value="DDU" {{ ($shipment->customs_mode ?? '') === 'DDU' ? 'selected' : '' }}>DDU (Delivered Duty Unpaid)</option>
                            </select>
                            <p class="text-[10px] text-gray-400 mt-1">UK/EU under DDP, USA/Canada under DDU / DDP</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Destination Last-Mile Delivery Company</label>
                            <select name="last_mile_carrier_name" class="w-full border border-indigo-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Select Courier / Local Partner --</option>
                                @if(!empty($carriers))
                                    @foreach($carriers as $car)
                                        <option value="{{ $car->name }}" {{ ($shipment->last_mile_carrier_name ?? '') === $car->name ? 'selected' : '' }}>
                                            {{ $car->name }} ({{ $car->country ?? 'Global' }})
                                        </option>
                                    @endforeach
                                @else
                                    <option value="Canpar Express" {{ ($shipment->last_mile_carrier_name ?? '') === 'Canpar Express' ? 'selected' : '' }}>Canpar Express (Canada Toronto DDP)</option>
                                    <option value="Obibox" {{ ($shipment->last_mile_carrier_name ?? '') === 'Obibox' ? 'selected' : '' }}>Obibox (Canada Courier)</option>
                                    <option value="Royal Mail" {{ ($shipment->last_mile_carrier_name ?? '') === 'Royal Mail' ? 'selected' : '' }}>Royal Mail (United Kingdom DDP)</option>
                                    <option value="Australia Post" {{ ($shipment->last_mile_carrier_name ?? '') === 'Australia Post' ? 'selected' : '' }}>Australia Post (Australia SYD)</option>
                                    <option value="New Zealand Post" {{ ($shipment->last_mile_carrier_name ?? '') === 'New Zealand Post' ? 'selected' : '' }}>New Zealand Post (NZ AKL)</option>
                                    <option value="UPS" {{ ($shipment->last_mile_carrier_name ?? '') === 'UPS' ? 'selected' : '' }}>UPS Worldwide Crossing</option>
                                    <option value="Aramex" {{ ($shipment->last_mile_carrier_name ?? '') === 'Aramex' ? 'selected' : '' }}>Aramex (Gulf & Middle East)</option>
                                @endif
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Last-Mile Tracking / AWB #</label>
                            <input type="text" name="last_mile_tracking_number" value="{{ $shipment->last_mile_tracking_number }}" placeholder="e.g. CAN12345678"
                                   class="w-full border border-indigo-300 rounded-lg px-3 py-2 text-sm bg-white font-mono focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Booking Confirmation Status</label>
                            <select name="status" class="w-full border border-indigo-300 rounded-lg px-3 py-2 text-sm bg-white font-bold focus:ring-2 focus:ring-indigo-500">
                                <option value="confirmed" {{ $shipment->status === 'confirmed' || $shipment->status === 'pending' ? 'selected' : '' }}>✅ Confirm Booking</option>
                                <option value="processing" {{ $shipment->status === 'processing' ? 'selected' : '' }}>📦 Preparing Shipment</option>
                                <option value="in_transit" {{ $shipment->status === 'in_transit' ? 'selected' : '' }}>✈️ In Transit</option>
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end pt-2">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Operational Instructions & Audit Notes</label>
                    <input type="text" name="routing_notes" placeholder="e.g. Direct handover via DXB Hub; customs clear under DDP; Obibox delivery"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <button type="submit" class="w-full bg-slate-900 hover:bg-black text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-md transition flex items-center justify-center gap-2">
                        <i class="fas fa-check-double text-teal-400"></i>
                        <span>Save Routing & Confirm Booking</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
    function filterAdminAgencies() {
        const hubSelect = document.getElementById('admin_routing_hub_id');
        const agencySelect = document.getElementById('admin_routing_agency_id');
        const modeSelect = document.getElementById('admin_routing_customs_mode');
        if (!hubSelect || !agencySelect) return;

        const hubId = hubSelect.value;
        const selectedOpt = hubSelect.options[hubSelect.selectedIndex];
        if (selectedOpt && selectedOpt.dataset.mode && modeSelect) {
            const m = selectedOpt.dataset.mode;
            if (m === 'DDP' || m === 'DDU') {
                modeSelect.value = m;
            }
        }

        for (let i = 0; i < agencySelect.options.length; i++) {
            const opt = agencySelect.options[i];
            if (!opt.value) continue;
            if (!hubId || opt.dataset.hub == hubId) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        }
    }
    </script>

    <!-- Status Update & Operations -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-2 mb-4 border-b pb-3">
            <i class="fas fa-sliders text-teal-600"></i>
            <h2 class="text-base font-bold text-gray-800">Operational Milestone Update</h2>
        </div>
        <form method="POST" action="{{ route('international.shipments.update-status', $shipment->id) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">New Milestone Status *</label>
                    <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                        <option value="pending" {{ $shipment->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ $shipment->status === 'confirmed' ? 'selected' : '' }}>Booking Confirmed</option>
                        <option value="processing" {{ $shipment->status === 'processing' ? 'selected' : '' }}>Preparing Shipment</option>
                        <option value="picked_up" {{ $shipment->status === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                        <option value="in_transit" {{ $shipment->status === 'in_transit' ? 'selected' : '' }}>In Transit (Airlift / Flight)</option>
                        <option value="customs_clearance" {{ $shipment->status === 'customs_clearance' ? 'selected' : '' }}>Customs Clearance</option>
                        <option value="out_for_delivery" {{ $shipment->status === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                        <option value="delivered" {{ $shipment->status === 'delivered' ? 'selected' : '' }}>Delivered Successfully</option>
                        <option value="returned" {{ $shipment->status === 'returned' ? 'selected' : '' }}>Returned to Sender</option>
                        <option value="cancelled" {{ $shipment->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Audit Notes / Hub Location</label>
                    <input type="text" name="notes" placeholder="e.g., Cleared export customs at Tribhuvan International Airport (TIA) Hub"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
            </div>
            <div>
                <button type="submit" class="bg-teal-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-teal-700 transition">
                    <i class="fas fa-check mr-1"></i> Record Milestone Update
                </button>
            </div>
        </form>
    </div>

    <!-- Tracking History -->
    @if(!empty($shipment->tracking_history))
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-history text-teal-600"></i> Audit History & Scan Log
            </h2>
            <div class="space-y-4">
                @foreach(array_reverse($shipment->tracking_history) as $event)
                    <div class="flex items-start gap-4 p-3 rounded-lg bg-gray-50 border border-gray-100 text-sm">
                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-100 text-teal-700 text-xs">
                            <i class="fas fa-barcode"></i>
                        </span>
                        <div class="flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                <span class="font-bold text-gray-900">{{ $event['status_label'] ?? ucfirst(str_replace('_', ' ', $event['status'] ?? 'Updated')) }}</span>
                                @if(!empty($event['time']))
                                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($event['time'])->format('M d, Y · h:i A') }}</span>
                                @endif
                            </div>
                            @if(!empty($event['description']))
                                <p class="text-xs text-gray-600 mt-1">{{ $event['description'] }}</p>
                            @endif
                            @if(!empty($event['location']))
                                <p class="text-xs text-teal-700 font-medium mt-1"><i class="fas fa-location-dot mr-1"></i>{{ $event['location'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
