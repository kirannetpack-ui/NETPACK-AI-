@extends('layouts.app')

@section('title', 'Add Transit Point')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Add Transit Point</h1>
            <p class="text-sm text-gray-500 mt-1">Create a new transit point for an overseas partner</p>
        </div>

        <div class="p-6">
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

        <form method="POST" action="{{ route($routePrefix.'.transit-points.store') }}">
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

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Overseas Partner *</label>
                        <select name="partner_id" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">Select Partner</option>
                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->name }} ({{ $partner->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('partner_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Transit Point Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                               placeholder="e.g., New York Hub">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Type *</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($types as $key => $label)
                                <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ old('type') == $key ? 'border-teal-500 bg-teal-50' : '' }}">
                                    <input type="radio" name="type" value="{{ $key }}" {{ old('type', 'transit') == $key ? 'checked' : '' }} 
                                           class="text-teal-600 focus:ring-teal-500">
                                    <span class="text-sm">
                                        <span class="font-medium">{{ $label }}</span>
                                        <span class="text-xs text-gray-500 block">
                                            {{ $key === 'hub' ? 'Main hub (Only 1 per partner)' : 'Intermediate transit point (Multiple allowed)' }}
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Location (City) *</label>
                        <input type="text" name="location" value="{{ old('location') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                               placeholder="e.g., New York">
                        @error('location')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Country *</label>
                        <input type="text" name="country" value="{{ old('country') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                               placeholder="e.g., USA">
                        @error('country')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_mandatory" value="1" {{ old('is_mandatory') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            <span class="text-sm font-medium">Mandatory Transit Point</span>
                            <span class="text-xs text-gray-500">(First transit point for the partner)</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-save mr-2"></i> Create Transit Point
                    </button>
                    <a href="{{ route('overseas.transit-points.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
