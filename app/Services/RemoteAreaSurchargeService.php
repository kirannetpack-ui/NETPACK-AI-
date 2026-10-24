<?php

namespace App\Services;

use App\Models\RemoteAreaSurcharge;

class RemoteAreaSurchargeService
{
    /**
     * Manual rates remain the production-safe fallback. Carrier adapters can
     * be registered when the company has official credentials and terms.
     */
    public function resolve(string $country, string $postalCode, ?int $partnerId = null, ?string $serviceCode = null): array
    {
        $surcharge = RemoteAreaSurcharge::checkSurcharge($country, $postalCode, $partnerId);
        if (!$surcharge) {
            return ['is_remote' => false, 'source' => 'manual', 'last_updated_at' => null];
        }

        return [
            'is_remote' => true,
            'surcharge' => $surcharge,
            'source' => $surcharge->source ?: 'manual',
            'last_updated_at' => optional($surcharge->source_updated_at ?? $surcharge->updated_at)->toIso8601String(),
            'currency' => $surcharge->currency ?: 'USD',
            'service_conditions' => $surcharge->service_conditions,
        ];
    }
}
