<?php

namespace App\Services\Tracking\Drivers;

use App\Services\Tracking\Contracts\CarrierTrackingDriverInterface;

/** Absence of carrier evidence must never create shipment events. */
class SmartAutonomousDriver implements CarrierTrackingDriverInterface
{
    public function getName(): string
    {
        return 'smart_autonomous';
    }

    public function supports(string $carrier, string $trackingNumber): bool
    {
        return true;
    }

    public function track(string $trackingNumber, array $options = []): array
    {
        return [
            'success' => false,
            'updated' => false,
            'source' => $this->getName(),
            'message' => 'Verified carrier tracking is unavailable. The last confirmed shipment status has been preserved. Check the carrier portal or retry later.',
        ];
    }
}
