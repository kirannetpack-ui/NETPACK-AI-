@extends('layouts.agency')

@section('title', 'File Flight Arrival Notice | Manifest ' . $manifest->manifest_number)

@section('content')
<div class="max-w-6xl mx-auto px-4 space-y-6">
    <!-- Header Card -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl shadow-xl p-6 text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('agency.manifests.index') }}" class="text-xs font-semibold text-indigo-300 hover:text-white flex items-center gap-1 mb-1">
                <i class="fas fa-arrow-left"></i> Back to Inbound Manifests
            </a>
            <h1 class="text-2xl font-black tracking-tight flex items-center gap-2">
                <i class="fas fa-plane-arrival text-emerald-400"></i>
                Flight Arrival Notice: {{ $manifest->manifest_number }}
            </h1>
            <p class="text-slate-300 text-xs mt-1">
                MAWB: <span class="font-mono font-bold text-sky-300">{{ $manifest->mawb_number ?? 'N/A' }}</span> &bull; 
                Airline: <span class="font-bold text-white">{{ $manifest->mawb->airline_name ?? 'Airline Carrier' }}</span> &bull; 
                Flight: <span class="font-mono font-bold text-white">{{ $manifest->flight_number ?? 'TBD' }}</span>
            </p>
        </div>
        <div class="text-right">
            <span class="px-3 py-1 rounded-xl text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                Inbound Facility: {{ $agency->name ?? 'Hub Terminal' }}
            </span>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-500 p-4 rounded-xl text-sm space-y-1">
            <p class="font-bold flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> Validation errors:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('agency.manifests.process-arrival-notice', $manifest->id) }}" method="POST" id="arrivalNoticeForm" class="space-y-6">
        @csrf

        <!-- Telemetry Capture Block (Date, Time, Location, Staff) -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fas fa-satellite-dish text-indigo-500"></i>
                    Flight Touchdown Telemetry & Arrival Stamp
                </h3>
                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                    <i class="fas fa-check-circle"></i> Auto-Capturing Date, Time & Location
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                        Arrival Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="arrival_date" id="arrival_date" value="{{ old('arrival_date', date('Y-m-d')) }}" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                        Arrival Time <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="arrival_time" id="arrival_time" value="{{ old('arrival_time', date('H:i')) }}" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                        Arrival Location / Hub Facility <span class="text-rose-500">*</span>
                    </label>
                    @php
                        $defaultLoc = ($agency->city ?? $manifest->hub->city ?? 'Gateway Hub') . ' Cargo Facility (' . ($manifest->hub->code ?? 'HUB') . ')';
                    @endphp
                    <input type="text" name="arrival_location" id="arrival_location" value="{{ old('arrival_location', $defaultLoc) }}" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- Mode Selection: Whole Manifest vs Partial Arrival -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                <i class="fas fa-tasks text-emerald-500"></i> Select Arrival Notice Mode
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Whole Manifest Selection -->
                <label class="relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition" id="cardWhole">
                    <input type="radio" name="arrival_mode" value="whole" id="modeWhole" checked onchange="handleModeChange()" class="w-4 h-4 text-emerald-600 mt-1 border-slate-300">
                    <div class="ml-3">
                        <span class="block text-sm font-bold text-slate-900 dark:text-white">Whole Manifest Arrival</span>
                        <span class="block text-xs text-slate-500 mt-0.5">
                            All {{ $manifest->shipments->count() }} manifested packets have arrived completely and intact with zero discrepancies.
                        </span>
                    </div>
                </label>

                <!-- Partial Arrival Selection -->
                <label class="relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition" id="cardPartial">
                    <input type="radio" name="arrival_mode" value="partial" id="modePartial" onchange="handleModeChange()" class="w-4 h-4 text-amber-600 mt-1 border-slate-300">
                    <div class="ml-3">
                        <span class="block text-sm font-bold text-slate-900 dark:text-white">Partial Arrival (Packet Discrepancies)</span>
                        <span class="block text-xs text-slate-500 mt-0.5">
                            Select individually arrived packets. Provide mandatory remarks for non-arrival / short-landed packets.
                        </span>
                    </div>
                </label>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">General Inbound Remarks (Optional)</label>
                <textarea name="general_remarks" rows="2" placeholder="Customs seal intact, offloaded from flight on schedule..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">{{ old('general_remarks') }}</textarea>
            </div>
        </div>

        <!-- Manifest Packets Checklist -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden space-y-3">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-boxes text-indigo-500"></i> Manifest Packets ({{ $manifest->shipments->count() }})
                    </h3>
                    <p class="text-xs text-slate-500">
                        Check packet arrival status. Unchecking a packet prompts for non-arrival remarks.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                        Arrived: <span id="lblArrivedCount" class="font-bold">{{ $manifest->shipments->count() }}</span>
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300">
                        Non-Arrival: <span id="lblNonArrivedCount" class="font-bold">0</span>
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3 w-12 text-center">Arrived?</th>
                            <th class="px-5 py-3">Tracking / HAWB</th>
                            <th class="px-5 py-3">Consignee & Destination</th>
                            <th class="px-5 py-3">Weight (kg)</th>
                            <th class="px-5 py-3">Non-Arrival Remarks (If Packet Missing / Damaged)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @foreach($manifest->shipments as $item)
                            @php $s = $item->shipment; @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition" id="row_{{ $s->id }}">
                                <td class="px-5 py-3 text-center">
                                    <input type="checkbox" name="arrived_shipment_ids[]" value="{{ $s->id }}" checked onchange="handlePacketCheck('{{ $s->id }}')" class="packet-checkbox w-4 h-4 text-emerald-600 rounded">
                                </td>
                                <td class="px-5 py-3 font-mono">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $s->tracking_number }}</span>
                                    @if($s->hawb_number)
                                        <div class="text-[10px] text-indigo-600 dark:text-indigo-400">HAWB: {{ $s->hawb_number }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $s->receiver_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $s->receiver_city ?? '' }}, {{ $s->receiver_country ?? '' }}</div>
                                </td>
                                <td class="px-5 py-3 font-mono font-bold">
                                    {{ number_format($item->weight ?? $s->actual_weight ?? 1.0, 2) }} kg
                                </td>
                                <td class="px-5 py-3">
                                    <div id="remarksWrapper_{{ $s->id }}" class="hidden">
                                        <input type="text" name="non_arrival_remarks[{{ $s->id }}]" id="remarks_{{ $s->id }}" placeholder="e.g. Short-landed, damaged box, offloaded in transit" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-rose-300 dark:border-rose-700 bg-rose-50/40 dark:bg-rose-950/20 text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500">
                                    </div>
                                    <span id="okBadge_{{ $s->id }}" class="inline-flex items-center text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                        <i class="fas fa-check-circle mr-1"></i> Arrived
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submission Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('agency.manifests.index') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-bold shadow-xl shadow-emerald-600/30 transition transform hover:-translate-y-0.5">
                <i class="fas fa-check-double"></i> Confirm & File Arrival Notice
            </button>
        </div>
    </form>
</div>

<script>
function handleModeChange() {
    const isWhole = document.getElementById('modeWhole').checked;
    const cardWhole = document.getElementById('cardWhole');
    const cardPartial = document.getElementById('cardPartial');

    if (isWhole) {
        cardWhole.classList.add('border-emerald-500', 'bg-emerald-50/30');
        cardWhole.classList.remove('border-slate-200');
        cardPartial.classList.remove('border-amber-500', 'bg-amber-50/30');
        cardPartial.classList.add('border-slate-200');

        // Check all packets and hide remarks
        document.querySelectorAll('.packet-checkbox').forEach(cb => {
            cb.checked = true;
            const sid = cb.value;
            document.getElementById('remarksWrapper_' + sid).classList.add('hidden');
            document.getElementById('okBadge_' + sid).classList.remove('hidden');
            document.getElementById('row_' + sid).classList.remove('bg-rose-50/50');
        });
    } else {
        cardPartial.classList.add('border-amber-500', 'bg-amber-50/30');
        cardPartial.classList.remove('border-slate-200');
        cardWhole.classList.remove('border-emerald-500', 'bg-emerald-50/30');
        cardWhole.classList.add('border-slate-200');
    }
    updateCounters();
}

function handlePacketCheck(sid) {
    const cb = document.querySelector(`.packet-checkbox[value="${sid}"]`);
    const remarksWrapper = document.getElementById('remarksWrapper_' + sid);
    const okBadge = document.getElementById('okBadge_' + sid);
    const row = document.getElementById('row_' + sid);

    if (cb.checked) {
        remarksWrapper.classList.add('hidden');
        okBadge.classList.remove('hidden');
        row.classList.remove('bg-rose-50/50');
    } else {
        remarksWrapper.classList.remove('hidden');
        okBadge.classList.add('hidden');
        row.classList.add('bg-rose-50/50');
        document.getElementById('remarks_' + sid).focus();
        // Switch mode to partial if unchecked
        document.getElementById('modePartial').checked = true;
        handleModeChange();
    }
    updateCounters();
}

function updateCounters() {
    const total = document.querySelectorAll('.packet-checkbox').length;
    const arrived = document.querySelectorAll('.packet-checkbox:checked').length;
    const nonArrived = total - arrived;

    document.getElementById('lblArrivedCount').innerText = arrived;
    document.getElementById('lblNonArrivedCount').innerText = nonArrived;
}

document.addEventListener('DOMContentLoaded', function() {
    handleModeChange();
});
</script>
@endsection
