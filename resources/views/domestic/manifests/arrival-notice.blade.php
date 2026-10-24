@extends('layouts.app')

@section('title', 'Domestic Hub Inbound Arrival Notice - ' . $manifest->manifest_number)
@section('page-title', '🏢 Domestic Inbound Arrival Notice')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
    <!-- Header Card -->
    <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 rounded-2xl shadow-xl p-6 text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('domestic.manifests.show', $manifest->id) }}" class="text-xs font-semibold text-teal-300 hover:text-white flex items-center gap-1 mb-2">
                <i class="fas fa-arrow-left"></i> Back to Manifest Details
            </a>
            <h1 class="text-2xl font-black tracking-tight flex items-center gap-2">
                <i class="fas fa-truck-ramp-box text-emerald-400"></i>
                Domestic Inbound Arrival Notice: {{ $manifest->manifest_number }}
            </h1>
            <p class="text-slate-300 text-xs mt-1">
                Route: <span class="font-bold text-white">{{ $manifest->origin_city ?? 'Kathmandu Central' }}</span> ➔ 
                <span class="font-bold text-emerald-300">{{ $manifest->destination_city ?? 'Regional Hub' }}</span> &bull; 
                Assigned Partner: <span class="font-bold text-sky-300">{{ $manifest->partner->name ?? 'Domestic Partner' }}</span> &bull; 
                Total Load: <span class="font-mono font-bold text-white">{{ $manifest->total_shipments }} Consignments ({{ number_format($manifest->total_weight, 2) }} kg)</span>
            </p>
        </div>
        <div class="flex flex-col items-end gap-2">
            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                <i class="fas fa-map-marker-alt mr-1"></i> Nepal Wide Hub Network
            </span>
            <span class="text-xs text-slate-400">Manifest Status: <strong class="text-amber-300">{{ ucfirst($manifest->status) }}</strong></span>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-sm space-y-1">
            <p class="font-bold flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> Please correct the errors below:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('domestic.manifests.process-arrival-notice', $manifest->id) }}" method="POST" id="arrivalNoticeForm" class="space-y-6">
        @csrf

        <!-- Telemetry & Hub Facility Verification Block -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-2">
                    <i class="fas fa-satellite-dish text-teal-600"></i>
                    Hub Inbound Touchdown Telemetry & Arrival Verification
                </h3>
                <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                    <i class="fas fa-check-circle"></i> Nepal Provincial Hub Stamp
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">
                        Arrival Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="arrival_date" id="arrival_date" value="{{ old('arrival_date', date('Y-m-d')) }}" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">
                        Arrival Time <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="arrival_time" id="arrival_time" value="{{ old('arrival_time', date('H:i')) }}" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">
                        Receiving Hub / Facility <span class="text-rose-500">*</span>
                    </label>
                    <input list="nepalHubList" name="arrival_location" id="arrival_location" value="{{ old('arrival_location', $defaultLocation) }}" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="Select or type facility">
                    <datalist id="nepalHubList">
                        @foreach($nepalHubs as $hub)
                            <option value="{{ $hub }}"></option>
                        @endforeach
                    </datalist>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">
                        Operator / Receiving Staff <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="operator_name" id="operator_name" value="{{ old('operator_name', $operatorName) }}" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>
            </div>
        </div>

        <!-- Mode Selection: Whole vs Partial -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-2 pb-3 border-b border-slate-100">
                <i class="fas fa-clipboard-check text-emerald-600"></i> Inbound Verification Mode
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Whole Manifest -->
                <label class="relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition border-teal-500 bg-teal-50/50" id="cardWhole">
                    <input type="radio" name="arrival_mode" value="whole" id="modeWhole" checked onchange="toggleArrivalMode()" class="w-4 h-4 text-teal-600 mt-1 border-slate-300 focus:ring-teal-500">
                    <div class="ml-3">
                        <span class="block text-sm font-bold text-slate-900">Whole Manifest Arrival</span>
                        <span class="block text-xs text-slate-500 mt-0.5">
                            All {{ $manifest->shipments->count() }} consignments arrived completely and intact with zero damage or missing packets.
                        </span>
                    </div>
                </label>

                <!-- Partial Manifest -->
                <label class="relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition border-slate-200 hover:border-slate-300" id="cardPartial">
                    <input type="radio" name="arrival_mode" value="partial" id="modePartial" onchange="toggleArrivalMode()" class="w-4 h-4 text-teal-600 mt-1 border-slate-300 focus:ring-teal-500">
                    <div class="ml-3">
                        <span class="block text-sm font-bold text-slate-900">Partial Manifest Verification</span>
                        <span class="block text-xs text-slate-500 mt-0.5">
                            Select specifically which packets arrived. Record non-arrival exception remarks for any missing or damaged loads.
                        </span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Consignment Verification Checklist -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" id="shipmentsChecklistContainer">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-boxes-packing text-teal-600"></i>
                        Consignment Line Verification ({{ $manifest->shipments->count() }} Total Packages)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Uncheck missing items and record reasons to capture exceptions.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-xs font-semibold text-slate-600">
                        <span id="counterArrived" class="text-emerald-700 font-bold">{{ $manifest->shipments->count() }}</span> Arrived &bull; 
                        <span id="counterMissing" class="text-rose-600 font-bold">0</span> Non-Arrival Exceptions
                    </div>
                    <button type="button" onclick="toggleSelectAll(true)" class="text-xs px-2.5 py-1 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 font-semibold text-slate-700">All</button>
                    <button type="button" onclick="toggleSelectAll(false)" class="text-xs px-2.5 py-1 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 font-semibold text-slate-700">None</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">Arrived?</th>
                            <th class="py-3 px-4">Tracking / Waybill</th>
                            <th class="py-3 px-4">Consignee (Receiver)</th>
                            <th class="py-3 px-4">Destination</th>
                            <th class="py-3 px-4">Weight</th>
                            <th class="py-3 px-4">Bag #</th>
                            <th class="py-3 px-4 min-w-[220px]">Non-Arrival / Exception Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($manifest->shipments as $manifestShipment)
                            @php
                                $s = $manifestShipment->shipment;
                                $isArrivedDefault = true;
                            @endphp
                            <tr class="hover:bg-slate-50/75 transition" id="row_{{ $manifestShipment->id }}">
                                <td class="py-3 px-4 text-center">
                                    <input type="checkbox" 
                                           name="arrived_shipment_ids[]" 
                                           value="{{ $manifestShipment->id }}" 
                                           id="chk_{{ $manifestShipment->id }}" 
                                           checked 
                                           onchange="handleShipmentCheck('{{ $manifestShipment->id }}')" 
                                           class="shipment-check w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                    {{ $s->tracking_number ?? 'N/A' }}
                                    @if(!empty($s->hawb_number))
                                        <span class="block text-[10px] text-teal-700 font-normal">HAWB: {{ $s->hawb_number }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $s->receiver_name ?? 'N/A' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $s->receiver_phone ?? '' }}</div>
                                </td>
                                <td class="py-3 px-4 text-slate-700 font-medium">
                                    {{ $s->receiver_city ?? $manifest->destination_city }}
                                    @if($s->receiver_zone)
                                        <span class="block text-[10px] text-slate-400">{{ $s->receiver_zone }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-700">
                                    {{ number_format($s->actual_weight ?? $s->weight ?? 1.0, 2) }} kg
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600">
                                    {{ $manifestShipment->bag->bag_number ?? 'Bag-01' }}
                                </td>
                                <td class="py-3 px-4">
                                    <input type="text" 
                                           name="non_arrival_remarks[{{ $manifestShipment->id }}]" 
                                           id="remark_{{ $manifestShipment->id }}" 
                                           placeholder="State reason if not arrived (e.g. short-landed, damaged)" 
                                           disabled 
                                           class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 bg-slate-100 text-slate-500 focus:bg-white focus:border-rose-400 focus:ring-2 focus:ring-rose-200">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="text-xs text-slate-500">
                <i class="fas fa-shield-halved text-teal-600 mr-1"></i>
                Confirming arrival immediately updates shipment status, records timestamped event logs, and notifies dispatch dispatchers.
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('domestic.manifests.show', $manifest->id) }}" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-100 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md transition flex items-center gap-2">
                    <i class="fas fa-check-double"></i> Confirm & File Inbound Arrival Notice
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function toggleArrivalMode() {
    const isWhole = document.getElementById('modeWhole').checked;
    const cardWhole = document.getElementById('cardWhole');
    const cardPartial = document.getElementById('cardPartial');

    if (isWhole) {
        cardWhole.className = 'relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition border-teal-500 bg-teal-50/50';
        cardPartial.className = 'relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition border-slate-200 hover:border-slate-300';
        toggleSelectAll(true);
    } else {
        cardWhole.className = 'relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition border-slate-200 hover:border-slate-300';
        cardPartial.className = 'relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition border-teal-500 bg-teal-50/50';
    }
}

function handleShipmentCheck(id) {
    const chk = document.getElementById('chk_' + id);
    const remark = document.getElementById('remark_' + id);
    const row = document.getElementById('row_' + id);

    if (chk.checked) {
        remark.disabled = true;
        remark.value = '';
        remark.classList.add('bg-slate-100', 'text-slate-500');
        remark.classList.remove('bg-white', 'text-slate-900', 'border-rose-400');
        row.classList.remove('bg-rose-50/50');
    } else {
        remark.disabled = false;
        remark.classList.remove('bg-slate-100', 'text-slate-500');
        remark.classList.add('bg-white', 'text-slate-900', 'border-rose-400');
        row.classList.add('bg-rose-50/50');
        remark.focus();
        if (!remark.value) {
            remark.value = 'Package not received at hub arrival';
        }
        document.getElementById('modePartial').checked = true;
        toggleArrivalMode();
    }
    updateCounters();
}

function toggleSelectAll(state) {
    const checkboxes = document.querySelectorAll('.shipment-check');
    checkboxes.forEach(cb => {
        cb.checked = state;
        const id = cb.value;
        const remark = document.getElementById('remark_' + id);
        const row = document.getElementById('row_' + id);
        if (state) {
            remark.disabled = true;
            remark.value = '';
            remark.classList.add('bg-slate-100', 'text-slate-500');
            remark.classList.remove('bg-white', 'text-slate-900', 'border-rose-400');
            row.classList.remove('bg-rose-50/50');
        } else {
            remark.disabled = false;
            remark.classList.remove('bg-slate-100', 'text-slate-500');
            remark.classList.add('bg-white', 'text-slate-900', 'border-rose-400');
            row.classList.add('bg-rose-50/50');
            if (!remark.value) remark.value = 'Package not received at hub arrival';
        }
    });
    updateCounters();
}

function updateCounters() {
    const checkboxes = document.querySelectorAll('.shipment-check');
    let arrived = 0;
    let missing = 0;
    checkboxes.forEach(cb => {
        if (cb.checked) arrived++;
        else missing++;
    });
    document.getElementById('counterArrived').innerText = arrived;
    document.getElementById('counterMissing').innerText = missing;
}
</script>
@endsection
