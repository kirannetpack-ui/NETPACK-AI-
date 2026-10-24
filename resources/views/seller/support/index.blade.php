@extends('layouts.seller')

@section('title', 'Support')
@section('page-title', 'Support Center')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">🛟 Support Center</h1>
                <p class="text-sm text-gray-500 mt-1">Get help with your orders, payments, and more</p>
            </div>
            <a href="{{ route('seller.support.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-plus mr-2"></i> New Ticket
            </a>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total Tickets</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Open</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['open'] ?? 0 }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">In Progress</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['in_progress'] ?? 0 }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Resolved</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['resolved'] ?? 0 }}</p>
                </div>
            </div>

            <!-- Search -->
            <form method="GET" class="flex flex-wrap gap-3 mb-6">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search tickets..." 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <select name="status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">All Status</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </div>
                @if(request('search') || request('status'))
                    <div>
                        <a href="{{ route('seller.support') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                            <i class="fas fa-undo mr-2"></i> Reset
                        </a>
                    </div>
                @endif
            </form>

            <!-- Tickets List -->
            @if($tickets->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Ticket #</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Subject</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Category</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Priority</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-mono text-sm">#{{ $ticket->id }}</td>
                                    <td class="py-3 px-4">{{ Str::limit($ticket->subject, 40) }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($ticket->category ?? 'General') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $ticket->status === 'open' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($ticket->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                               ($ticket->status === 'resolved' ? 'bg-green-100 text-green-800' : 
                                               'bg-gray-100 text-gray-800')) }}">
                                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $ticket->priority === 'high' ? 'bg-red-100 text-red-800' : 
                                               ($ticket->priority === 'medium' ? 'bg-orange-100 text-orange-800' : 
                                               'bg-green-100 text-green-800') }}">
                                            {{ ucfirst($ticket->priority ?? 'Normal') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm">{{ $ticket->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex gap-2">
                                            <a href="{{ route('seller.support.show', $ticket->id) }}" 
                                               class="text-blue-600 hover:text-blue-800" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($ticket->status !== 'resolved' && $ticket->status !== 'closed')
                                                <a href="{{ route('seller.support.show', $ticket->id) }}" 
                                                   class="text-teal-600 hover:text-teal-800" title="Reply">
                                                    <i class="fas fa-reply"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $tickets->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-ticket-alt text-5xl text-gray-300 mb-4 block"></i>
                    <h3 class="text-lg font-semibold text-gray-700">No Support Tickets</h3>
                    <p class="text-gray-500 mt-2">You haven't created any support tickets yet.</p>
                    <a href="{{ route('seller.support.create') }}" class="inline-block mt-4 bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-plus mr-2"></i> Create Support Ticket
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Help Section -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-question-circle text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-800">FAQ</h4>
                    <p class="text-sm text-gray-500">Common questions answered</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-phone text-green-600 text-xl"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-800">Call Support</h4>
                    <p class="text-sm text-gray-500">+977-9800000000</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-envelope text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-800">Email Support</h4>
                    <p class="text-sm text-gray-500">support@netpack.com</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection