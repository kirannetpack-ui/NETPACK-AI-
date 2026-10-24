@extends('layouts.app')

@section('title', 'Upload Remote Area Surcharges')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Upload Remote Area Surcharges</h1>
            <p class="text-sm text-gray-500 mt-1">Upload Excel or CSV file with remote area surcharges</p>
        </div>

        <div class="p-6">
            <!-- Format Requirements -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-800 mb-2">📋 File Format Requirements</h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• <strong>Excel (.xlsx, .xls)</strong> or <strong>CSV (.csv)</strong></li>
                    <li>• Column order: Country, Zip Code Pattern, Area Name, Surcharge Amount, Surcharge Percentage</li>
                    <li>• First row should be headers (will be skipped)</li>
                </ul>
                
                <div class="mt-3 p-3 bg-white rounded border border-blue-200">
                    <p class="text-xs text-gray-500 mb-1">📝 Zip Code Pattern Examples:</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                        <div class="p-2 bg-gray-50 rounded">
                            <span class="font-medium">Exact Match:</span>
                            <span class="text-gray-600">10001</span>
                        </div>
                        <div class="p-2 bg-gray-50 rounded">
                            <span class="font-medium">Wildcard:</span>
                            <span class="text-gray-600">995* (matches 99501, 99502, etc.)</span>
                        </div>
                        <div class="p-2 bg-gray-50 rounded">
                            <span class="font-medium">Single Range:</span>
                            <span class="text-gray-600">10000-20000</span>
                        </div>
                        <div class="p-2 bg-gray-50 rounded">
                            <span class="font-medium">Multiple Ranges:</span>
                            <span class="text-gray-600">10000-20000, 30000-40000</span>
                        </div>
                        <div class="p-2 bg-gray-50 rounded">
                            <span class="font-medium">Prefix Match:</span>
                            <span class="text-gray-600">100 (matches 10001, 10002)</span>
                        </div>
                        <div class="p-2 bg-gray-50 rounded">
                            <span class="font-medium">Suffix Match:</span>
                            <span class="text-gray-600">01 (matches 10001, 20001)</span>
                        </div>
                    </div>
                </div>

                <div class="mt-3 p-3 bg-white rounded border border-blue-200">
                    <p class="text-xs text-gray-500 mb-1">📝 Sample Excel Format:</p>
                    <div class="overflow-x-auto">
                        <table class="text-xs">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-2 py-1 text-left">Country</th>
                                    <th class="px-2 py-1 text-left">Zip Code Pattern</th>
                                    <th class="px-2 py-1 text-left">Area Name</th>
                                    <th class="px-2 py-1 text-left">Surcharge Amount (USD)</th>
                                    <th class="px-2 py-1 text-left">Surcharge Percentage (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="px-2 py-1 border">USA</td>
                                    <td class="px-2 py-1 border">995*</td>
                                    <td class="px-2 py-1 border">Remote Alaska</td>
                                    <td class="px-2 py-1 border">50.00</td>
                                    <td class="px-2 py-1 border">15</td>
                                </tr>
                                <tr>
                                    <td class="px-2 py-1 border">USA</td>
                                    <td class="px-2 py-1 border">10000-20000</td>
                                    <td class="px-2 py-1 border">Remote NY Area</td>
                                    <td class="px-2 py-1 border">25.00</td>
                                    <td class="px-2 py-1 border">10</td>
                                </tr>
                                <tr>
                                    <td class="px-2 py-1 border">USA</td>
                                    <td class="px-2 py-1 border">30000-40000, 50000-60000</td>
                                    <td class="px-2 py-1 border">Remote Multiple</td>
                                    <td class="px-2 py-1 border">35.00</td>
                                    <td class="px-2 py-1 border">12</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <form method="POST" action="{{ route('international.surcharges.store') }}" enctype="multipart/form-data">
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
                        <select name="partner_id" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">Select Partner</option>
                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->company_name ?? $partner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Effective From</label>
                        <input type="date" name="effective_from" value="{{ old('effective_from', date('Y-m-d')) }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Surcharge File *</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-teal-500 transition">
                            <input type="file" name="surcharge_file" required accept=".xlsx,.xls,.csv" 
                                   class="w-full" onchange="updateFileName(this)">
                            <div id="fileInfo">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 block mb-2"></i>
                                <span class="text-gray-500 text-sm">Choose file or drag and drop</span>
                                <p class="text-xs text-gray-400 mt-1">Supported formats: .xlsx, .xls, .csv</p>
                            </div>
                        </div>
                        @error('surcharge_file')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-upload mr-2"></i> Upload Surcharges
                    </button>
                    <a href="{{ route('international.surcharges') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateFileName(input) {
    const fileInfo = document.getElementById('fileInfo');
    if (input.files && input.files[0]) {
        const fileName = input.files[0].name;
        const fileSize = (input.files[0].size / 1024).toFixed(1);
        fileInfo.innerHTML = `
            <i class="fas fa-file text-3xl text-teal-600 block mb-2"></i>
            <span class="text-gray-700 font-medium">${fileName}</span>
            <p class="text-xs text-gray-400">${fileSize} KB</p>
            <button type="button" onclick="clearFile(this)" class="text-red-500 text-xs hover:underline mt-1">Remove</button>
        `;
    }
}

function clearFile(btn) {
    const input = document.querySelector('input[name="surcharge_file"]');
    input.value = '';
    const fileInfo = document.getElementById('fileInfo');
    fileInfo.innerHTML = `
        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 block mb-2"></i>
        <span class="text-gray-500 text-sm">Choose file or drag and drop</span>
        <p class="text-xs text-gray-400 mt-1">Supported formats: .xlsx, .xls, .csv</p>
    `;
}
</script>
@endsection