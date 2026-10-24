<?php

return [
    'sequence_width' => 6,
    'hawb_sequence_width' => 3,

    'tracking_prefixes' => [
        'domestic' => 'NPD',
        'ecommerce' => 'NPE',
        'international' => 'NPI',
    ],

    'domestic_hawb_prefix' => 'DOMNP',
    'ecommerce_hawb_prefix' => 'ECNP',

    'international_hawb' => [
        'default_prefix' => 'INNP',
        'regions' => [
            'USNP' => ['US', 'USA', 'UNITED STATES', 'UNITED STATES OF AMERICA', 'CA', 'CAN', 'CANADA'],
            'UKNP' => ['GB', 'GBR', 'UK', 'UNITED KINGDOM', 'ENGLAND', 'SCOTLAND', 'WALES', 'NORTHERN IRELAND'],
            'AUNP' => ['AU', 'AUS', 'AUSTRALIA'],
            'EUNP' => [
                'EUROPE', 'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI',
                'FR', 'GR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT',
                'RO', 'SE', 'SI', 'SK', 'IS', 'LI', 'NO', 'CH',
                'AUSTRIA', 'BELGIUM', 'BULGARIA', 'CROATIA', 'CYPRUS', 'CZECHIA',
                'CZECH REPUBLIC', 'GERMANY', 'DENMARK', 'ESTONIA', 'SPAIN', 'FINLAND',
                'FRANCE', 'GREECE', 'HUNGARY', 'IRELAND', 'ITALY', 'LITHUANIA',
                'LUXEMBOURG', 'LATVIA', 'MALTA', 'NETHERLANDS', 'POLAND', 'PORTUGAL',
                'ROMANIA', 'SWEDEN', 'SLOVENIA', 'SLOVAKIA', 'ICELAND',
                'LIECHTENSTEIN', 'NORWAY', 'SWITZERLAND',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracking presentation
    |--------------------------------------------------------------------------
    |
    | These values keep service and milestone presentation consistent across
    | the public tracker, customer accounts, rider screens and admin tools.
    |
    */
    'services' => [
        'flash' => ['label' => 'Flash Delivery', 'icon' => 'fa-bolt', 'accent' => 'rose', 'promise' => '1–2 hours'],
        'same_day' => ['label' => 'Same Day', 'icon' => 'fa-clock', 'accent' => 'amber', 'promise' => '4–6 hours'],
        'standard' => ['label' => 'Standard Delivery', 'icon' => 'fa-truck', 'accent' => 'blue', 'promise' => '1–2 business days'],
        'himalayan' => ['label' => 'Himalayan Service', 'icon' => 'fa-mountain-sun', 'accent' => 'violet', 'promise' => '2–4 business days'],
        'express' => ['label' => 'International Express', 'icon' => 'fa-plane-departure', 'accent' => 'sky', 'promise' => 'Priority international'],
        'economy' => ['label' => 'International Economy', 'icon' => 'fa-earth-asia', 'accent' => 'indigo', 'promise' => 'Economical international'],
        'ecommerce' => ['label' => 'E-Commerce Delivery', 'icon' => 'fa-cart-shopping', 'accent' => 'teal', 'promise' => 'Live rider delivery'],
        'grocery' => ['label' => 'Grocery Box', 'icon' => 'fa-basket-shopping', 'accent' => 'emerald', 'promise' => 'Fresh local delivery'],
        'document' => ['label' => 'Document', 'icon' => 'fa-file-lines', 'accent' => 'slate', 'promise' => 'Secure document delivery'],
        'parcel' => ['label' => 'Parcel', 'icon' => 'fa-box', 'accent' => 'cyan', 'promise' => 'Tracked parcel delivery'],
        'default' => ['label' => 'COURIER with NETPACK Delivery', 'icon' => 'fa-box-open', 'accent' => 'teal', 'promise' => 'Tracked delivery'],
    ],

    'statuses' => [
        'pending' => ['label' => 'Order Placed', 'icon' => 'fa-receipt', 'tone' => 'slate', 'description' => 'Shipment information has been received.'],
        'assigned' => ['label' => 'Rider Assigned', 'icon' => 'fa-motorcycle', 'tone' => 'blue', 'description' => 'A rider has been assigned to this delivery.'],
        'confirmed' => ['label' => 'Booking Confirmed', 'icon' => 'fa-circle-check', 'tone' => 'blue', 'description' => 'The booking has been confirmed by NETPACK.'],
        'processing' => ['label' => 'Preparing Shipment', 'icon' => 'fa-boxes-packing', 'tone' => 'amber', 'description' => 'The shipment is being prepared for dispatch.'],
        'picked_up' => ['label' => 'Picked Up', 'icon' => 'fa-box', 'tone' => 'indigo', 'description' => 'The shipment has been collected from the sender.'],
        'in_transit' => ['label' => 'In Transit', 'icon' => 'fa-truck-fast', 'tone' => 'violet', 'description' => 'The shipment is moving through the delivery network.'],
        'customs_clearance' => ['label' => 'Customs Clearance', 'icon' => 'fa-passport', 'tone' => 'amber', 'description' => 'The international shipment is being processed by customs.'],
        'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => 'fa-motorcycle', 'tone' => 'teal', 'description' => 'The rider is completing the final delivery.'],
        'delivered' => ['label' => 'Delivered', 'icon' => 'fa-circle-check', 'tone' => 'emerald', 'description' => 'The shipment was delivered successfully.'],
        'failed_delivery' => ['label' => 'Delivery Attempted', 'icon' => 'fa-triangle-exclamation', 'tone' => 'rose', 'description' => 'The delivery could not be completed and may require action.'],
        'failed' => ['label' => 'Delivery Failed', 'icon' => 'fa-triangle-exclamation', 'tone' => 'rose', 'description' => 'The delivery could not be completed.'],
        'returned' => ['label' => 'Returning to Sender', 'icon' => 'fa-arrow-rotate-left', 'tone' => 'slate', 'description' => 'The shipment is being returned to the sender.'],
        'cancelled' => ['label' => 'Cancelled', 'icon' => 'fa-circle-xmark', 'tone' => 'rose', 'description' => 'This shipment has been cancelled.'],
    ],

    'live' => [
        'refresh_seconds' => (int) env('TRACKING_LIVE_REFRESH_SECONDS', 15),
        'stale_after_seconds' => (int) env('TRACKING_LIVE_STALE_AFTER_SECONDS', 120),
        'tile_url' => env('TRACKING_MAP_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'tile_attribution' => env('TRACKING_MAP_ATTRIBUTION', '&copy; OpenStreetMap contributors'),
    ],
];
