@extends('layouts.app')

@section('title', 'Upload International Rate Sheet')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Upload International Rate Sheet</h1>
            <p class="text-sm text-gray-500 mt-1">Upload Excel or CSV file with overseas partner rates</p>
        </div>

        <div class="p-6">
            <!-- Simple Format Guide -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-800 mb-2">📋 Simple Format Guide</h3>
                <p class="text-sm text-blue-700 mb-2">
                    The system will automatically detect your columns. You can use any of these formats:
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <!-- Format 1: Simple -->
                    <div class="bg-white rounded-lg p-3 border border-blue-200">
                        <h4 class="font-medium text-gray-700 text-sm">Format 1: Simple</h4>
                        <p class="text-xs text-gray-500">Country, Weight, Rate</p>
                        <div class="mt-2 text-xs">
                            <div class="bg-gray-50 p-1 rounded">USA, 5, 25.00</div>
                            <div class="bg-gray-50 p-1 rounded mt-1">UK, 5, 22.00</div>
                        </div>
                    </div>
                    
                    <!-- Format 2: With Weight Range -->
                    <div class="bg-white rounded-lg p-3 border border-blue-200">
                        <h4 class="font-medium text-gray-700 text-sm">Format 2: Weight Range</h4>
                        <p class="text-xs text-gray-500">Country, Weight From, Weight To, Rate</p>
                        <div class="mt-2 text-xs">
                            <div class="bg-gray-50 p-1 rounded">USA, 0, 5, 25.00</div>
                            <div class="bg-gray-50 p-1 rounded mt-1">USA, 5, 10, 22.00</div>
                        </div>
                    </div>
                    
                    <!-- Format 3: Zone Based -->
                    <div class="bg-white rounded-lg p-3 border border-blue-200">
                        <h4 class="font-medium text-gray-700 text-sm">Format 3: Zone Based</h4>
                        <p class="text-xs text-gray-500">Zone/Group, Countries, Rate</p>
                        <div class="mt-2 text-xs">
                            <div class="bg-gray-50 p-1 rounded">Zone 1, USA,CAN,MEX, 25.00</div>
                            <div class="bg-gray-50 p-1 rounded mt-1">Zone 2, UK,FR,DE, 22.00</div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 p-2 bg-white rounded border border-blue-200">
                    <p class="text-xs text-gray-500">📝 <strong>Supported Headers:</strong> Country, Country Code, Weight, Weight From, Weight To, Rate, Rate/Kg, Zone, Group</p>
                </div>
            </div>

            <!-- Upload Form -->
            <form method="POST" action="{{ route('international.rates.parse') }}" enctype="multipart/form-data">
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
                                <option value="{{ $partner->id }}" {{ old('overseas_partner_id') == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->company_name ?? $partner->name }} ({{ $partner->email }})
                                </option>
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
                        <input type="date" name="effective_from" value="{{ old('effective_from', date('Y-m-d')) }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Effective To (Optional)</label>
                        <input type="date" name="effective_to" value="{{ old('effective_to') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Rate Sheet File *</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-teal-500 transition">
                            <input type="file" name="rate_file" required accept=".xlsx,.xls,.csv" 
                                   class="w-full" onchange="updateFileName(this)">
                            <div id="fileInfo">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 block mb-2"></i>
                                <span class="text-gray-500 text-sm">Choose file or drag and drop</span>
                                <p class="text-xs text-gray-400 mt-1">Supported formats: .xlsx, .xls, .csv</p>
                            </div>
                        </div>
                        @error('rate_file')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Column Mapping Preview -->
                <div id="columnPreview" class="hidden mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h4 class="font-medium text-gray-700 text-sm mb-2">📊 Detected Columns</h4>
                    <div id="columnMapping" class="text-sm text-gray-600"></div>
                </div>

                <div class="mt-6 flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-file-import mr-2"></i> Parse & Preview
                    </button>
                    <a href="{{ route('international.rates') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
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
            <i class="fas fa-file-excel text-3xl text-green-600 block mb-2"></i>
            <span class="text-gray-700 font-medium">${fileName}</span>
            <p class="text-xs text-gray-400">${fileSize} KB</p>
            <button type="button" onclick="clearFile(this)" class="text-red-500 text-xs hover:underline mt-1">Remove</button>
        `;
        
        // Show column preview
        showColumnPreview(input);
    }
}

function clearFile(btn) {
    const input = document.querySelector('input[name="rate_file"]');
    input.value = '';
    const fileInfo = document.getElementById('fileInfo');
    fileInfo.innerHTML = `
        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 block mb-2"></i>
        <span class="text-gray-500 text-sm">Choose file or drag and drop</span>
        <p class="text-xs text-gray-400 mt-1">Supported formats: .xlsx, .xls, .csv</p>
    `;
    document.getElementById('columnPreview').classList.add('hidden');
}

function showColumnPreview(input) {
    // This would require reading the file client-side
    // For now, show a sample preview
    const preview = document.getElementById('columnPreview');
    const mapping = document.getElementById('columnMapping');
    preview.classList.remove('hidden');
    mapping.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2">
            <div class="bg-white p-2 rounded border">
                <span class="font-medium">Detected:</span>
                <span class="text-teal-600">Country, Weight, Rate</span>
            </div>
            <div class="bg-white p-2 rounded border">
                <span class="font-medium">Sample:</span>
                <span class="text-gray-600">USA → 5 kg → $25.00</span>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">The system will auto-detect and map your columns</p>
    `;
}
</script>
@endsection