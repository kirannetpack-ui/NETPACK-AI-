<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Delivery handover · NETPACK</title>
    @vite(['resources/css/app.css'])
</head>
<body class="np-system netpack-workspace">
    <main class="min-h-screen flex items-center justify-center p-5">
        <section class="np-section max-w-md w-full">
            <p class="np-eyebrow">NETPACK / Delivery handover</p>
            <h1>Your delivery code</h1>
            <p class="np-page-description">Consignment {{ $assignment->master_awb }}</p>
            @if($assignment->status === 'completed')
                <p class="mt-6 text-sm text-teal-800">Delivery is complete. This code can no longer be used.</p>
            @elseif(in_array($assignment->status, ['failed', 'cancelled']))
                <p class="mt-6 text-sm text-slate-600">This delivery is closed. Contact the sender for the next steps.</p>
            @elseif($assignment->pickup_otp_verified_at)
                <p class="mt-6 text-3xl tracking-widest font-mono text-slate-800">{{ $assignment->delivery_otp }}</p>
                <p class="mt-5 text-sm text-slate-600">Share this code with the assigned rider only when the parcel is handed to you. Pay any agreed cash-on-delivery amount before confirming receipt.</p>
            @else
                <p class="mt-6 text-sm text-slate-600">Your parcel is awaiting pickup. The delivery code becomes available after pickup is confirmed.</p>
            @endif
        </section>
    </main>
</body>
</html>
