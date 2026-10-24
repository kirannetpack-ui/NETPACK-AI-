@extends('layouts.app')

@section('title', 'Format Settings | ' . $agency->name)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('international.agencies.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 mb-1">
                <i class="fas fa-arrow-left"></i> Back to Agencies
            </a>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Custom Manifest & Data Sheet Formats</h1>
            <p class="text-sm text-slate-500">
                Agency: <span class="font-bold text-slate-800 dark:text-slate-200">{{ $agency->name }}</span> ({{ $agency->code }}) &bull; Hub: {{ $agency->hub->name ?? 'None' }}
            </p>
        </div>
        <a href="{{ route('international.agencies.edit', $agency->id) }}" class="px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
            <i class="fas fa-edit mr-1"></i> Edit Agency Profile
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-lg"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-blue-500/10 border border-blue-500/20 rounded-2xl p-4 text-xs text-blue-900 dark:text-blue-300 space-y-1">
        <p class="font-bold flex items-center gap-1.5"><i class="fas fa-info-circle"></i> Per-Agency Customizable Export Schema</p>
        <p>
            Different destination hub agencies and customs clearance brokers require distinct column sequences and field headers on arrival manifests and data sheets. Customize the active columns and column headers below.
        </p>
    </div>

    <form action="{{ route('international.agencies.update-format-settings', $agency->id) }}" method="POST" class="space-y-6">
        @csrf

        @php
            $allAvailableFields = [
                'hawb_number' => 'HAWB Number',
                'tracking_number' => 'Tracking Number',
                'mawb_number' => 'MAWB Number',
                'sender_name' => 'Shipper / Sender Name',
                'sender_phone' => 'Shipper Contact Phone',
                'sender_email' => 'Shipper Email',
                'sender_address' => 'Shipper Address',
                'sender_city' => 'Origin City',
                'sender_country' => 'Origin Country',
                'receiver_name' => 'Consignee / Receiver Name',
                'receiver_phone' => 'Consignee Contact Phone',
                'receiver_email' => 'Consignee Email',
                'receiver_address' => 'Consignee Full Address',
                'receiver_city' => 'Destination City',
                'receiver_state' => 'Destination State / Province',
                'receiver_postal_code' => 'Destination Postal / Zip Code',
                'receiver_country' => 'Destination Country',
                'receiver_tax_id' => 'Consignee Tax ID / VAT / EORI',
                'actual_weight' => 'Actual Gross Weight (kg)',
                'chargeable_weight' => 'Chargeable / Volumetric Weight (kg)',
                'length' => 'Length (cm)',
                'width' => 'Width (cm)',
                'height' => 'Height (cm)',
                'package_type' => 'Packaging Type (Carton/Pallet)',
                'pieces' => 'Number of Pieces / Cartons',
                'description' => 'Detailed Goods Description',
                'declared_value' => 'Customs Declared Value',
                'currency' => 'Currency',
                'customs_mode' => 'Customs Mode (DDP / DDU)',
                'last_mile_carrier_name' => 'Last Mile Carrier Partner',
                'last_mile_tracking_number' => 'Last Mile Forwarding Tracking #',
                'agency_milestone' => 'Latest Lifecycle Milestone',
                'created_at' => 'Booking Date & Time',
            ];

            $currentManifestFields = $agency->getManifestFields();
            $currentDataSheetFields = $agency->getDataSheetFields();
        @endphp

        <!-- Section 1: Air Cargo Manifest Columns -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-file-invoice text-indigo-500"></i> Air Cargo Flight Manifest Columns
                    </h3>
                    <p class="text-xs text-slate-500">Columns printed on the airline/agency flight manifest document.</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950 text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                    {{ count($currentManifestFields) }} Selected
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($allAvailableFields as $key => $defaultLabel)
                    @php
                        $isSelected = array_key_exists($key, $currentManifestFields);
                        $customLabel = $currentManifestFields[$key] ?? $defaultLabel;
                    @endphp
                    <div class="p-3 rounded-xl border {{ $isSelected ? 'border-indigo-300 dark:border-indigo-800 bg-indigo-50/40 dark:bg-indigo-950/20' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30' }} flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="manifest_fields[{{ $key }}][enabled]" id="mf_{{ $key }}" value="1" {{ $isSelected ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                            <label for="mf_{{ $key }}" class="text-xs font-bold text-slate-800 dark:text-slate-200 cursor-pointer">
                                {{ $key }}
                            </label>
                        </div>
                        <input type="text" name="manifest_fields[{{ $key }}][label]" value="{{ $customLabel }}" placeholder="Header Label" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Section 2: Comprehensive Data Sheet Columns -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-file-excel text-emerald-500"></i> Comprehensive Inbound Data Sheet Format
                    </h3>
                    <p class="text-xs text-slate-500">Contains full shipment parameters exported for customs brokers, EDI feeds, and destination arrival staff.</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                    {{ count($currentDataSheetFields) }} Selected
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($allAvailableFields as $key => $defaultLabel)
                    @php
                        $isSelected = array_key_exists($key, $currentDataSheetFields);
                        $customLabel = $currentDataSheetFields[$key] ?? $defaultLabel;
                    @endphp
                    <div class="p-3 rounded-xl border {{ $isSelected ? 'border-emerald-300 dark:border-emerald-800 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30' }} flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="datasheet_fields[{{ $key }}][enabled]" id="ds_{{ $key }}" value="1" {{ $isSelected ? 'checked' : '' }} class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                            <label for="ds_{{ $key }}" class="text-xs font-bold text-slate-800 dark:text-slate-200 cursor-pointer">
                                {{ $key }}
                            </label>
                        </div>
                        <input type="text" name="datasheet_fields[{{ $key }}][label]" value="{{ $customLabel }}" placeholder="Export Column Header" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('international.agencies.index') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-600/30 transition">
                <i class="fas fa-save"></i> Save Agency Format Settings
            </button>
        </div>
    </form>
</div>
@endsection
