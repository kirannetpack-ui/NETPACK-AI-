@extends('layouts.partner')

@section('title', 'Export Deliveries')
@section('page-title', 'Export Deliveries Report')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">📊 Export Deliveries Report</h1>
                <p class="text-sm text-gray-500 mt-1">Filter and export your delivery data to CSV</p>
            </div>
            <a href="{{ route('partner.deliveries.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Deliveries
            </a>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <form method="GET" action="{{ route('partner.deliveries.export') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Status</label>
                        <select name="status" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="picked_up">Picked Up</option>
                            <option value="in_transit">In Transit</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="failed_delivery">Failed Delivery</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Service Tier</label>
                        <select name="service_tier" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">All Services</option>
                            <option value="flash">⚡ Flash</option>
                            <option value="same_day">🕐 Same Day</option>
                            <option value="standard">🚚 Standard</option>
                            <option value="himalayan">🏔️ Himalayan</option>
                            <option value="ecommerce">🛒 E-commerce</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">From Date</label>
                        <input type="date" name="from_date" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">To Date</label>
                        <input type="date" name="to_date" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-file-export mr-2"></i> Export CSV
                    </button>
                    <button type="reset" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        <i class="fas fa-undo mr-2"></i> Reset Filters
                    </button>
                    <a href="{{ route('partner.deliveries.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </a>
                </div>
            </form>

            <!-- Export Information -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h4 class="text-sm font-semibold text-blue-700 mb-2">
                        <i class="fas fa-file-csv mr-2"></i> CSV Format
                    </h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>✓ Compatible with Excel</li>
                        <li>✓ UTF-8 encoding</li>
                        <li>✓ Auto-download</li>
                    </ul>
                </div>

                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <h4 class="text-sm font-semibold text-green-700 mb-2">
                        <i class="fas fa-info-circle mr-2"></i> Included Data
                    </h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>✓ Order details</li>
                        <li>✓ Customer information</li>
                        <li>✓ Tracking status</li>
                        <li>✓ Timestamps</li>
                    </ul>
                </div>

                <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                    <h4 class="text-sm font-semibold text-purple-700 mb-2">
                        <i class="fas fa-bolt mr-2"></i> Quick Export
                    </h4>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('partner.deliveries.export') }}?status=delivered" class="bg-green-600 text-white px-3 py-1 rounded-lg text-xs hover:bg-green-700 transition">
                            Delivered
                        </a>
                        <a href="{{ route('partner.deliveries.export') }}?status=pending" class="bg-yellow-600 text-white px-3 py-1 rounded-lg text-xs hover:bg-yellow-700 transition">
                            Pending
                        </a>
                        <a href="{{ route('partner.deliveries.export') }}?service_tier=flash" class="bg-red-600 text-white px-3 py-1 rounded-lg text-xs hover:bg-red-700 transition">
                            Flash
                        </a>
                        <a href="{{ route('partner.deliveries.export') }}?service_tier=standard" class="bg-blue-600 text-white px-3 py-1 rounded-lg text-xs hover:bg-blue-700 transition">
                            Standard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Delivery Count Preview -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-database mr-2"></i> Your Deliveries Summary
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-center">
                    <div class="p-2 bg-white rounded-lg border">
                        <p class="text-2xl font-bold text-gray-800">{{ \App\Models\PickupRequest::where('partner_id', auth()->id())->count() }}</p>
                        <p class="text-xs text-gray-500">Total</p>
                    </div>
                    <div class="p-2 bg-yellow-50 rounded-lg border border-yellow-200">
                        <p class="text-2xl font-bold text-yellow-600">{{ \App\Models\PickupRequest::where('partner_id', auth()->id())->where('status', 'pending')->count() }}</p>
                        <p class="text-xs text-yellow-600">Pending</p>
                    </div>
                    <div class="p-2 bg-blue-50 rounded-lg border border-blue-200">
                        <p class="text-2xl font-bold text-blue-600">{{ \App\Models\PickupRequest::where('partner_id', auth()->id())->whereIn('status', ['picked_up', 'in_transit'])->count() }}</p>
                        <p class="text-xs text-blue-600">In Transit</p>
                    </div>
                    <div class="p-2 bg-green-50 rounded-lg border border-green-200">
                        <p class="text-2xl font-bold text-green-600">{{ \App\Models\PickupRequest::where('partner_id', auth()->id())->where('status', 'delivered')->count() }}</p>
                        <p class="text-xs text-green-600">Delivered</p>
                    </div>
                    <div class="p-2 bg-red-50 rounded-lg border border-red-200">
                        <p class="text-2xl font-bold text-red-600">{{ \App\Models\PickupRequest::where('partner_id', auth()->id())->where('is_delayed', true)->count() }}</p>
                        <p class="text-xs text-red-600">Delayed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection