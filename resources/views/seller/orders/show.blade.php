@extends('layouts.seller')

@section('title', "Order #{$order->order_number}")
@section('page-title', "Order #{$order->order_number}")

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Order Record
                </span>
                <span class="font-mono text-xs text-slate-400">#{{ $order->order_number }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900">Order Dossier &amp; Fulfillment</h1>
            <p class="text-xs text-slate-500 mt-0.5">Placed on {{ $order->created_at?->format('M d, Y, h:i A') }}</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
                <i class="fas fa-print text-slate-500"></i>
                <span>Print Order</span>
            </button>
            <a href="{{ route('seller.orders') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                Back to Orders
            </a>
        </div>
    </div>

    <!-- Status & Financial Snapshot -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Fulfillment Status</span>
            <span class="inline-block mt-1 px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $order->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($order->status === 'cancelled' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                {{ ucfirst($order->status ?? 'pending') }}
            </span>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Payment Method</span>
            <p class="text-sm font-black text-slate-800 mt-1 uppercase font-mono">
                {{ $order->payment_method ?? 'Prepaid' }}
                @if($order->payment_method === 'cod')
                    <span class="text-[10px] text-amber-600 block lowercase font-sans font-semibold">cash on delivery</span>
                @endif
            </p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Total Amount</span>
            <p class="text-xl font-black text-emerald-700 mt-0.5 font-mono">
                Rs. {{ number_format((float)($order->total_amount ?? 0), 2) }}
            </p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">COD Collectable</span>
            <p class="text-xl font-black text-amber-600 mt-0.5 font-mono">
                Rs. {{ number_format((float)($order->cod_amount ?? 0), 2) }}
            </p>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Customer & Delivery -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs space-y-3">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fas fa-user-circle text-teal-600"></i> Customer &amp; Delivery Destination
            </h3>
            <div class="space-y-1.5 text-xs text-slate-600">
                <p><span class="text-slate-400">Customer Name:</span> <strong class="text-slate-900">{{ $order->customer_name }}</strong></p>
                <p><span class="text-slate-400">Contact Phone:</span> <strong class="text-slate-900 font-mono">{{ $order->customer_phone }}</strong></p>
                @if($order->customer_email)
                    <p><span class="text-slate-400">Email:</span> <span class="text-slate-800">{{ $order->customer_email }}</span></p>
                @endif
                <p><span class="text-slate-400">Delivery Address:</span> <strong class="text-slate-900">{{ $order->shipping_address ?? 'Not specified' }}</strong></p>
                @if($order->special_instructions)
                    <p class="p-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-[11px] mt-2">
                        <i class="fas fa-note-sticky text-amber-500 mr-1"></i> {{ $order->special_instructions }}
                    </p>
                @endif
            </div>
        </div>

        <!-- Deliveries Breakdown -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs space-y-3">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fas fa-truck text-teal-600"></i> Deliveries &amp; Stops
            </h3>
            @if(is_array($order->delivery_data) && count($order->delivery_data) > 0)
                <div class="space-y-2">
                    @foreach($order->delivery_data as $idx => $deliv)
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs">
                            <span class="font-bold text-slate-800">Stop #{{ $idx + 1 }}: {{ $deliv['recipient_name'] ?? 'Recipient' }}</span>
                            <span class="text-[11px] text-slate-500 block">{{ $deliv['address'] ?? '' }} &bull; {{ $deliv['recipient_phone'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-500">Single delivery address registered: {{ $order->shipping_address }}</p>
            @endif
        </div>
    </div>

    <!-- Items Ordered -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">Order Items</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-3">#</th>
                        <th class="p-3">Item Name</th>
                        <th class="p-3 text-center">Quantity</th>
                        <th class="p-3 text-right">Unit Price</th>
                        <th class="p-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($order->items ?? [] as $i => $item)
                        <tr>
                            <td class="p-3 text-slate-400 font-mono">{{ $i + 1 }}</td>
                            <td class="p-3 font-semibold text-slate-800">{{ $item->product_name ?? 'Item' }}</td>
                            <td class="p-3 text-center font-mono font-bold">{{ $item->quantity ?? 1 }}</td>
                            <td class="p-3 text-right font-mono">Rs. {{ number_format((float)($item->price ?? 0), 2) }}</td>
                            <td class="p-3 text-right font-mono font-bold text-slate-900">Rs. {{ number_format((float)($item->subtotal ?? ($item->price * $item->quantity)), 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-slate-400">Order items details recorded in manifest.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
