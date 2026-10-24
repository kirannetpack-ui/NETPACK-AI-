@extends('layouts.app')

@section('title', 'Flight Manifest ' . $manifest->manifest_number . ' | Netpack')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Card -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('international.manifests.index') }}" class="text-xs font-semibold text-indigo-300 hover:text-white flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i> All Manifests
                    </a>
                    <span class="text-slate-500">&bull;</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        Air Cargo Consolidation
                    </span>
                </div>
                <h1 class="text-3xl font-black tracking-tight font-mono">{{ $manifest->manifest_number }}</h1>
                <p class="text-slate-300 text-sm mt-1">
                    Bound MAWB: <span class="font-mono font-bold text-sky-300">{{ $manifest->mawb_number ?? 'N/A' }}</span> &bull; 
                    Gateway: <span class="font-bold text-white">{{ $manifest->hub->name ?? 'Direct' }}</span>
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('international.manifests.data-sheet', $manifest->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md transition">
                    <i class="fas fa-table"></i> View Data Sheet
                </a>
                <a href="{{ route('international.manifests.data-sheet', [$manifest->id, 'export' => 'csv']) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold border border-slate-700 shadow-sm transition">
                    <i class="fas fa-file-csv text-emerald-400"></i> Export CSV
                </a>
                <button onclick="document.getElementById('emailAgencyModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fas fa-paper-plane"></i> Dispatch to Agency
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-lg"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Key Manifest Info Panels -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Flight & MAWB Details -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i class="fas fa-plane text-indigo-500"></i> Flight & MAWB Information
            </h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">Master Airway Bill:</span>
                    <span class="font-mono font-bold text-sky-600 dark:text-sky-400">{{ $manifest->mawb_number ?? 'Unbound' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">Operating Airline:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $manifest->mawb->airline_name ?? 'Airline Partner' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">Flight Number:</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $manifest->flight_number ?? 'TBD' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Flight Date:</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">
                        {{ $manifest->flight_date ? \Carbon\Carbon::parse($manifest->flight_date)->format('M d, Y') : 'Pending' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Destination Hub & Agency -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i class="fas fa-building text-amber-500"></i> Hub & Receiving Agency
            </h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">Gateway Hub:</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $manifest->hub->code ?? 'N/A' }} - {{ $manifest->hub->name ?? 'Hub' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">Handling Agency:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $manifest->agency->name ?? 'Central Hub Facility' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">Clearance Mode:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $manifest->hub->mode_type ?? 'Hybrid DDP/DDU' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Last Mile Courier:</span>
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $manifest->shipments->first()->shipment->last_mile_carrier_name ?? 'Local Courier Handover' }}</span>
                </div>
            </div>
        </div>

        <!-- Dispatch Telemetry & Agency Pre-alert -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i class="fas fa-paper-plane text-emerald-500"></i> Agency Transmission Telemetry
            </h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">Email Status:</span>
                    @if($manifest->agency_emails_sent_at)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                            <i class="fas fa-check-circle mr-1 text-[9px]"></i> Dispatched
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                            Pending Dispatch
                        </span>
                    @endif
                </div>
                <div class="py-1 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500 block mb-1">Pre-defined Agency Recipients:</span>
                    <div class="flex flex-wrap gap-1">
                        @php
                            $recipients = $manifest->agency_emails_sent_to ?? ($manifest->agency ? $manifest->agency->getAllNotificationEmails() : []);
                        @endphp
                        @forelse($recipients as $email)
                            <span class="font-mono text-[10px] bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700">
                                {{ $email }}
                            </span>
                        @empty
                            <span class="text-slate-400 italic text-[11px]">No emails defined</span>
                        @endforelse
                    </div>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Sent Timestamp:</span>
                    <span class="font-mono text-slate-700 dark:text-slate-300">
                        {{ $manifest->agency_emails_sent_at ? \Carbon\Carbon::parse($manifest->agency_emails_sent_at)->format('M d, Y H:i') : 'Not sent yet' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Packets List & Arrival Status -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden space-y-3">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-boxes text-indigo-500"></i> Manifest Packets & Arrival Telemetry ({{ $manifest->shipments->count() }} pkts)
                </h3>
                <p class="text-xs text-slate-500">
                    Real-time status of each manifested consignment, arrival notices, and non-arrival remarks.
                </p>
            </div>
            <div class="text-xs font-mono font-bold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 px-3 py-1.5 rounded-xl">
                Gross: {{ number_format($manifest->total_weight, 2) }} kg
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">#</th>
                        <th class="px-5 py-3.5">Tracking / HAWB</th>
                        <th class="px-5 py-3.5">Shipper & Consignee</th>
                        <th class="px-5 py-3.5">Destination</th>
                        <th class="px-5 py-3.5">Weight (kg)</th>
                        <th class="px-5 py-3.5">Arrival Notice Status</th>
                        <th class="px-5 py-3.5">Telemetry & Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @foreach($manifest->shipments as $index => $item)
                        @php $s = $item->shipment; @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-400">{{ $index + 1 }}</td>
                            <td class="px-5 py-3.5 font-mono">
                                <a href="{{ route('tracking.public', ['tracking_number' => $s->tracking_number]) }}" target="_blank" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                    {{ $s->tracking_number }}
                                    <i class="fas fa-external-link-alt text-[9px]"></i>
                                </a>
                                @if($s->hawb_number)
                                    <div class="text-[10px] text-slate-400">HAWB: {{ $s->hawb_number }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $s->receiver_name }}</div>
                                <div class="text-[10px] text-slate-400">From: {{ $s->sender_name }} ({{ $s->sender_city ?? 'Nepal' }})</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $s->receiver_city ?? '' }}, {{ $s->receiver_country ?? '' }}</span>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $s->receiver_postal_code ?? '' }}</div>
                            </td>
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-900 dark:text-white">
                                {{ number_format($item->weight ?? $s->actual_weight ?? 1.0, 2) }} kg
                            </td>
                            <td class="px-5 py-3.5">
                                @if($item->arrival_status === 'arrived')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        <i class="fas fa-check-circle mr-1 text-[9px]"></i> Arrived at Hub
                                    </span>
                                @elseif($item->arrival_status === 'not_arrived')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                        <i class="fas fa-exclamation-triangle mr-1 text-[9px]"></i> Non-Arrival / Short
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        In Transit
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-[11px]">
                                @if($item->arrival_status === 'arrived')
                                    <div class="text-slate-700 dark:text-slate-300">
                                        <i class="fas fa-clock text-slate-400 mr-1"></i> {{ $item->arrived_at ? \Carbon\Carbon::parse($item->arrived_at)->format('M d, Y H:i') : 'N/A' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        <i class="fas fa-map-marker-alt text-rose-500 mr-1"></i> {{ $item->arrived_location ?? 'Hub Gateway' }}
                                        @if($item->staff_name) &bull; Staff: {{ $item->staff_name }} @endif
                                    </div>
                                @elseif($item->arrival_status === 'not_arrived')
                                    <div class="text-rose-600 dark:text-rose-400 font-semibold">
                                        Remarks: {{ $item->non_arrival_remarks ?? 'Short-landed packet' }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Awaiting flight touch-down</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Dispatch to Agency Emails -->
<div id="emailAgencyModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-lg w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-paper-plane text-indigo-500"></i> Dispatch Pre-Alert to Agency
            </h3>
            <button onclick="document.getElementById('emailAgencyModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form action="{{ route('international.manifests.send-agency-email', $manifest->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Pre-Defined Agency Inboxes</label>
                <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-xl text-xs space-y-1 font-mono text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                    @forelse($recipients as $em)
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-emerald-500 text-xs"></i> {{ $em }}
                        </div>
                    @empty
                        <div class="text-amber-500 font-sans italic">No emails configured. Please update Agency settings.</div>
                    @endforelse
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Custom Operational Notes (Optional)</label>
                <textarea name="custom_notes" rows="3" placeholder="Please prepare import clearance for MAWB {{ $manifest->mawb_number }}. Consignee paperwork attached." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('emailAgencyModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-md">
                    Send Flight Pre-Alert
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
