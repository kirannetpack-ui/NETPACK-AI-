@extends('layouts.app')

@section('title', 'Build Air Cargo Manifest | International Logistics')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('international.manifests.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 mb-1">
                <i class="fas fa-arrow-left"></i> Back to Manifests
            </a>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Build International Flight Manifest</h1>
            <p class="text-sm text-slate-500">
                Consolidate packaged shipments, automatically bind an unused MAWB, and schedule airline dispatch.
            </p>
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

    <form action="{{ route('international.manifests.store') }}" method="POST" id="manifestForm" class="space-y-6">
        @csrf

        <!-- Top Config Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-5">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                <i class="fas fa-route text-indigo-500"></i> Routing, Hub & MAWB Binding
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Gateway Hub -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">
                        Destination Gateway Hub <span class="text-rose-500">*</span>
                    </label>
                    <select name="hub_id" id="hub_id" required class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500" onchange="filterHubAgencies()">
                        <option value="">-- Select Gateway Hub --</option>
                        @foreach($hubs as $hub)
                            <option value="{{ $hub->id }}" {{ old('hub_id', $hubId) == $hub->id ? 'selected' : '' }} data-code="{{ $hub->code }}">
                                {{ $hub->code }} - {{ $hub->name }} ({{ $hub->country }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Partner Agency -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">
                        Destination Partner Agency
                    </label>
                    <select name="agency_id" id="agency_id" class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Direct Hub Facility / Select Agency --</option>
                        @foreach($agencies as $agency)
                            <option value="{{ $agency->id }}" data-hub="{{ $agency->hub_id }}" {{ old('agency_id', $agencyId) == $agency->id ? 'selected' : '' }}>
                                {{ $agency->name }} ({{ $agency->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Service Classification -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">
                        Service Class <span class="text-rose-500">*</span>
                    </label>
                    <select name="service_type" id="service_type" required class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="economy" {{ old('service_type', $serviceType) == 'economy' ? 'selected' : '' }}>Economy (Agency & Hub Routing)</option>
                        <option value="express" {{ old('service_type', $serviceType) == 'express' ? 'selected' : '' }}>Express / Priority (3-4 Days DHL/UPS/FedEx/SF)</option>
                    </select>
                </div>
            </div>

            <!-- MAWB Pool Selection & Auto-Binding Engine -->
            <div class="bg-sky-50/50 dark:bg-sky-950/20 border border-sky-200 dark:border-sky-800/60 rounded-xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-xs font-bold uppercase text-sky-900 dark:text-sky-300 flex items-center gap-1.5">
                            <i class="fas fa-barcode text-sky-600"></i> Bind Master Airway Bill (MAWB) <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Select an unused MAWB from the inventory stock or let the system auto-bind the first available one.
                        </p>
                    </div>
                    <button type="button" onclick="autoSelectFirstMawb()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-600 hover:bg-sky-500 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                        <i class="fas fa-magic"></i> Auto-Bind Unused MAWB
                    </button>
                </div>

                <select name="mawb_id" id="mawb_id" required class="w-full font-mono text-sm px-3.5 py-2.5 rounded-xl border border-sky-300 dark:border-sky-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-sky-500">
                    <option value="">-- Select Unused MAWB from Inventory Pool --</option>
                    @forelse($unusedMawbs as $mawb)
                        <option value="{{ $mawb->id }}" data-airline="{{ $mawb->airline_name }}" data-flight="{{ $mawb->flight_number }}" data-date="{{ $mawb->flight_date }}" {{ old('mawb_id') == $mawb->id ? 'selected' : '' }}>
                            MAWB: {{ $mawb->mawb_number }} &bull; {{ $mawb->airline_name }} (Flight: {{ $mawb->flight_number ?? 'TBD' }}) &bull; [UNUSED STOCK]
                        </option>
                    @empty
                        <option value="" disabled>No unused MAWBs available in pool! Please pre-feed MAWBs first.</option>
                    @endforelse
                </select>
                @if($unusedMawbs->isEmpty())
                    <p class="text-xs text-rose-500 font-semibold flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i> MAWB pool exhausted. <a href="{{ route('international.mawbs.create') }}" class="underline font-bold" target="_blank">Pre-feed new MAWB here</a>.
                    </p>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">Airline Flight Number</label>
                    <input type="text" name="flight_number" id="flight_number" value="{{ old('flight_number') }}" placeholder="e.g. EK-565, QR-651" class="w-full font-mono text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">Scheduled Flight Date</label>
                    <input type="date" name="flight_date" id="flight_date" value="{{ old('flight_date', date('Y-m-d')) }}" class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">Default Last Mile Courier</label>
                    <input type="text" name="last_mile_carrier_name" value="{{ old('last_mile_carrier_name') }}" placeholder="e.g. Canpar, Obibox, Royal Mail, AusPost" class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">Consolidation Notes / Cargo Handover Instructions</label>
                <textarea name="notes" rows="2" placeholder="Pre-cleared air-cargo consolidation details, pallet strapping, airline security declaration..." class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Shipments Selection Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden space-y-3">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-boxes text-indigo-500"></i> Packaged Shipments Ready for Manifesting
                    </h3>
                    <p class="text-xs text-slate-500">
                        Select packaged international shipments to consolidate into this flight manifest.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 px-3 py-1.5 rounded-xl border border-indigo-200 dark:border-indigo-800">
                        Selected: <span id="selectedCount" class="font-bold">0</span> pkts (<span id="selectedWeight" class="font-bold">0.00</span> kg)
                    </span>
                    <button type="button" onclick="toggleSelectAllShipments()" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 px-3 py-1.5 rounded-lg border border-indigo-300 dark:border-indigo-700">
                        Select / Deselect All
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3 w-12 text-center">
                                <input type="checkbox" id="masterCheckbox" onchange="toggleSelectAllShipments(this.checked)" class="w-4 h-4 text-indigo-600 rounded">
                            </th>
                            <th class="px-5 py-3">Tracking / HAWB</th>
                            <th class="px-5 py-3">Consignee & Destination</th>
                            <th class="px-5 py-3">Packaging & Weight</th>
                            <th class="px-5 py-3">Customs Mode</th>
                            <th class="px-5 py-3">Service / Milestone</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse($eligibleShipments as $shipment)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3 text-center">
                                    <input type="checkbox" name="shipment_ids[]" value="{{ $shipment->id }}" data-weight="{{ $shipment->actual_weight ?? $shipment->chargeable_weight ?? 1.0 }}" onchange="recalculateManifestTotals()" class="shipment-checkbox w-4 h-4 text-indigo-600 rounded">
                                </td>
                                <td class="px-5 py-3 font-mono">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $shipment->tracking_number }}</span>
                                    @if($shipment->hawb_number)
                                        <div class="text-[11px] text-indigo-600 dark:text-indigo-400">HAWB: {{ $shipment->hawb_number }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $shipment->receiver_name }}</div>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-1">
                                        <i class="fas fa-map-marker-alt text-rose-500 text-[10px]"></i>
                                        {{ $shipment->receiver_city ?? 'City' }}, {{ $shipment->receiver_country ?? 'Destination' }}
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ number_format($shipment->actual_weight ?? 1.0, 2) }} kg</span>
                                    <div class="text-[10px] text-slate-400">{{ $shipment->package_type ?? 'Carton Box' }}</div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ ($shipment->customs_mode ?? 'DDP') == 'DDP' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                                        {{ $shipment->customs_mode ?? 'DDP' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-xs font-semibold text-indigo-600">{{ ucfirst($shipment->service_type ?? 'economy') }}</span>
                                    <div class="text-[10px] text-slate-400">{{ $shipment->agency_milestone ?? 'Packaging Completed' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                    No shipments currently ready for manifesting. Book international shipments and ensure status is marked "Packaging Completed".
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('international.manifests.index') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</a>
            <button type="submit" id="btnSubmitManifest" class="inline-flex items-center gap-2 px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold shadow-xl shadow-indigo-600/30 transition transform hover:-translate-y-0.5">
                <i class="fas fa-check-circle"></i> Generate Manifest & Bind MAWB
            </button>
        </div>
    </form>
</div>

<script>
function autoSelectFirstMawb() {
    const mawbSelect = document.getElementById('mawb_id');
    for (let i = 0; i < mawbSelect.options.length; i++) {
        if (mawbSelect.options[i].value && !mawbSelect.options[i].disabled) {
            mawbSelect.selectedIndex = i;
            const opt = mawbSelect.options[i];
            if (opt.dataset.flight) {
                document.getElementById('flight_number').value = opt.dataset.flight;
            }
            if (opt.dataset.date) {
                document.getElementById('flight_date').value = opt.dataset.date;
            }
            break;
        }
    }
}

function filterHubAgencies() {
    const hubId = document.getElementById('hub_id').value;
    const agencySelect = document.getElementById('agency_id');
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

function toggleSelectAllShipments(forceState) {
    const checkboxes = document.querySelectorAll('.shipment-checkbox');
    const master = document.getElementById('masterCheckbox');
    const newState = (forceState !== undefined) ? forceState : !master.checked;
    master.checked = newState;
    checkboxes.forEach(cb => { cb.checked = newState; });
    recalculateManifestTotals();
}

function recalculateManifestTotals() {
    const checkboxes = document.querySelectorAll('.shipment-checkbox:checked');
    let totalWeight = 0;
    checkboxes.forEach(cb => {
        totalWeight += parseFloat(cb.dataset.weight || 0);
    });
    document.getElementById('selectedCount').innerText = checkboxes.length;
    document.getElementById('selectedWeight').innerText = totalWeight.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    filterHubAgencies();
    recalculateManifestTotals();
});
</script>
@endsection
