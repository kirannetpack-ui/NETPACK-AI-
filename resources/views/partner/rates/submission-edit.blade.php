@extends('layouts.app')
@section('title', 'Edit Domestic Rate')
@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-5"><h1 class="text-2xl font-bold text-gray-900">Edit and Resubmit Rate</h1><p class="text-sm text-gray-500 mt-1">Changes return the rate to pending review and remove it from customer quotations until approved.</p></div>
    <form method="POST" action="{{ route('partner.rates.update', $rate) }}" class="bg-white rounded-xl shadow-sm border border-gray-100">
        @csrf @method('PUT')
        @include('partner.rates.submission-form')
        <div class="flex justify-end gap-3 border-t px-6 py-4"><a href="{{ route('partner.rates.index') }}" class="rounded-lg border px-4 py-2 text-sm font-semibold">Cancel</a><button class="rounded-lg bg-teal-600 px-5 py-2 text-sm font-semibold text-white">Resubmit for approval</button></div>
    </form>
</div>
@endsection
