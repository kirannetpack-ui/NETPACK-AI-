@extends('layouts.public')

@section('title', 'Shipment Reference Not Found &middot; COURIER with NETPACK')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4">
    <div class="bg-white rounded-3xl p-8 sm:p-12 border border-slate-200 shadow-xl text-center space-y-6">
        <div class="h-20 w-20 rounded-3xl bg-amber-50 border border-amber-200/80 text-amber-600 mx-auto flex items-center justify-center text-3xl shadow-xs">
            <i class="fas fa-box-open"></i>
        </div>

        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                <i class="fas fa-triangle-exclamation"></i> Reference Not Located
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-heading">
                Consignment Number Not Found
            </h1>
            <p class="text-sm text-slate-600 max-w-md mx-auto leading-relaxed">
                We could not locate any active dispatch or waybill record matching:
            </p>
            <div class="inline-block bg-slate-100 border border-slate-200 px-4 py-2 rounded-xl text-sm font-mono font-bold text-slate-900 mt-1">
                {{ $trackingNumber ?? 'N/A' }}
            </div>
        </div>

        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200 text-left text-xs text-slate-600 space-y-2 max-w-md mx-auto">
            <p class="font-bold text-slate-800">Please check the following suggestions:</p>
            <ul class="list-disc list-inside space-y-1 text-slate-600">
                <li>Verify format: <strong>NPI-YYYY-XXXXXX-C</strong> (International) or <strong>NPD-YYYY-XXXXXX-C</strong> (Domestic).</li>
                <li>If searching by HAWB, ensure the region prefix is included (e.g. <strong>USNP-2026-001</strong>).</li>
                <li>Recent bookings may take 15–30 minutes to appear in regional hub telemetry.</li>
            </ul>
        </div>

        <!-- Quick Retry Form -->
        <form method="GET" action="{{ route('tracking.search') }}" class="max-w-md mx-auto pt-2">
            <div class="flex gap-2">
                <input type="text" name="tracking" placeholder="Enter tracking or HAWB number" required
                       class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono uppercase focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-500/20" />
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition shrink-0">
                    Search Again
                </button>
            </div>
        </form>

        <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-center gap-4 text-xs">
            <a href="{{ route('tracking.page') }}" class="text-slate-600 hover:text-slate-900 font-semibold flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i> Back to Tracking Hub
            </a>
            <span class="text-slate-300 hidden sm:inline">&middot;</span>
            <a href="tel:+97715970123" class="text-teal-700 font-bold hover:underline flex items-center gap-1.5">
                <i class="fas fa-headset"></i> Call Support: +977-1-5970123
            </a>
        </div>
    </div>
</div>
@endsection
