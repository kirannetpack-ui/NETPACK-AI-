@extends('layouts.app')

@section('title', 'Partner Charge Details')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-600">Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.partner-charges.index') }}" class="hover:text-teal-600">Partner Charges</a>
        <span class="mx-2">/</span>
        <span class="text-gray-700">Charge #{{ $charge->id }}</span>
    </nav>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-teal-600 to-teal-700 px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Charge #{{ $charge->id }}</h1>
                    <p class="text-white/80 text-sm mt-1">
                        {{ $charge->shipment_reference ?? 'N/A' }} | 
                        {{ $charge->partner->name ?? 'N/A' }}
                    </p>
                </div>
                <div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-{{ $charge->status_color }}-100 text-{{ $charge->status_color }}-800">
                        {{ $charge->status_label }}
                    </span>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Comparison Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Partner Charge</p>
                    <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($charge->total_charge, 2) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">System Calculated</p>
                    <p class="text-2xl font-bold text-green-600">
                        Rs. {{ number_format($charge->system_total_charge ?? 0, 2) }}
                    </p>
                </div>
                <div class="border rounded-lg p-4 {{ $charge->charge_difference > 0 ? 'bg-red-50' : 'bg-green-50' }}">
                    <p class="text-sm text-gray-500">Difference</p>
                    <p class="text-2xl font-bold {{ $charge->charge_difference > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $charge->charge_difference > 0 ? '+' : '' }}{{ number_format($charge->charge_difference ?? 0, 2) }}
                        ({{ number_format($charge->charge_percentage_difference ?? 0, 1) }}%)
                    </p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b mb-6">
                <nav class="flex gap-4">
                    <button class="px-4 py-2 border-b-2 border-teal-600 text-teal-600 font-medium" onclick="showTab('details')">
                        <i class="fas fa-info-circle mr-2"></i> Details
                    </button>
                    <button class="px-4 py-2 text-gray-500 hover:text-gray-700" onclick="showTab('breakdown')">
                        <i class="fas fa-list mr-2"></i> Breakdown
                    </button>
                    <button class="px-4 py-2 text-gray-500 hover:text-gray-700" onclick="showTab('system')">
                        <i class="fas fa-calculator mr-2"></i> System Calculation
                    </button>
                    <button class="px-4 py-2 text-gray-500 hover:text-gray-700" onclick="showTab('history')">
                        <i class="fas fa-history mr-2"></i> History
                    </button>
                    <button class="px-4 py-2 text-gray-500 hover:text-gray-700" onclick="showTab('documents')">
                        <i class="fas fa-file mr-2"></i> Documents
                    </button>
                </nav>
            </div>

            <!-- Tab: Details -->
            <div id="tab-details" class="tab-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Shipment Reference</p>
                        <p class="font-medium">{{ $charge->shipment_reference ?? 'N/A' }}</p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Service Type</p>
                        <p class="font-medium">{{ ucfirst($charge->service_type ?? 'N/A') }}</p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Weight</p>
                        <p class="font-medium">{{ $charge->weight_kg ?? 'N/A' }} kg</p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Distance</p>
                        <p class="font-medium">{{ $charge->distance_km ?? 'N/A' }} km</p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Submitted By</p>
                        <p class="font-medium">{{ $charge->submittedBy->name ?? 'N/A' }}</p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Submitted At</p>
                        <p class="font-medium">{{ $charge->submitted_at ? $charge->submitted_at->format('M d, Y H:i') : 'N/A' }}</p>
                    </div>
                    @if($charge->notes)
                    <div class="md:col-span-2 border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Notes</p>
                        <p class="font-medium">{{ $charge->notes }}</p>
                    </div>
                    @endif
                    @if($charge->dispute_reason)
                    <div class="md:col-span-2 border rounded-lg p-4 bg-red-50">
                        <p class="text-sm text-red-600 font-medium">Dispute Reason</p>
                        <p class="font-medium text-red-800">{{ $charge->dispute_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Tab: Breakdown -->
            <div id="tab-breakdown" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">Partner Charge Breakdown</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between border-b py-2">
                                <span class="text-gray-600">Base Charge</span>
                                <span class="font-medium">Rs. {{ number_format($charge->base_charge, 2) }}</span>
                            </div>
                            <div class="flex justify-between border-b py-2">
                                <span class="text-gray-600">Weight Charge</span>
                                <span class="font-medium">Rs. {{ number_format($charge->weight_charge, 2) }}</span>
                            </div>
                            <div class="flex justify-between border-b py-2">
                                <span class="text-gray-600">Distance Charge</span>
                                <span class="font-medium">Rs. {{ number_format($charge->distance_charge, 2) }}</span>
                            </div>
                            <div class="flex justify-between border-b py-2">
                                <span class="text-gray-600">Fuel Surcharge</span>
                                <span class="font-medium">Rs. {{ number_format($charge->fuel_surcharge, 2) }}</span>
                            </div>
                            <div class="flex justify-between border-b py-2">
                                <span class="text-gray-600">Handling Fee</span>
                                <span class="font-medium">Rs. {{ number_format($charge->handling_fee, 2) }}</span>
                            </div>
                            <div class="flex justify-between border-b py-2">
                                <span class="text-gray-600">Insurance Charge</span>
                                <span class="font-medium">Rs. {{ number_format($charge->insurance_charge, 2) }}</span>
                            </div>
                            <div class="flex justify-between border-b py-2">
                                <span class="text-gray-600">Customs Charge</span>
                                <span class="font-medium">Rs. {{ number_format($charge->customs_charge, 2) }}</span>
                            </div>
                            <div class="flex justify-between border-b py-2 font-bold">
                                <span class="text-gray-700">Total Charge</span>
                                <span class="text-red-600">Rs. {{ number_format($charge->total_charge, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">Additional Charges</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between border-b py-2">
                                <span class="text-gray-600">Additional Charges</span>
                                <span class="font-medium">Rs. {{ number_format($charge->additional_charges, 2) }}</span>
                            </div>
                            @if($charge->charge_breakdown)
                                @foreach($charge->charge_breakdown as $key => $value)
                                    <div class="flex justify-between border-b py-2">
                                        <span class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                        <span class="font-medium">Rs. {{ number_format($value, 2) }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: System Calculation -->
            <div id="tab-system" class="tab-content hidden">
                @if($systemCalculation)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-3">System Calculated Rates</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between border-b py-2">
                                    <span class="text-gray-600">Base Rate</span>
                                    <span class="font-medium">Rs. {{ number_format($systemCalculation['base_rate']['amount'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between border-b py-2">
                                    <span class="text-gray-600">Sub Charges</span>
                                    <span class="font-medium">Rs. {{ number_format($systemCalculation['sub_rates']['total'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between border-b py-2">
                                    <span class="text-gray-600">Margin</span>
                                    <span class="font-medium">Rs. {{ number_format($systemCalculation['margin']['amount'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between border-b py-2 font-bold">
                                    <span class="text-gray-700">System Total</span>
                                    <span class="text-green-600">Rs. {{ number_format($systemCalculation['total'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-3">Breakdown Details</h4>
                            <div class="space-y-2">
                                @if(isset($systemCalculation['breakdown']))
                                    @foreach($systemCalculation['breakdown'] as $key => $value)
                                        <div class="flex justify-between border-b py-2">
                                            <span class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                            <span class="font-medium">Rs. {{ number_format($value, 2) }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-calculator text-4xl block mb-2"></i>
                        System calculation not available for this charge.
                    </div>
                @endif
            </div>

            <!-- Tab: History -->
            <div id="tab-history" class="tab-content hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Action</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Performed By</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Notes</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-600">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($charge->history as $history)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ ucfirst($history->action) }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3">{{ $history->performedBy->name ?? 'N/A' }}</td>
                                    <td class="py-2 px-3">{{ $history->notes ?? '-' }}</td>
                                    <td class="py-2 px-3 text-sm">{{ $history->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-gray-500">No history found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Documents -->
            <div id="tab-documents" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($charge->invoice_file)
                        <div class="border rounded-lg p-4">
                            <p class="text-sm text-gray-500">Invoice File</p>
                            <a href="{{ asset('storage/' . $charge->invoice_file) }}" target="_blank" class="text-teal-600 hover:underline flex items-center gap-2 mt-1">
                                <i class="fas fa-file-pdf"></i>
                                View Invoice
                            </a>
                        </div>
                    @endif
                    @if($charge->supporting_document)
                        <div class="border rounded-lg p-4">
                            <p class="text-sm text-gray-500">Supporting Document</p>
                            <a href="{{ asset('storage/' . $charge->supporting_document) }}" target="_blank" class="text-teal-600 hover:underline flex items-center gap-2 mt-1">
                                <i class="fas fa-file-alt"></i>
                                View Document
                            </a>
                        </div>
                    @endif
                    @if(!$charge->invoice_file && !$charge->supporting_document)
                        <div class="md:col-span-2 text-center py-4 text-gray-500">
                            <i class="fas fa-file text-2xl block mb-2"></i>
                            No documents uploaded.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons (Admin only) -->
            @if(in_array($charge->status, ['pending', 'under_review']))
            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Admin Actions</h3>
                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('admin.partner-charges.update-status', $charge->id) }}" class="inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action" value="verify">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-check mr-2"></i> Verify
                        </button>
                    </form>

                    <button onclick="showDisputeForm()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-times mr-2"></i> Dispute
                    </button>

                    <button onclick="showAdjustForm()" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition">
                        <i class="fas fa-edit mr-2"></i> Adjust
                    </button>

                    <form method="POST" action="{{ route('admin.partner-charges.update-status', $charge->id) }}" class="inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-check-double mr-2"></i> Approve
                        </button>
                    </form>
                </div>

                <!-- Dispute Form -->
                <div id="dispute-form" class="hidden mt-4 p-4 bg-red-50 rounded-lg border border-red-200">
                    <h4 class="font-medium text-red-800 mb-2">Dispute Charge</h4>
                    <form method="POST" action="{{ route('admin.partner-charges.update-status', $charge->id) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action" value="dispute">
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">Dispute Reason *</label>
                            <textarea name="dispute_reason" rows="3" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" required placeholder="Please provide a reason for disputing this charge..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">Additional Notes</label>
                            <textarea name="notes" rows="2" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Any additional notes..."></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                                Submit Dispute
                            </button>
                            <button type="button" onclick="hideDisputeForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Adjust Form -->
                <div id="adjust-form" class="hidden mt-4 p-4 bg-orange-50 rounded-lg border border-orange-200">
                    <h4 class="font-medium text-orange-800 mb-2">Adjust Charge</h4>
                    <form method="POST" action="{{ route('admin.partner-charges.update-status', $charge->id) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action" value="adjust">
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">Adjusted Amount *</label>
                            <input type="number" name="adjusted_amount" step="0.01" min="0" value="{{ $charge->total_charge }}" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" required>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">Adjustment Notes</label>
                            <textarea name="notes" rows="2" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Reason for adjustment..."></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">
                                Submit Adjustment
                            </button>
                            <button type="button" onclick="hideAdjustForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function showTab(tabId) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    // Show selected tab
    document.getElementById('tab-' + tabId).classList.remove('hidden');
    // Update tab styles
    document.querySelectorAll('nav button').forEach(btn => {
        btn.classList.remove('border-b-2', 'border-teal-600', 'text-teal-600');
        btn.classList.add('text-gray-500');
    });
    // Find and highlight the clicked tab
    const buttons = document.querySelectorAll('nav button');
    const tabMap = ['details', 'breakdown', 'system', 'history', 'documents'];
    buttons.forEach((btn, index) => {
        if (tabMap[index] === tabId) {
            btn.classList.remove('text-gray-500');
            btn.classList.add('border-b-2', 'border-teal-600', 'text-teal-600');
        }
    });
}

function showDisputeForm() {
    document.getElementById('dispute-form').classList.remove('hidden');
}

function hideDisputeForm() {
    document.getElementById('dispute-form').classList.add('hidden');
}

function showAdjustForm() {
    document.getElementById('adjust-form').classList.remove('hidden');
}

function hideAdjustForm() {
    document.getElementById('adjust-form').classList.add('hidden');
}
</script>
@endsection