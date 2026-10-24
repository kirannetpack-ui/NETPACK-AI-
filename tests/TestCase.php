<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function carrierWebhookHeaders(array $payload, string $carrier): array
    {
        $secret = str_repeat('test-secret-', 4);
        config(["carrier_webhooks.secrets.{$carrier}" => $secret]);
        $timestamp = (string) time();

        return [
            'X-Netpack-Timestamp' => $timestamp,
            'X-Netpack-Signature' => hash_hmac('sha256', $timestamp.'.'.json_encode($payload), $secret),
        ];
    }
}
