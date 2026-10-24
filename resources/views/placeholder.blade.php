{{-- resources/views/placeholder.blade.php --}}
@extends('layouts.app')

@section('title', $page)

@section('content')
<div class="bg-white rounded-2xl shadow-sm p-8 text-center">
    <i class="fas fa-construction text-5xl text-gray-400 mb-4"></i>
    <h1 class="text-2xl font-bold text-gray-700">{{ $page }}</h1>
    <p class="text-gray-500 mt-2">This feature is coming soon!</p>
</div>
@endsection