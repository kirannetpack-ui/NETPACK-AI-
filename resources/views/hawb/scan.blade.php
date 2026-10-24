@extends('layouts.app')

@section('title', 'Shipment Scan Desk')
@section('page-title', 'Shipment Scan Desk')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <section class="overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-teal-900 p-6 text-white shadow-lg md:p-8">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-teal-300">NETPACK operations</p>
                <h1 class="mt-2 text-3xl font-bold">Scan a real journey milestone</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Every scan records who processed it, where it happened, the source, and the customer-facing status. Only logically valid next events are offered.</p>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center text-xs">
                <div class="rounded-xl bg-white/10 p-3"><i class="fas fa-box mb-2 block text-lg text-teal-300"></i>Domestic</div>
                <div class="rounded-xl bg-white/10 p-3"><i class="fas fa-plane mb-2 block text-lg text-sky-300"></i>International</div>
                <div class="rounded-xl bg-white/10 p-3"><i class="fas fa-shield-halved mb-2 block text-lg text-emerald-300"></i>Audited</div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(340px,0.8fr)]">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div><p class="text-sm font-medium text-teal-700">Step 1</p><h2 class="text-xl font-bold text-slate-900">Identify shipment</h2></div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-xl text-teal-700"><i class="fas fa-qrcode"></i></span>
            </div>

            <div class="mt-5 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 p-4 text-center">
                <div id="reader" class="mx-auto max-w-md overflow-hidden rounded-lg"></div>
                <button id="cameraButton" type="button" class="mt-3 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:border-teal-500 hover:text-teal-700"><i class="fas fa-camera mr-2"></i>Start camera scanner</button>
                <p id="cameraHelp" class="mt-2 text-xs text-slate-500">Camera access is optional; a USB scanner or manual entry also works.</p>
            </div>

            <form id="lookupForm" class="mt-5">
                <label for="manualTracking" class="text-sm font-semibold text-slate-700">Tracking or HAWB number</label>
                <div class="mt-2 flex gap-2">
                    <input id="manualTracking" type="text" autocomplete="off" placeholder="e.g. NPI-2026-000101-7" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-4 py-3 font-mono uppercase focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                    <button class="rounded-lg bg-teal-600 px-5 py-3 font-semibold text-white hover:bg-teal-700" type="submit"><i class="fas fa-search mr-2"></i>Find</button>
                </div>
            </form>
            <div id="lookupMessage" class="mt-4 hidden rounded-lg p-3 text-sm"></div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div><p class="text-sm font-medium text-teal-700">Step 2</p><h2 class="text-xl font-bold text-slate-900">Record checkpoint</h2></div>
                <span id="serviceIcon" class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-xl text-slate-500"><i class="fas fa-box-open"></i></span>
            </div>

            <div id="emptyState" class="py-14 text-center text-slate-500">
                <i class="fas fa-barcode text-4xl text-slate-300"></i><p class="mt-3 font-medium">Scan or enter a shipment first</p><p class="mt-1 text-xs">The next valid events will appear here.</p>
            </div>

            <form id="scanForm" class="mt-5 hidden space-y-4">
                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div><p id="serviceLabel" class="text-xs font-semibold uppercase tracking-wide text-teal-700"></p><p id="resultTracking" class="mt-1 font-mono text-lg font-bold text-slate-900"></p><p id="routeSummary" class="mt-1 text-sm text-slate-500"></p></div>
                        <span id="statusBadge" class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold capitalize text-blue-700"></span>
                    </div>
                </div>
                <div>
                    <label for="eventCode" class="text-sm font-semibold text-slate-700">Next physical event</label>
                    <select id="eventCode" required class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-3 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200"></select>
                    <p class="mt-1 text-xs text-slate-500">Backward or service-incompatible scans are blocked by the server.</p>
                </div>
                <div><label for="scanLocation" class="text-sm font-semibold text-slate-700">Facility / location</label><input id="scanLocation" required maxlength="255" placeholder="e.g. Kathmandu Gateway" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-3 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200"></div>
                <div><label for="scanNotes" class="text-sm font-semibold text-slate-700">Operational note <span class="font-normal text-slate-400">(optional)</span></label><textarea id="scanNotes" rows="2" maxlength="1000" placeholder="Exception details, bag number, customs reference…" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-3 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200"></textarea></div>
                <button id="recordButton" type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-3 font-semibold text-white hover:bg-slate-800"><i class="fas fa-stamp mr-2"></i>Record verified scan</button>
            </form>
        </section>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const lookupUrl = @json(route('hawb.scan'));
const updateUrl = @json(route('hawb.update-from-scan'));
let shipment = null;
let scanner = null;

function normalizeTracking(value) {
    const text = value.trim();
    try { const url = new URL(text); return decodeURIComponent(url.pathname.split('/').filter(Boolean).pop() || text); }
    catch (_) { return text; }
}
function showMessage(message, success = false) {
    const element = document.getElementById('lookupMessage');
    element.textContent = message;
    element.className = `mt-4 rounded-lg p-3 text-sm ${success ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'}`;
}
async function lookupShipment(value) {
    const tracking = normalizeTracking(value);
    if (!tracking) return;
    const response = await fetch(lookupUrl, {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken}, body: JSON.stringify({tracking})});
    const data = await response.json();
    if (!response.ok || !data.success) throw new Error(data.message || 'Shipment could not be found.');
    shipment = data.shipment;
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('scanForm').classList.remove('hidden');
    document.getElementById('resultTracking').textContent = shipment.tracking_number;
    document.getElementById('serviceLabel').textContent = shipment.service.label;
    document.getElementById('routeSummary').textContent = `${shipment.receiver_city || 'Destination'} · ${shipment.type === 'international' ? 'International' : 'Domestic'}`;
    document.getElementById('statusBadge').textContent = shipment.status.replaceAll('_', ' ');
    document.getElementById('serviceIcon').innerHTML = `<i class="fas ${shipment.service.icon}"></i>`;
    document.getElementById('serviceIcon').className = 'flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-xl text-teal-700';
    const select = document.getElementById('eventCode');
    select.innerHTML = shipment.available_events.length ? shipment.available_events.map(event => `<option value="${event.code}">${event.label}</option>`).join('') : '<option value="">No further events available</option>';
    document.getElementById('recordButton').disabled = shipment.available_events.length === 0;
    showMessage('Shipment verified. Select the physical event that just occurred.', true);
}
document.getElementById('lookupForm').addEventListener('submit', async event => { event.preventDefault(); try { await lookupShipment(document.getElementById('manualTracking').value); } catch (error) { showMessage(error.message); } });
document.getElementById('scanForm').addEventListener('submit', async event => {
    event.preventDefault();
    const button = document.getElementById('recordButton');
    button.disabled = true; button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Recording scan…';
    try {
        const response = await fetch(updateUrl, {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken}, body: JSON.stringify({tracking: shipment.tracking_number, event_code: document.getElementById('eventCode').value, location: document.getElementById('scanLocation').value, notes: document.getElementById('scanNotes').value})});
        const data = await response.json();
        if (!response.ok || !data.success) { const validationMessage = data.errors ? Object.values(data.errors).flat()[0] : null; throw new Error(validationMessage || data.message || 'The scan could not be recorded.'); }
        document.getElementById('scanNotes').value = '';
        await lookupShipment(shipment.tracking_number);
        showMessage('Verified scan recorded successfully.', true);
    } catch (error) { showMessage(error.message); }
    finally { button.disabled = false; button.innerHTML = '<i class="fas fa-stamp mr-2"></i>Record verified scan'; }
});
document.getElementById('cameraButton').addEventListener('click', async () => {
    if (typeof Html5Qrcode === 'undefined') return showMessage('Camera scanner could not be loaded. Use manual entry.');
    try {
        scanner = scanner || new Html5Qrcode('reader');
        await scanner.start({facingMode: 'environment'}, {fps: 10, qrbox: {width: 240, height: 180}}, async decoded => { await scanner.stop(); document.getElementById('manualTracking').value = normalizeTracking(decoded); try { await lookupShipment(decoded); } catch (error) { showMessage(error.message); } });
        document.getElementById('cameraButton').classList.add('hidden');
        document.getElementById('cameraHelp').textContent = 'Camera active — place the HAWB QR code inside the frame.';
    } catch (_) { showMessage('Camera access was unavailable. You can continue with manual or USB-scanner entry.'); }
});
</script>
@endsection
