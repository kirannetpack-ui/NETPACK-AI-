@extends('layouts.app')

@section('title', 'Rate Change Notifications')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Rate Change Notifications</h1>
            <p class="text-sm text-gray-500 mt-1">View all rate change notifications from partners</p>
        </div>

        <div class="p-6">
            @if($notifications->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Time</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Partner</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Zone</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Changes</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notifications as $notification)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 text-sm">{{ $notification->created_at->diffForHumans() }}</td>
                                    <td class="py-3 px-4">{{ $notification->metadata['partner_name'] ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">{{ $notification->metadata['zone_name'] ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm">
                                        @if(isset($notification->metadata['changes']))
                                            @foreach($notification->metadata['changes'] as $field => $change)
                                                <span class="text-xs inline-block bg-gray-100 px-2 py-1 rounded mr-1 mb-1">
                                                    {{ $field }}: {{ $change['old'] }} → {{ $change['new'] }}
                                                </span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending Review
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-bell text-4xl block mb-2"></i>
                    <p>No rate change notifications</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection