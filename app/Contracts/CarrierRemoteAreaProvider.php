<?php

namespace App\Contracts;

/**
 * Official carrier integrations must implement this contract. No web scraping
 * is permitted: providers are activated only with a documented carrier API or
 * approved data feed.
 */
interface CarrierRemoteAreaProvider
{
    public function carrier(): string;

    /** @return array{is_remote: bool, amount?: float, currency?: string, conditions?: string, updated_at?: string}|null */
    public function lookup(string $country, string $postalCode, ?string $serviceCode = null): ?array;
}
