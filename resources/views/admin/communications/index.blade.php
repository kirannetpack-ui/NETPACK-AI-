@extends('layouts.app')

@section('title', 'Communications & Delay Management Hub - COURIER with NETPACK')
@section('page-title', 'Communications & Delay Management')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 rounded-2xl p-6 text-white shadow-sm border border-teal-900/40">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        Operational Communications
                    </span>
                    <span class="text-xs text-slate-400">&bull; Nepal & Global Network</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight">Delivery Reminders & Delay Exception Center</h1>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl">
                    Broadcast automated delivery reminders, record transit delay reasons with expected resolution times, and audit dispatch logs across SMS, Email, and Push channels.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 text-xs font-semibold bg-white/10 hover:bg-white/20 text-white rounded-lg transition border border-white/10 flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Reminders</p>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total_reminders']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg">
                    <i class="fas fa-bell"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Pending Reminders</p>
                    <p class="text-2xl font-black text-amber-600 mt-1">{{ number_format($stats['pending_reminders']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Active Delays</p>
                    <p class="text-2xl font-black text-rose-600 mt-1">{{ number_format($stats['delayed_shipments']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Audit Logs Dispatched</p>
                    <p class="text-2xl font-black text-blue-600 mt-1">{{ number_format($stats['total_logs']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN GRID: Interactive Form + Active Exceptions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Interactive Form: Broadcast Shipment Delay Notice -->
        <div class="lg:col-span-1 bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-bullhorn text-amber-600"></i>
                        <span>Issue Delay & Exception Alert</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Send instant delay broadcast to Client & Partner</p>
                </div>
            </div>

            <form action="{{ route('admin.communications.send-delay') }}" method="POST" class="p-5 space-y-4">
                @csrf

                <!-- Tracking Number / Reference -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Tracking / HAWB / Pickup # *
                    </label>
                    <div class="relative">
                        <i class="fas fa-barcode absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <input type="text" 
                               name="tracking_number" 
                               placeholder="e.g. NP-DOM-98214 or HAWB12345" 
                               required
                               class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 font-mono font-medium">
                    </div>
                </div>

                <!-- Reason Code -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Delay Reason Code *
                    </label>
                    <select name="reason_code" required class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                        @foreach($reasons as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Expected Resolution Time -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Expected Delivery / Resolution Time
                    </label>
                    <input type="datetime-local" 
                           name="expected_resolution" 
                           class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>

                <!-- Channel -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Dispatch Channel *
                    </label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label class="flex items-center gap-2 p-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="channel" value="all" checked class="text-teal-600 focus:ring-teal-500">
                            <span class="font-medium text-slate-700">All (Email+SMS)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="channel" value="email" class="text-teal-600 focus:ring-teal-500">
                            <span class="font-medium text-slate-700">Email Only</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="channel" value="sms" class="text-teal-600 focus:ring-teal-500">
                            <span class="font-medium text-slate-700">SMS Only</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="channel" value="push" class="text-teal-600 focus:ring-teal-500">
                            <span class="font-medium text-slate-700">In-App Push</span>
                        </label>
                    </div>
                </div>

                <!-- Custom Details / Notes -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Additional Operational Note
                    </label>
                    <textarea name="custom_note" 
                              rows="3" 
                              placeholder="Describe checkpoint situation (e.g. Mugling road closed due to dry landslide, re-route via Hetauda expected tomorrow 11 AM)..."
                              class="w-full text-xs border border-slate-200 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-teal-500"></textarea>
                </div>

                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs uppercase tracking-wider rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                    <i class="fas fa-paper-plane"></i>
                    <span>Broadcast Delay Notice</span>
                </button>
            </form>
        </div>

        <!-- Active Delayed Consignments -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-clock-rotate-left text-rose-500"></i>
                        <span>Active Delayed Consignments</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Shipments marked with active delay flags and resolution timetables</p>
                </div>
                <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 rounded text-[11px] font-bold">
                    {{ $delayedPickups->count() }} Flagged
                </span>
            </div>

            <div class="p-0 overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200/80 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Consignment #</th>
                            <th class="py-3 px-4">Service Tier</th>
                            <th class="py-3 px-4">Delay Reason</th>
                            <th class="py-3 px-4">Reported At</th>
                            <th class="py-3 px-4">Expected Resolution</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($delayedPickups as $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                    {{ $item->pickup_number ?? ('PR-' . $item->id) }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700">
                                        {{ ucfirst($item->service_tier ?? 'standard') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 max-w-xs truncate font-medium text-rose-700" title="{{ $item->delay_reason }}">
                                    <i class="fas fa-exclamation-circle mr-1 text-rose-500"></i>
                                    {{ $item->delay_reason ?? 'Unspecified' }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-[11px]">
                                    {{ $item->delay_reported_at ? $item->delay_reported_at->format('M d, H:i') : 'N/A' }}
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-700 text-[11px]">
                                    {{ $item->expected_resolution_time ? \Carbon\Carbon::parse($item->expected_resolution_time)->format('M d, H:i') : 'TBD' }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('tracking.page') }}?tracking={{ $item->pickup_number ?? $item->id }}" 
                                       target="_blank"
                                       class="px-2.5 py-1 text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded transition inline-flex items-center gap-1">
                                        <i class="fas fa-radar"></i> Track
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    <i class="fas fa-circle-check text-2xl text-emerald-400 mb-1 block"></i>
                                    <span>No active delay exceptions logged. All shipments are moving smoothly!</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TABS / LISTS: Automated Reminders Schedule & Dispatched Log -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Automated Scheduled Delivery Reminders -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-business-time text-teal-600"></i>
                        <span>Scheduled Delivery Reminders</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Automated timer thresholds (Flash 2-4h, E-Comm 1h, Same Day, Standard)</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200/80 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Shipment #</th>
                            <th class="py-3 px-4">Tier</th>
                            <th class="py-3 px-4">Target Role</th>
                            <th class="py-3 px-4">Scheduled For</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reminders as $rem)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                    {{ $rem->pickupRequest->pickup_number ?? ('#'.$rem->pickup_request_id) }}
                                </td>
                                <td class="py-3 px-4 font-semibold uppercase text-[10px] text-slate-600">
                                    {{ $rem->service_tier }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ ucfirst($rem->reminder_type) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-600 text-[11px]">
                                    {{ $rem->scheduled_at ? $rem->scheduled_at->format('M d, H:i') : 'N/A' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($rem->is_sent)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Dispatched
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Queued
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">
                                    <span>No delivery reminders scheduled in the queue.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reminders->hasPages())
                <div class="p-3 border-t border-slate-100">
                    {{ $reminders->links() }}
                </div>
            @endif
        </div>

        <!-- Dispatched Notifications Audit Log -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-list-check text-blue-600"></i>
                        <span>Dispatched Audit Log</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Historical log of all sent reminders, alerts, and SMS/Email dispatches</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200/80 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Sent To</th>
                            <th class="py-3 px-4">Channel</th>
                            <th class="py-3 px-4">Message Snippet</th>
                            <th class="py-3 px-4">Dispatched At</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 font-medium text-slate-800 max-w-[120px] truncate" title="{{ $log->sent_to }}">
                                    {{ $log->sent_to }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $log->channel ?? 'email' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 max-w-[150px] truncate text-slate-600" title="{{ $log->message }}">
                                    {{ $log->message }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-[11px]">
                                    {{ $log->sent_at ? $log->sent_at->format('M d, H:i') : 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form action="{{ route('admin.communications.resend', $log->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="px-2 py-0.5 text-[10px] font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded transition"
                                                title="Resend this notification">
                                            <i class="fas fa-rotate-right mr-0.5"></i> Resend
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">
                                    <span>No notification audit logs found.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="p-3 border-t border-slate-100">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
