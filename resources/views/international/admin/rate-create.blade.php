@extends('layouts.app')

@section('title', 'Upload International Rate Sheet')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Upload International Rate Sheet</h1>
            <p class="text-sm text-gray-500 mt-1">Upload Excel, CSV, or JSON file with overseas partner rates</p>
        </div>

        <div class="p-6">
            <!-- File Format Requirements -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-800 mb-2">📋 File Format Requirements</h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• <strong>Excel (.xlsx, .xls)</strong> or <strong>CSV (.csv)</strong> or <strong>JSON (.json)</strong></li>
                    <li>• Column order: Country, City, Weight From, Weight To, Rate/Kg, Minimum Rate, Transit Time</li>
                    <li>• First row should be headers (will be skipped)</li>
                    <li>• Weight in kilograms (kg)</li>
                    <li>• Rates in USD ($)</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('international.rates.store') }}" enctype="multipart/form-data">
                @csrf

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Overseas Partner *</label>
                        <select name="overseas_partner_id" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">Select Partner</option>
                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}">{{ $partner->name }} ({{ $partner->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Service Type *</label>
                        <select name="service_type" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="express">Express</option>
                            <option value="standard">Standard</option>
                            <option value="economy">Economy</option>
                            <option value="priority">Priority</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Effective From *</label>
                        <input type="date" name="effective_from" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Effective To (Optional)</label>
                        <input type="date" name="effective_to" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Import Type *</label>
                        <select name="import_type" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="base_rates">Base Rates Only</option>
                            <option value="sub_rates">Sub Rates Only</option>
                            <option value="both">Both Base and Sub Rates</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Rate Sheet File *</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-teal-500 transition">
                            <input type="file" name="rate_file" required accept=".xlsx,.xls,.csv,.json" 
                                   class="w-full" onchange="this.parentElement.querySelector('span').textContent = this.files[0]?.name || 'Choose file...'">
                            <span class="text-gray-500 text-sm">Choose file or drag and drop</span>
                            <p class="text-xs text-gray-400 mt-1">Supported formats: .xlsx, .xls, .csv, .json</p>
                        </div>
                        @error('rate_file')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-upload mr-2"></i> Upload & Parse Rates
                    </button>
                    <a href="{{ route('international.rates') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection