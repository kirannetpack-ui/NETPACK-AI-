<?php

// NETPACK relay contract. Native carrier callbacks need an adapter that verifies
// the carrier's own signature before forwarding a signed NETPACK envelope.
return [
    'tolerance_seconds' => 300,
    'secrets' => [
        'dhl' => env('DHL_WEBHOOK_SECRET'),
        'fedex' => env('FEDEX_WEBHOOK_SECRET'),
        'ups' => env('UPS_WEBHOOK_SECRET'),
        'aramex' => env('ARAMEX_WEBHOOK_SECRET'),
        'royal_mail' => env('ROYAL_MAIL_WEBHOOK_SECRET'),
        'australia_post' => env('AUSTRALIA_POST_WEBHOOK_SECRET'),
        'dpd' => env('DPD_WEBHOOK_SECRET'),
    ],
];
