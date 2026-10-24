<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NETPACK — Courier & logistics')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
</head>
<body class="np-system np-legacy-screen">
<a href="#main-content" class="np-skip">Skip to content</a>
<header class="np-public-header"><div class="np-container"><x-wordmark /><nav class="np-public-links" aria-label="Main navigation"><a href="{{ route('rates.inquiry') }}" class="np-desktop-link">Delivery rates</a><a href="{{ route('tracking.page') }}">Track shipment</a>@auth<a href="{{ route('dashboard') }}" class="np-button">Workspace ↗</a>@else<a href="{{ route('login') }}" class="np-button">Sign in ↗</a>@endauth</nav></div></header>
<main class="np-container" id="main-content" style="padding-top:40px;padding-bottom:55px">@yield('content')</main>
<footer class="np-public-footer"><div class="np-container"><div><x-wordmark /><p>&copy; {{ now()->year }} COURIER with NETPACK · Nepal</p></div><nav aria-label="Footer navigation"><a href="{{ route('tracking.page') }}">Tracking</a><a href="{{ route('rates.inquiry') }}">Rates</a><a href="{{ route('login') }}">Account</a></nav></div></footer>
<x-ai-copilot-widget />
@stack('scripts')
</body>
</html>
