<?php

namespace App\Services;

use App\Models\DeliveryZone;
use App\Models\DomesticRate;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DomesticRateQuoteService
{
    public function __construct(private readonly DomesticPartnerRoutingService $routing)
    {
    }

    public function quote(
        int $originZoneId,
        int $destinationZoneId,
        string $serviceType,
        float $weight,
        ?int $preferredPartnerId = null,
        bool $isCod = false
    ): array {
        $origin = DeliveryZone::active()->findOrFail($originZoneId);
        $destination = DeliveryZone::active()->findOrFail($destinationZoneId);

        $rates = DomesticRate::active()
            ->with('partner')
            ->where('origin_zone_id', $origin->id)
            ->where('destination_zone_id', $destination->id)
            ->where('service_type', $serviceType)
            ->byWeight($weight)
            ->get();

        if ($preferredPartnerId !== null) {
            $rates = $rates->where('partner_id', $preferredPartnerId)->values();
        }

        $doorToDoor = $this->bestRate($rates->where('rate_type', 'door_to_door'), $weight, $isCod, $origin->id, $serviceType, 'pickup', $preferredPartnerId);
        $composed = $this->composedPlan($rates, $weight, $isCod, $origin, $destination, $serviceType, $preferredPartnerId);

        $plans = collect([$doorToDoor, $composed])->filter();
        if ($plans->isEmpty()) {
            throw ValidationException::withMessages([
                'origin_zone_id' => 'No approved partner rate covers the selected origin, destination, service and weight. Please request an assisted quotation.',
            ]);
        }

        $plan = $plans->sortBy('customer_price')->first();

        return array_merge($plan, [
            'origin_zone' => $origin,
            'destination_zone' => $destination,
            'service_type' => $serviceType,
            'weight' => $weight,
            'quoted_at' => now()->toIso8601String(),
            'expires_at' => now()->addHours(24)->toIso8601String(),
        ]);
    }

    private function composedPlan(
        Collection $rates,
        float $weight,
        bool $isCod,
        DeliveryZone $origin,
        DeliveryZone $destination,
        string $serviceType,
        ?int $preferredPartnerId
    ): ?array {
        $legs = [];
        foreach (['pickup', 'logistics', 'delivery'] as $type) {
            $zoneId = $type === 'delivery' ? $destination->id : $origin->id;
            $leg = $this->bestRate($rates->where('rate_type', $type), $weight, $isCod, $zoneId, $serviceType, $type, $preferredPartnerId);
            if (! $leg) {
                return null;
            }
            $legs[] = $leg['legs'][0];
        }

        return $this->summarize($legs, 'composed');
    }

    private function bestRate(
        Collection $rates,
        float $weight,
        bool $isCod,
        int $assignmentZoneId,
        string $serviceType,
        string $legType,
        ?int $preferredPartnerId
    ): ?array {
        if ($rates->isEmpty()) {
            return null;
        }

        $routing = $this->routing->resolve($assignmentZoneId, $serviceType, $legType, $preferredPartnerId);
        if ($routing) {
            $partnerRate = $rates->firstWhere('partner_id', $routing['partner']->id);
            if ($partnerRate) {
                $rates = collect([$partnerRate]);
            }
        }

        $rated = $rates->map(function (DomesticRate $rate) use ($weight, $isCod, $routing) {
            $breakdown = $rate->calculateRate($weight);
            $partnerCost = (float) $breakdown['total'];
            if (! $isCod) {
                $partnerCost -= (float) $rate->cod_charge;
                $breakdown['cod_charge'] = 0;
                $breakdown['total'] = $partnerCost;
            }
            $price = $rate->customerPrice($partnerCost);

            return [
                'rate' => $rate,
                'partner_cost' => $price['partner_cost'],
                'markup_amount' => $price['markup_amount'],
                'customer_price' => $price['customer_price'],
                'breakdown' => $breakdown,
                'assignment_source' => $routing && $routing['partner']->id === $rate->partner_id
                    ? $routing['source']
                    : 'best_approved_rate',
            ];
        })->sortBy('customer_price')->first();

        return $this->summarize([$rated], 'single');
    }

    private function summarize(array $legs, string $planType): array
    {
        return [
            'plan_type' => $planType,
            'partner_cost' => round(collect($legs)->sum('partner_cost'), 2),
            'markup_amount' => round(collect($legs)->sum('markup_amount'), 2),
            'customer_price' => round(collect($legs)->sum('customer_price'), 2),
            'currency' => $legs[0]['rate']->currency ?? 'NPR',
            'estimated_hours' => collect($legs)->sum(fn ($leg) => (int) ($leg['rate']->estimated_hours ?: (($leg['rate']->estimated_days ?: 0) * 24))),
            'legs' => $legs,
        ];
    }
}
