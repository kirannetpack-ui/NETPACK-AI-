@extends('layouts.app')

@section('title', 'Domestic Consignment & Bag Scan Desk')
@section('page-title', '📱 Domestic Consignment Scan Desk')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 rounded-2xl shadow-xl p-6 text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight flex items-center gap-2">
                <i class="fas fa-barcode text-teal-400"></i>
                Domestic Scan Desk: Nepal-Wide Inbound & Transit
            </h1>
            <p class="text-slate-300 text-xs mt-1">
                Scan Domestic Consignment Notes, HAWBs, or Bag Barcodes to stamp instantaneous arrival, sorting, and dispatch telemetry across Nepal's provincial network.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Scanner Ready
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Control & Scanner Panel -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-2 pb-2 border-b">
                    <i class="fas fa-sliders text-teal-600"></i> Scan Configuration
                </h3>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Scan Action Mode</label>
                    <select id="scanAction" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-teal-500 font-semibold">
                        <option value="arrival">📥 Hub Arrival Notice (Receive into Hub)</option>
                        <option value="dispatch">🚚 Transit Dispatch (Forward to Next Hub)</option>
                        <option value="delivery">🛵 Out for Delivery (Handover to Rider/Partner)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Current Hub Location</label>
                    <select id="scanLocation" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-teal-500 font-semibold">
                        @foreach($nepalHubs as $hub)
                            <option value="{{ $hub }}">{{ $hub }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Status Note (Optional)</label>
                    <input type="text" id="statusNote" placeholder="e.g. Normal condition, Sorted to Bay 3" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-teal-500">
                </div>

                <!-- Input Field -->
                <div class="pt-2 border-t">
                    <label class="block text-xs font-black uppercase tracking-wider text-teal-800 mb-1">Barcode / QR Input</label>
                    <div class="relative">
                        <input type="text" 
                               id="barcodeInput" 
                               autofocus 
                               placeholder="Scan or type Tracking # / Bag QR..." 
                               class="w-full text-sm font-mono font-bold px-4 py-3 rounded-xl border-2 border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-500/20 bg-teal-50/30 text-slate-900 pr-12">
                        <button type="button" onclick="submitScan()" class="absolute right-2 top-2 h-8 w-8 rounded-lg bg-teal-600 hover:bg-teal-700 text-white flex items-center justify-center transition">
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Accepts handheld USB scanner inputs automatically on Enter key press.</p>
                </div>
            </div>

            <div class="bg-teal-50/60 border border-teal-200 rounded-2xl p-4 text-xs text-teal-900 space-y-2">
                <div class="font-bold flex items-center gap-1.5 text-teal-800">
                    <i class="fas fa-lightbulb"></i> Rapid Operation Tip
                </div>
                <p>
                    Point your laser scanner at any Waybill QR or Bag Barcode. Stamped telemetry includes exact GPS depot coordinates, staff name, and date/time.
                </p>
            </div>
        </div>

        <!-- Scan Log & Telemetry Output -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i class="fas fa-list-check text-teal-600"></i> Scan Feed & Real-Time Verification
                    </h3>
                    <span id="scanCount" class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-slate-200 text-slate-700">0 Scans</span>
                </div>

                <div id="scanFeed" class="divide-y divide-slate-100 max-h-[500px] overflow-y-auto p-4 space-y-3">
                    <div id="emptyFeedMessage" class="text-center py-12 text-slate-400 text-xs">
                        <i class="fas fa-qrcode text-3xl text-slate-300 mb-2 block"></i>
                        Awaiting your first scan. Ready to capture consignment movements.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const barcodeInput = document.getElementById('barcodeInput');
let scanCount = 0;

barcodeInput.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        submitScan();
    }
});

function submitScan() {
    const barcode = barcodeInput.value.trim();
    if (!barcode) return;

    const action = document.getElementById('scanAction').value;
    const location = document.getElementById('scanLocation').value;
    const note = document.getElementById('statusNote').value.trim();

    barcodeInput.disabled = true;

    fetch('{{ route("domestic.manifests.process-scan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            barcode: barcode,
            action: action,
            location: location,
            status_note: note
        })
    })
    .then(res => res.json())
    .then(data => {
        barcodeInput.disabled = false;
        barcodeInput.value = '';
        barcodeInput.focus();

        if (data.success) {
            addScanRecord(data, true);
            playBeep(true);
        } else {
            addScanRecord({ message: data.message, barcode: barcode }, false);
            playBeep(false);
        }
    })
    .catch(err => {
        barcodeInput.disabled = false;
        barcodeInput.focus();
        addScanRecord({ message: 'Network or server error: ' + err.message, barcode: barcode }, false);
        playBeep(false);
    });
}

function addScanRecord(data, isSuccess) {
    const empty = document.getElementById('emptyFeedMessage');
    if (empty) empty.remove();

    const feed = document.getElementById('scanFeed');
    scanCount++;
    document.getElementById('scanCount').innerText = scanCount + ' Scans';

    const card = document.createElement('div');
    card.className = `p-4 rounded-xl border transition animate-fade-in ${isSuccess ? 'bg-emerald-50/40 border-emerald-200' : 'bg-rose-50/40 border-rose-200'}`;

    if (isSuccess) {
        const item = data.item;
        card.innerHTML = `
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-600 text-white mb-1">
                        ${data.type.toUpperCase()}: ${item.number}
                    </span>
                    <h4 class="text-sm font-bold text-slate-900">${item.receiver ? 'Consignee: ' + item.receiver : 'Bag Package Content (' + (item.count || 0) + ' PKG)'}</h4>
                    <p class="text-xs text-slate-600 mt-0.5">${data.message}</p>
                </div>
                <div class="text-right text-[11px] text-slate-500">
                    <div class="font-bold text-slate-800">${item.status}</div>
                    <div>📍 ${item.location}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">${item.time}</div>
                </div>
            </div>
        `;
    } else {
        card.innerHTML = `
            <div class="flex items-start gap-3">
                <i class="fas fa-circle-xmark text-rose-500 text-base mt-0.5"></i>
                <div>
                    <h4 class="text-xs font-bold text-rose-800">Scan Failed: ${data.barcode || 'Code'}</h4>
                    <p class="text-xs text-rose-600 mt-0.5">${data.message}</p>
                </div>
            </div>
        `;
    }

    feed.insertBefore(card, feed.firstChild);
}

function playBeep(success) {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = success ? 'sine' : 'square';
        osc.frequency.value = success ? 800 : 300;
        gain.gain.value = 0.1;
        osc.start();
        setTimeout(() => { osc.stop(); }, success ? 100 : 250);
    } catch(e) {}
}
</script>
@endsection
