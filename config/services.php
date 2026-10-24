<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'project' => env('OPENAI_PROJECT_ID'),
        'text_model' => env('OPENAI_TEXT_MODEL', 'gpt-5.6-terra'),
        'realtime_model' => env('OPENAI_REALTIME_MODEL', 'gpt-realtime-2.1'),
    ],

    'khalti' => [
        'live' => env('KHALTI_LIVE', false),
        'public_key' => env('KHALTI_PUBLIC_KEY'),
        'secret_key' => env('KHALTI_SECRET_KEY'),
    ],

    'esewa' => [
        'live' => env('ESEWA_LIVE', false),
        'merchant_code' => env('ESEWA_MERCHANT_CODE'),
        'secret_key' => env('ESEWA_SECRET_KEY'),
    ],

    'serpapi' => [
        'key' => env('SERPAPI_KEY'),
    ],

    // Enable a carrier only after NETPACK has an approved commercial/API
    // agreement. Each adapter must use the CarrierRemoteAreaProvider contract;
    // scraping public carrier pages is intentionally not supported.
    'carrier_remote_area' => [
        'ups' => ['enabled' => env('UPS_REMOTE_AREA_ENABLED', false)],
        'fedex' => ['enabled' => env('FEDEX_REMOTE_AREA_ENABLED', false)],
        'dhl' => ['enabled' => env('DHL_REMOTE_AREA_ENABLED', false)],
        'aramex' => ['enabled' => env('ARAMEX_REMOTE_AREA_ENABLED', false)],
        'dpd' => ['enabled' => env('DPD_REMOTE_AREA_ENABLED', false)],
        'team_global' => ['enabled' => env('TEAM_GLOBAL_REMOTE_AREA_ENABLED', false)],
    ],

];
