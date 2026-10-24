@extends('layouts.public')

@section('title', 'Universal Cargo & Parcel Tracking - COURIER with NETPACK')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header Hero Card -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-teal-950 to-teal-900 p-8 md:p-12 text-white shadow-2xl border border-teal-800/40">
        <div class="relative z-10 max-w-2xl mx-auto text-center space-y-4">
            <span class="inline-flex items-center gap-2 rounded-full bg-teal-500/20 px-4 py-1.5 text-xs font-semibold text-teal-300 backdrop-blur border border-teal-400/30">
                <i class="fas fa-satellite-dish"></i> World-Class Automated Tracking &middot; International & Domestic
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight">
                Track & Trace Any Consignment
            </h1>
            <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                Universal search across NETPACK Tracking Numbers, Regional HAWBs, Airline MAWBs, Global Delivery Carriers, Domestic Shipments, and Scheduled Pickups.
            </p>

            <!-- Universal Search Form -->
            <form method="GET" action="{{ route('tracking.search') }}" class="pt-4 max-w-xl mx-auto" id="trackingForm" onsubmit="saveSearchHistory()">
                <div class="relative flex flex-col sm:flex-row gap-2 bg-white/10 backdrop-blur-md p-2 rounded-2xl border border-white/20 shadow-inner">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-teal-300">
                            <i class="fas fa-barcode text-lg"></i>
                        </span>
                        <input type="text" name="tracking" id="trackingInput"
                               placeholder="e.g. NPI-2026-000001-4, USNP-2026-001, or 176-12345678"
                               class="w-full pl-11 pr-4 py-3.5 bg-white text-slate-900 font-mono font-semibold rounded-xl placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-400 uppercase text-sm md:text-base tracking-wide"
                               required
                               autocomplete="off"
                               autofocus>
                    </div>
                    <button type="submit"
                            class="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold px-7 py-3.5 rounded-xl transition duration-200 flex items-center justify-center gap-2 shadow-lg shadow-teal-500/30 shrink-0">
                        <i class="fas fa-magnifying-glass"></i>
                        <span>Track Now</span>
                    </button>
                </div>
            </form>

            <!-- Quick Test Chips -->
            <div class="pt-2 text-xs text-slate-300 flex flex-wrap items-center justify-center gap-2">
                <span class="text-slate-400 font-medium">Quick samples:</span>
                <button type="button" onclick="fillTracking('NPI-2026-000001-4')" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-teal-200 font-mono border border-white/10 transition">
                    NPI-2026-000001-4 (Intl)
                </button>
                <button type="button" onclick="fillTracking('USNP-2026-001')" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-teal-200 font-mono border border-white/10 transition">
                    USNP-2026-001 (HAWB)
                </button>
                <button type="button" onclick="fillTracking('NPD-2026-000001-8')" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-teal-200 font-mono border border-white/10 transition">
                    NPD-2026-000001-8 (Domestic)
                </button>
                <button type="button" onclick="fillTracking('NPE-2026-000001-6')" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-teal-200 font-mono border border-white/10 transition">
                    NPE-2026-000001-6 (Ecom)
                </button>
            </div>

            <!-- Recent Searches Section (Loaded from LocalStorage) -->
            <div id="recentSearchesContainer" class="pt-3 hidden">
                <span class="text-[11px] text-slate-400 uppercase tracking-wider font-bold">Recent Inquiries:</span>
                <div id="recentSearchesList" class="flex flex-wrap items-center justify-center gap-1.5 mt-1.5"></div>
            </div>
        </div>

        <!-- Ambient Glow -->
        <div class="absolute -bottom-16 -right-16 w-64 h-64 bg-teal-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -top-16 -left-16 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- 4 Supported Multi-Identifier Columns -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- International Service -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:border-teal-500/40 hover:shadow-md transition space-y-3">
            <div class="flex items-center gap-3">
                <span class="h-10 w-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fas fa-plane-departure"></i>
                </span>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">International Hub & Agency</h3>
                    <p class="text-xs text-slate-500">IATA Air Freight & HAWB</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Automated multi-stage tracking from Tribhuvan Airport (KTM) through overseas transit hubs (DXB, LHR, FRA, JFK) with live customs clearance and FedEx/DHL last-mile synchronization.
            </p>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-sky-700 font-mono">
                <span>NPI-... / HAWB / MAWB</span>
                <i class="fas fa-arrow-right"></i>
            </div>
        </div>

        <!-- Domestic Service -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:border-teal-500/40 hover:shadow-md transition space-y-3">
            <div class="flex items-center gap-3">
                <span class="h-10 w-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fas fa-truck-fast"></i>
                </span>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Domestic Express Fleet</h3>
                    <p class="text-xs text-slate-500">All 7 Provinces & 77 Districts</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Automated highway fleet tracking, bag dispatch scans between Nepal district sorting hubs, ward-level rider allocation, and verified digital proof of delivery.
            </p>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-teal-700 font-mono">
                <span>NPD-YYYY-XXXXXX-C</span>
                <i class="fas fa-arrow-right"></i>
            </div>
        </div>

        <!-- Pickups & E-Commerce -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:border-teal-500/40 hover:shadow-md transition space-y-3">
            <div class="flex items-center gap-3">
                <span class="h-10 w-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fas fa-box-check"></i>
                </span>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Pickups & E-Commerce</h3>
                    <p class="text-xs text-slate-500">Live Collection & Courier Dispatch</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Automated tracking from scheduled pickup window, courier dispatch, physical sender collection, sorting depot intake, and live rider GPS delivery.
            </p>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-indigo-700 font-mono">
                <span>NPE-... / PICKUP-...</span>
                <i class="fas fa-arrow-right"></i>
            </div>
        </div>
    </div>

    <!-- Security & Privacy Guarantee -->
    <div class="bg-slate-100 rounded-3xl p-6 border border-slate-200 text-xs text-slate-600 flex flex-col sm:flex-row items-center gap-4">
        <span class="h-10 w-10 shrink-0 rounded-2xl bg-slate-200 text-slate-700 flex items-center justify-center text-lg">
            <i class="fas fa-shield-halved"></i>
        </span>
        <div class="space-y-1">
            <p class="font-bold text-slate-800">Privacy-Safe Enterprise Telemetry</p>
            <p class="leading-relaxed">
                In compliance with international data privacy laws, public tracking displays verified operational milestones, departure hubs, airline flight corridors, and destination cities without exposing private telephone numbers, full personal street addresses, or private invoices.
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function fillTracking(number) {
    const input = document.getElementById('trackingInput');
    input.value = number;
    input.focus();
}

function saveSearchHistory() {
    const val = document.getElementById('trackingInput').value.trim();
    if (!val) return;
    try {
        let recents = JSON.parse(localStorage.getItem('netpack_recent_tracks') || '[]');
        recents = recents.filter(x => x.toUpperCase() !== val.toUpperCase());
        recents.unshift(val.toUpperCase());
        if (recents.length > 5) recents.pop();
        localStorage.setItem('netpack_recent_tracks', JSON.stringify(recents));
    } catch(e) {}
}

document.addEventListener('DOMContentLoaded', function() {
    try {
        const recents = JSON.parse(localStorage.getItem('netpack_recent_tracks') || '[]');
        if (recents.length > 0) {
            const container = document.getElementById('recentSearchesContainer');
            const list = document.getElementById('recentSearchesList');
            recents.forEach(num => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'px-2.5 py-0.5 rounded-lg bg-teal-500/20 hover:bg-teal-500/30 text-teal-300 font-mono text-[11px] border border-teal-500/30 transition';
                btn.textContent = num;
                btn.onclick = () => fillTracking(num);
                list.appendChild(btn);
            });
            container.classList.remove('hidden');
        }
    } catch(e) {}
});
</script>
@endpush
@endsection
