@extends('layouts.app')

@section('title', 'Verification Requests')

@section('content')
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50">
        <h1 class="text-xl font-semibold text-gray-800">Pending Verifications</h1>
    </div>
    
    <div class="p-6">
        <div class="grid gap-4">
            @foreach($pendingUsers as $user)
            <div class="border rounded-lg p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $user->email }} | {{ ucfirst($user->user_type) }}</p>
                        <p class="text-xs text-gray-400">Submitted: {{ $user->submitted_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.verification.show', $user) }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg text-sm">
                            Review Documents
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection