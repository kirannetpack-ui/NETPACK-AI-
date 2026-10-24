@extends('layouts.app')

@section('title', 'Manifest Details - ' . $manifest->manifest_number)
@section('page-title', '📦 Manifest Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-5 border-b flex flex-col md:flex-row justify-between md:items-center gap-4 bg-slate-50/50">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-gray-800">Manifest #{{ $manifest->manifest_number }}</h1>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $manifest->status_badge }}">
                        {{ $manifest->status_label }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Created: {{ $manifest->created_at->format('M d, Y H:i') }} | 
                    Route: <strong class="text-gray-700">{{ $manifest->origin_city ?? 'Kathmandu' }}</strong> ➔ <strong class="text-emerald-700">{{ $manifest->destination_city ?? 'Destination' }}</strong>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('domestic.manifests.index') }}" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-300 transition text-xs font-bold">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
                <a href="{{ route('domestic.manifests.arrival-notice', $manifest->id) }}" class="bg-emerald-600 text-white px-3.5 py-2 rounded-lg hover:bg-emerald-700 transition text-xs font-bold flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-truck-ramp-box"></i> Inbound Arrival Notice
                </a>
                <a href="{{ route('domestic.manifests.scan') }}" class="bg-indigo-600 text-white px-3.5 py-2 rounded-lg hover:bg-indigo-700 transition text-xs font-bold flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-barcode"></i> Scan Desk
                </a>
                @if($manifest->partner)
                <form method="POST" action="{{ route('domestic.manifests.send-partner-reminder', $manifest->id) }}" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Send automated SLA delivery reminder to partner {{ $manifest->partner->name }}?');" class="bg-amber-500 text-white px-3.5 py-2 rounded-lg hover:bg-amber-600 transition text-xs font-bold flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-bell"></i> Send SLA Reminder
                    </button>
                </form>
                @endif
                <button onclick="window.print()" class="bg-teal-600 text-white px-3.5 py-2 rounded-lg hover:bg-teal-700 transition text-xs font-bold flex items-center gap-1.5">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg mb-6 text-sm flex items-center gap-2">
                    <i class="fas fa-check-circle text-emerald-600"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-lg mb-6 text-sm flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-rose-600"></i>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Manifest Info Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="border rounded-lg p-4 bg-slate-50/50">
                    <p class="text-xs text-gray-500">Load Type</p>
                    <p class="font-bold text-gray-800">{{ ucfirst(str_replace('_', ' ', $manifest->load_type)) }}</p>
                </div>
                <div class="border rounded-lg p-4 bg-slate-50/50">
                    <p class="text-xs text-gray-500">Total Bags</p>
                    <p class="font-bold text-gray-800">{{ $manifest->total_bags }}</p>
                </div>
                <div class="border rounded-lg p-4 bg-slate-50/50">
                    <p class="text-xs text-gray-500">Total Consignments</p>
                    <p class="font-bold text-gray-800">{{ $manifest->total_shipments }} PKG</p>
                </div>
                <div class="border rounded-lg p-4 bg-slate-50/50">
                    <p class="text-xs text-gray-500">Total Weight</p>
                    <p class="font-bold text-gray-800">{{ number_format($manifest->total_weight, 2) }} kg</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-xs text-gray-500">Origin Gateway</p>
                    <p class="font-semibold">{{ $manifest->origin_city ?? 'Kathmandu' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-xs text-gray-500">Destination Hub</p>
                    <p class="font-semibold">{{ $manifest->destination_city ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-xs text-gray-500">Assigned Partner</p>
                    <p class="font-semibold text-teal-700">{{ $manifest->partner->name ?? 'Unassigned' }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-xs text-gray-500">Created By</p>
                    <p class="font-semibold">{{ $manifest->creator->name ?? 'Admin Operations' }}</p>
                </div>
            </div>

            <!-- Bags Section -->
            <div class="border-t pt-5">
                <h3 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-box-open text-teal-600"></i> Manifest Bags & Barcodes ({{ $manifest->bags->count() }})
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($manifest->bags as $bag)
                        <div class="border rounded-xl p-4 {{ $bag->status === 'scanned' ? 'bg-blue-50/50 border-blue-200' : ($bag->status === 'sorted' ? 'bg-purple-50/50 border-purple-200' : ($bag->status === 'dispatched' ? 'bg-green-50/50 border-green-200' : 'bg-white')) }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-bold text-sm text-gray-900">{{ $bag->bag_number }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Type: {{ ucfirst(str_replace('_', ' ', $bag->bag_type)) }}</p>
                                    <p class="text-xs text-gray-500">Contents: <strong>{{ $bag->shipment_count }} Packages</strong></p>
                                    <p class="text-xs text-gray-500">Weight: {{ number_format($bag->weight, 2) }} kg</p>
                                    @if($bag->current_location)
                                        <p class="text-[11px] text-teal-700 mt-1">📍 {{ $bag->current_location }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $bag->status_badge }}">
                                        {{ ucfirst($bag->status) }}
                                    </span>
                                    <div class="mt-2">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data={{ $bag->qr_code }}" 
                                             alt="QR Code" class="w-14 h-14 inline-block border rounded p-0.5 bg-white">
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 pt-2 border-t border-slate-200 flex items-center gap-1.5">
                                <button onclick="scanBag('{{ $bag->qr_code }}', 'receive')" class="flex-1 bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700 transition font-semibold">
                                    <i class="fas fa-check mr-1"></i> Receive
                                </button>
                                <button onclick="scanBag('{{ $bag->qr_code }}', 'sort')" class="flex-1 bg-purple-600 text-white px-2 py-1 rounded text-xs hover:bg-purple-700 transition font-semibold">
                                    <i class="fas fa-sort mr-1"></i> Sort
                                </button>
                                <button onclick="scanBag('{{ $bag->qr_code }}', 'dispatch')" class="flex-1 bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700 transition font-semibold">
                                    <i class="fas fa-paper-plane mr-1"></i> Dispatch
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Shipments Section & Bulk Re-Manifesting -->
            <div class="border-t pt-6 mt-6">
                <form method="POST" action="{{ route('domestic.manifests.bulk-re-manifest') }}" id="bulkRemanifestForm">
                    @csrf
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-boxes-stacked text-teal-600"></i> 
                                Consignments ({{ $manifest->shipments->count() }} Total)
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Select multiple arrived packages below to initiate Bulk Re-Manifesting / Transshipment across Nepal.</p>
                        </div>

                        <!-- Bulk Action Controls -->
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="openBulkModal()" id="btnBulkRemanifest" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm disabled:opacity-50" disabled>
                                <i class="fas fa-share-nodes"></i> Bulk Re-Manifest Selected (<span id="selectedCount">0</span>)
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-slate-200 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-3 w-10 text-center">
                                        <input type="checkbox" id="selectAllShipments" onchange="toggleAllShipments(this)" class="w-4 h-4 text-teal-600 rounded border-slate-300">
                                    </th>
                                    <th class="py-3 px-3">Tracking # / HAWB</th>
                                    <th class="py-3 px-3">Receiver</th>
                                    <th class="py-3 px-3">Destination</th>
                                    <th class="py-3 px-3">Weight</th>
                                    <th class="py-3 px-3">Bag #</th>
                                    <th class="py-3 px-3">Arrival Status</th>
                                    <th class="py-3 px-3">Current Status</th>
                                    <th class="py-3 px-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($manifest->shipments as $manifestShipment)
                                    @php
                                        $s = $manifestShipment->shipment;
                                        $isEligibleForRemanifest = !in_array($manifestShipment->status, ['delivered', 'forwarded'], true);
                                    @endphp
                                    <tr class="hover:bg-slate-50/75 transition">
                                        <td class="py-3 px-3 text-center">
                                            @if($isEligibleForRemanifest && $s)
                                                <input type="checkbox" name="shipment_ids[]" value="{{ $s->id }}" onchange="updateSelectedCount()" class="shipment-row-check w-4 h-4 text-teal-600 rounded border-slate-300">
                                            @else
                                                <span class="text-slate-300 text-[10px]">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 font-mono font-bold text-slate-900">
                                            <a href="{{ route('tracking.show', $s->tracking_number ?? '') }}" target="_blank" class="text-teal-700 hover:underline">
                                                {{ $s->tracking_number ?? 'N/A' }}
                                            </a>
                                            @if(!empty($s->hawb_number))
                                                <span class="block text-[10px] text-slate-500 font-normal">HAWB: {{ $s->hawb_number }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="font-bold text-slate-800">{{ $s->receiver_name ?? 'N/A' }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $s->receiver_phone ?? '' }}</div>
                                        </td>
                                        <td class="py-3 px-3 text-slate-700 font-medium">
                                            {{ $s->receiver_city ?? $manifest->destination_city }}
                                        </td>
                                        <td class="py-3 px-3 font-mono text-slate-700">
                                            {{ number_format($s->actual_weight ?? $s->weight ?? 1.0, 2) }} kg
                                        </td>
                                        <td class="py-3 px-3 font-mono text-slate-600">
                                            {{ $manifestShipment->bag->bag_number ?? 'Bag-01' }}
                                        </td>
                                        <td class="py-3 px-3">
                                            @if($manifestShipment->arrival_status === 'arrived')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1 w-max">
                                                    <i class="fas fa-check-circle"></i> Arrived at Hub
                                                </span>
                                            @elseif($manifestShipment->arrival_status === 'non_arrival')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 flex items-center gap-1 w-max" title="{{ $manifestShipment->non_arrival_remarks }}">
                                                    <i class="fas fa-exclamation-circle"></i> Missing / Exception
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 w-max">
                                                    Pending Arrival
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $manifestShipment->status_badge }}">
                                                {{ ucfirst($manifestShipment->status) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-right">
                                            @if($isEligibleForRemanifest)
                                                <details class="relative inline-block text-left">
                                                    <summary class="cursor-pointer text-xs font-bold text-teal-700 hover:text-teal-800 bg-slate-100 px-2 py-1 rounded">Update</summary>
                                                    <div class="absolute right-0 mt-1 w-64 rounded-xl bg-white border border-slate-200 shadow-xl p-3 z-20 space-y-2 text-left">
                                                        <form method="POST" action="{{ route('domestic.manifests.shipments.update-status', [$manifest, $manifestShipment]) }}">
                                                            @csrf @method('PUT')
                                                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Update Status</label>
                                                            <select name="status" class="w-full rounded-lg border-slate-300 text-xs mb-2" required>
                                                                <option value="">Select status</option>
                                                                @foreach(['received' => 'Received at Hub', 'processed' => 'Processed', 'dispatched' => 'Dispatched', 'delivery_attempted' => 'Delivery Attempted', 'delivered' => 'Delivered', 'exception' => 'Exception'] as $value => $label)
                                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                            <input name="location" class="w-full rounded-lg border-slate-300 text-xs mb-2" placeholder="Current facility location">
                                                            <input name="notes" class="w-full rounded-lg border-slate-300 text-xs mb-2" placeholder="Operational note">
                                                            <button class="w-full rounded-lg bg-teal-600 px-2 py-1.5 text-xs font-bold text-white hover:bg-teal-700">Save Status</button>
                                                        </form>
                                                    </div>
                                                </details>
                                            @else
                                                <span class="text-[11px] text-gray-400">Completed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Bulk Re-Manifest Modal Details (Hidden until user clicks Bulk Re-manifest) -->
                    <div id="bulkModal" class="fixed inset-0 bg-slate-900/60 z-50 hidden flex items-center justify-center p-4">
                        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b">
                                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                    <i class="fas fa-share-nodes text-indigo-600"></i> Bulk Re-Manifest / Transshipment
                                </h3>
                                <button type="button" onclick="closeBulkModal()" class="text-slate-400 hover:text-slate-600">
                                    <i class="fas fa-xmark text-lg"></i>
                                </button>
                            </div>

                            <p class="text-xs text-slate-600">
                                Consolidating <strong id="modalPackageCount">0</strong> selected packages into a new outward domestic manifest for linehaul or partner delivery.
                            </p>

                            <div class="space-y-3 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Origin City / Sorting Hub <span class="text-rose-500">*</span></label>
                                    <input type="text" name="origin_city" value="{{ $manifest->destination_city ?? $manifest->current_location ?? 'Kathmandu' }}" required class="w-full rounded-xl border-slate-300 px-3 py-2 text-xs">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Destination City / Hub <span class="text-rose-500">*</span></label>
                                    <input type="text" name="destination_city" placeholder="e.g. Pokhara, Biratnagar, Nepalgunj, Butwal" required class="w-full rounded-xl border-slate-300 px-3 py-2 text-xs">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Forwarding Domestic Partner / Branch <span class="text-rose-500">*</span></label>
                                    <select name="partner_id" required class="w-full rounded-xl border-slate-300 px-3 py-2 text-xs">
                                        <option value="">Select Partner</option>
                                        @foreach($forwardPartners as $partner)
                                            <option value="{{ $partner->id }}">{{ $partner->name }} ({{ $partner->email }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Load Type</label>
                                    <select name="load_type" class="w-full rounded-xl border-slate-300 px-3 py-2 text-xs">
                                        <option value="re_manifested">Re-Manifested Transit</option>
                                        <option value="linehaul">Highway Linehaul</option>
                                        <option value="express">Direct Express</option>
                                        <option value="consolidated">Consolidated Bag</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Operational Notes</label>
                                    <textarea name="notes" rows="2" placeholder="Routing details (e.g. Prithvi Highway Linehaul, Westbound Night Truck)" class="w-full rounded-xl border-slate-300 px-3 py-2 text-xs"></textarea>
                                </div>
                            </div>

                            <div class="pt-3 border-t flex items-center justify-end gap-2">
                                <button type="button" onclick="closeBulkModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100">Cancel</button>
                                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white shadow-md">Create & Forward Re-Manifest</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tracking Logs Section -->
            <div class="border-t pt-6 mt-6">
                <h3 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-timeline text-teal-600"></i> Manifest Audit & Movement History ({{ $manifest->trackingLogs->count() }})
                </h3>
                <div class="max-h-64 overflow-y-auto divide-y divide-slate-100 border border-slate-200 rounded-xl p-3 bg-slate-50/50 space-y-2">
                    @forelse($manifest->trackingLogs->sortByDesc('created_at') as $log)
                        <div class="flex items-start gap-3 py-2">
                            <span class="w-2.5 h-2.5 mt-1 rounded-full bg-teal-500 shrink-0"></span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between text-xs">
                                    <strong class="text-slate-900 font-bold uppercase tracking-wider text-[11px]">{{ ucfirst($log->event_type) }}</strong>
                                    <span class="text-[10px] text-slate-400">{{ $log->created_at->format('M d, Y H:i') }}</span>
                                </div>
                                <p class="text-xs text-slate-600 mt-0.5">{{ $log->description }}</p>
                                <div class="text-[10px] text-slate-400 mt-1 flex items-center gap-2">
                                    <span>📍 {{ $log->location ?? 'Facility' }}</span>
                                    <span>&bull;</span>
                                    <span>👤 {{ $log->performedBy->name ?? 'System' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">No telemetry movement events recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function scanBag(qrCode, action) {
    const location = prompt('Enter Hub Facility Location:', '{{ $manifest->current_location ?? 'Kathmandu Central Hub' }}');
    if (location === null) return;
    
    fetch('{{ route("domestic.manifests.scan-bag") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            qr_code: qrCode,
            action: action,
            location: location || 'Kathmandu Central Hub'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + (data.message || 'Scan failed'));
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
    });
}

function toggleAllShipments(master) {
    const checkboxes = document.querySelectorAll('.shipment-row-check');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.shipment-row-check:checked');
    const count = checked.length;
    document.getElementById('selectedCount').innerText = count;
    document.getElementById('modalPackageCount').innerText = count;
    const btn = document.getElementById('btnBulkRemanifest');
    btn.disabled = count === 0;
}

function openBulkModal() {
    document.getElementById('bulkModal').classList.remove('hidden');
}

function closeBulkModal() {
    document.getElementById('bulkModal').classList.add('hidden');
}
</script>
@endsection
