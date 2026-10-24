@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">System Settings</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="border rounded-lg p-4">
            <h3 class="font-semibold text-gray-800 mb-3">General Settings</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Site Name</label>
                    <input type="text" value="COURIER with NETPACK" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Site Email</label>
                    <input type="email" value="info@couriernetpack.com" class="w-full border rounded px-3 py-2">
                </div>
                <button class="bg-teal-600 text-white px-4 py-2 rounded">Save Settings</button>
            </div>
        </div>
        
        <div class="border rounded-lg p-4">
            <h3 class="font-semibold text-gray-800 mb-3">Shipping Settings</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Default Shipping Rate</label>
                    <input type="number" value="100" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Free Shipping Threshold</label>
                    <input type="number" value="5000" class="w-full border rounded px-3 py-2">
                </div>
                <button class="bg-teal-600 text-white px-4 py-2 rounded">Save Settings</button>
            </div>
        </div>
    </div>
</div>
@endsection
