@extends('layouts.app')

@section('title', 'Settlements')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Settlement Management</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-green-50 p-4 rounded-lg">
            <p class="text-sm text-green-600">Total Settled</p>
            <p class="text-2xl font-bold">रू 0</p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg">
            <p class="text-sm text-yellow-600">Pending</p>
            <p class="text-2xl font-bold">रू 0</p>
        </div>
        <div class="bg-blue-50 p-4 rounded-lg">
            <p class="text-sm text-blue-600">Total Partners</p>
            <p class="text-2xl font-bold">{{ \App\Models\DomesticPartner::count() }}</p>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm">ID</th>
                    <th class="px-4 py-3 text-left text-sm">User</th>
                    <th class="px-4 py-3 text-left text-sm">Type</th>
                    <th class="px-4 py-3 text-left text-sm">Amount</th>
                    <th class="px-4 py-3 text-left text-sm">Status</th>
                    <th class="px-4 py-3 text-left text-sm">Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No settlements yet</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection