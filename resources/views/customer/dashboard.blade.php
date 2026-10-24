@extends('layouts.app')
@section('title', 'Your shipments — NETPACK')
@section('page-title', 'Customer overview')
@section('content')
<div class="space-y-6">
    <div class="np-page-intro np-dashboard-heading"><div><p class="np-eyebrow">Your delivery workspace</p><h1>Hello, {{ $user->name }}.</h1><p class="np-page-description">Your parcels, their progress, and your next delivery — all in one place.</p></div><a href="{{ route('shipments.create') }}" class="np-button">Send a shipment ↗</a></div>
    <div class="np-kpi-grid grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach([
            ['All shipments', $user->shipmentsAsCustomer->count(), 'Your shipment history'],
            ['In progress', $user->shipmentsAsCustomer->whereNotIn('status', ['delivered', 'cancelled', 'returned'])->count(), 'Awaiting collection or delivery'],
            ['Delivered', $user->shipmentsAsCustomer->where('status', 'delivered')->count(), 'Completed deliveries'],
        ] as $metric)
        <div class="np-section"><p class="text-xs text-slate-500">{{ $metric[0] }}</p><p class="text-3xl font-semibold mt-3">{{ $metric[1] }}</p><p class="text-xs text-slate-400 mt-3">{{ $metric[2] }}</p></div>
        @endforeach
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('tracking.page') }}" class="np-service-card"><i class="fas fa-location-dot" aria-hidden="true"></i><h3>Follow a delivery</h3><p>Look up the latest recorded updates using your tracking number.</p><span class="np-card-link">Track shipment ↗</span></a>
        <a href="{{ route('rates.inquiry') }}" class="np-service-card"><i class="fas fa-calculator" aria-hidden="true"></i><h3>Plan your next shipment</h3><p>Explore destination and weight-based delivery rates.</p><span class="np-card-link">Get a quote ↗</span></a>
        <a href="{{ route('grocery.box') }}" class="np-service-card"><i class="fas fa-box-open" aria-hidden="true"></i><h3>Shop &amp; send</h3><p>Browse the grocery service and prepare your next order.</p><span class="np-card-link">Start grocery shopping ↗</span></a>
    </div>
</div>
@endsection
