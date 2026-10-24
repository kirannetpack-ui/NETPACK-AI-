@extends('layouts.app')

@section('title', 'Delivery Reminders')

@section('content')
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50">
        <h1 class="text-xl font-semibold text-gray-800">Pending Reminders</h1>
    </div>
    
    <div class="p-6">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Order ID</th>
                        <th class="px-4 py-3 text-left">Service</th>
                        <th class="px-4 py-3 text-left">Reminder #</th>
                        <th class="px-4 py-3 text-left">Scheduled</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reminders as $reminder)
                    <tr class="border-t">
                        <td class="px-4 py-3">#{{ $reminder->pickup_request_id }}</td>
                        <td class="px-4 py-3">{{ ucfirst($reminder->service_tier) }}</td>
                        <td class="px-4 py-3">{{ $reminder->reminder_number }}</td>
                        <td class="px-4 py-3">{{ $reminder->scheduled_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">
                            @if($reminder->is_sent)
                                <span class="text-green-600">✓ Sent</span>
                            @else
                                <span class="text-yellow-600">⏳ Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.orders.show', $reminder->pickup_request_id) }}" class="text-teal-600">View Order</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection