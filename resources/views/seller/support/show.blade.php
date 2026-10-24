@extends('layouts.seller')

@section('title', 'Support Ticket')
@section('page-title', 'Ticket Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Ticket #{{ $ticket->id }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $ticket->subject }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('seller.support') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
                @if($ticket->status !== 'resolved' && $ticket->status !== 'closed')
                    <button onclick="showReplyForm()" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-reply mr-2"></i> Reply
                    </button>
                @endif
            </div>
        </div>

        <div class="p-6">
            <!-- Ticket Info -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="border rounded-lg p-3">
                    <p class="text-xs text-gray-500">Status</p>
                    <p class="font-semibold">
                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                            {{ $ticket->status === 'open' ? 'bg-yellow-100 text-yellow-800' : 
                               ($ticket->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                               ($ticket->status === 'resolved' ? 'bg-green-100 text-green-800' : 
                               'bg-gray-100 text-gray-800')) }}">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </p>
                </div>
                <div class="border rounded-lg p-3">
                    <p class="text-xs text-gray-500">Category</p>
                    <p class="font-semibold">{{ ucfirst($ticket->category ?? 'General') }}</p>
                </div>
                <div class="border rounded-lg p-3">
                    <p class="text-xs text-gray-500">Priority</p>
                    <p class="font-semibold">
                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                            {{ $ticket->priority === 'high' ? 'bg-red-100 text-red-800' : 
                               ($ticket->priority === 'medium' ? 'bg-orange-100 text-orange-800' : 
                               'bg-green-100 text-green-800') }}">
                            {{ ucfirst($ticket->priority ?? 'Normal') }}
                        </span>
                    </p>
                </div>
                <div class="border rounded-lg p-3">
                    <p class="text-xs text-gray-500">Created</p>
                    <p class="font-semibold">{{ $ticket->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>

            <!-- Messages -->
            <div class="space-y-4 mb-6">
                @foreach($messages as $message)
                    <div class="flex {{ $message->user_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[70%] {{ $message->user_id === auth()->id() ? 'bg-teal-50 border-teal-200' : 'bg-gray-50 border-gray-200' }} border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-sm">{{ $message->user->name ?? 'Support' }}</span>
                                <span class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-gray-700">{{ $message->message }}</p>
                            @if($message->attachments)
                                <div class="mt-2">
                                    <a href="#" class="text-teal-600 hover:underline text-sm">
                                        <i class="fas fa-paperclip mr-1"></i> Attachment
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Reply Form -->
            <div id="replyForm" class="hidden border-t pt-4">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Reply to Ticket</h3>
                <form method="POST" action="{{ route('seller.support.reply', $ticket->id) }}">
                    @csrf
                    <div>
                        <textarea name="message" rows="4" required 
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                  placeholder="Type your reply here..."></textarea>
                    </div>
                    <div class="flex gap-3 mt-3">
                        <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-paper-plane mr-2"></i> Send Reply
                        </button>
                        <button type="button" onclick="hideReplyForm()" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Close Ticket -->
            @if($ticket->status !== 'resolved' && $ticket->status !== 'closed')
                <div class="border-t pt-4 mt-4">
                    <form method="POST" action="{{ route('seller.support.close', $ticket->id) }}" 
                          onsubmit="return confirm('Close this ticket?')">
                        @csrf
                        @method('POST')
                        <button type="submit" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition">
                            <i class="fas fa-check-circle mr-2"></i> Close Ticket
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function showReplyForm() {
    document.getElementById('replyForm').classList.remove('hidden');
}

function hideReplyForm() {
    document.getElementById('replyForm').classList.add('hidden');
}
</script>
@endsection