<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>COURIER with NETPACK — Courier &amp; logistics from Nepal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <x-pwa-head />
</head>
<body class="np-system">
@if(config('deployment.mode') === 'public-preview')
<div style="padding:10px 20px;background:#f5eee7;color:#785337;text-align:center;font-size:12px">Website preview · Booking, tracking and account services will open after operational setup.</div>
@endif
<a href="#main-content" class="np-skip">Skip to content</a>
<div class="np-public-utility"><div class="np-container"><span>BASED IN NEPAL. CONNECTED TO YOUR DESTINATION.</span><span>Domestic / International / E-commerce</span></div></div>
<header class="np-public-header">
    <div class="np-container">
        <x-wordmark />
        <nav class="np-public-links" aria-label="Main navigation">
            <a href="#services" class="np-desktop-link">Our services</a>
            <a href="{{ route('rates.inquiry') }}" class="np-desktop-link">Get a quote</a>
            <a href="{{ route('tracking.page') }}">Track</a>
            @auth
                <a href="{{ route('dashboard') }}" class="np-button">Workspace <span aria-hidden="true">↗</span></a>
            @else
                <a href="{{ route('login') }}" class="np-button">Sign in <span aria-hidden="true">↗</span></a>
            @endauth
        </nav>
    </div>
</header>
<main id="main-content">
    <section class="np-hero">
        <div class="np-container">
            <div class="np-hero-grid">
                <div>
                    <p class="np-eyebrow">Your next delivery starts here</p>
                    <h1>From your door.<br>To <em>what’s next.</em></h1>
                    <p class="np-hero-copy">Across Nepal or across borders. Thoughtful courier services for the parcels you send, the business you run, and the people waiting at the other end.</p>
                    <div class="np-hero-actions">
                        <a href="{{ route('shipments.create') }}" class="np-button">Send a shipment <span aria-hidden="true">↗</span></a>
                        <a href="{{ route('rates.inquiry') }}" class="np-button np-button-secondary">Explore delivery rates</a>
                    </div>
                </div>
                <div class="np-route-panel" aria-label="NETPACK delivery service overview">
                    <div class="np-route-header"><span>One connected journey</span><span>NP / WORLD</span></div>
                    <div class="np-route-nodes">
                        <div class="np-route-node"><small>ORIGIN</small><strong>Nepal</strong><span>Your doorstep</span></div>
                        <div class="np-route-line" aria-hidden="true"></div>
                        <div class="np-route-node"><small>DESTINATION</small><strong>Beyond</strong><span>Their doorstep</span></div>
                    </div>
                    <div class="np-route-caption"><strong>Every handover matters.</strong><br>Pickup, hub processing, transport, and final delivery — follow your parcel through each recorded milestone.</div>
                    <div class="np-route-footer"><span>01 · Domestic</span><span>02 · International</span><span>03 · E-commerce</span></div>
                </div>
            </div>
            <section class="np-track-panel" aria-labelledby="track-heading">
                <div><h2 id="track-heading">Expecting a delivery?</h2><p>Check its latest recorded shipment update.</p></div>
                <form method="GET" action="{{ route('tracking.search') }}" class="np-track-form" role="search">
                    <label for="home-tracking" class="sr-only">Tracking number</label>
                    <input id="home-tracking" name="tracking" type="search" placeholder="Enter your tracking number" required autocomplete="off">
                    <button type="submit" class="np-button">Track shipment <span aria-hidden="true">→</span></button>
                </form>
            </section>
        </div>
    </section>
    <section id="services" class="np-public-section">
        <div class="np-container">
            <div class="np-section-intro"><div><p class="np-eyebrow">Three services. One delivery partner.</p><h2>Built around the way<br>you need to send.</h2></div><p>A single place to plan your shipment, manage its documentation and keep delivery operations in view.</p></div>
            <div class="np-service-grid">
                <article class="np-public-service"><span class="np-service-no">01 / WITHIN NEPAL</span><h3>Domestic delivery</h3><p>For local deliveries and inter-district parcels, with pickup and partner handovers coordinated through one network.</p><ul><li>Local and regional shipments</li><li>Pickup and delivery coordination</li><li>Recorded shipment milestones</li></ul><a href="{{ route('shipments.create') }}">Plan a domestic shipment <span aria-hidden="true">↗</span></a></article>
                <article class="np-public-service"><span class="np-service-no">02 / ACROSS BORDERS</span><h3>International courier</h3><p>Send from Nepal to overseas destinations with route-based pricing and support for shipment documentation.</p><ul><li>Destination and weight-based rates</li><li>Air cargo and courier services</li><li>Waybills and carrier tracking</li></ul><a href="{{ route('rates.inquiry') }}">Explore international rates <span aria-hidden="true">↗</span></a></article>
                <article class="np-public-service"><span class="np-service-no">03 / FOR YOUR BUSINESS</span><h3>E-commerce logistics</h3><p>A dedicated merchant workspace to book customer orders, coordinate deliveries and review cash collections.</p><ul><li>Merchant shipment management</li><li>Delivery and return workflows</li><li>COD records and reconciliation</li></ul><a href="{{ route('register') }}">Create a business account <span aria-hidden="true">↗</span></a></article>
            </div>
        </div>
    </section>
    <section class="np-public-section alt">
        <div class="np-container">
            <div class="np-section-intro"><div><p class="np-eyebrow">A clear path from A to B</p><h2>Less uncertainty.<br>More visibility.</h2></div><a href="{{ route('register') }}" class="np-button np-button-secondary">Get started <span aria-hidden="true">↗</span></a></div>
            <div class="np-steps">
                <article><strong>01 / PREPARE</strong><h3>Choose your service</h3><p>Review the route, parcel weight and available rates before you book.</p></article>
                <article><strong>02 / BOOK</strong><h3>Share the details</h3><p>Add the sender, recipient and shipment information in your workspace.</p></article>
                <article><strong>03 / MOVE</strong><h3>Coordinate pickup</h3><p>Follow the handover from collection through transport and delivery.</p></article>
                <article><strong>04 / FOLLOW</strong><h3>Stay informed</h3><p>Use your tracking number to view the latest recorded updates.</p></article>
            </div>
        </div>
    </section>
</main>
<footer class="np-public-footer"><div class="np-container"><div><x-wordmark /><p>&copy; {{ now()->year }} COURIER with NETPACK · Nepal</p></div><nav aria-label="Footer navigation"><a href="{{ route('tracking.page') }}">Tracking</a><a href="{{ route('rates.inquiry') }}">Rates</a><a href="{{ route('login') }}">Your account</a></nav></div></footer>
@if(config('deployment.mode') !== 'public-preview')
<x-ai-copilot-widget />
@endif
<x-pwa-install-banner />
</body>
</html>
