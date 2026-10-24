<?php

namespace App\Services;

use App\Models\DomesticPartnerAssignment;
use App\Models\DomesticRate;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DomesticPartnerRoutingService
{
    public function eligiblePartnerIds(int $zoneId, string $serviceType, string $legType): array
    {
        $assignmentIds = DomesticPartnerAssignment::available()
            ->where('zone_id', $zoneId)
            ->where('leg_type', $legType)
            ->where('service_type', $serviceType)
            ->orderByDesc('is_default')
            ->orderBy('priority')
            ->pluck('partner_id');

        $rateIds = DomesticRate::active()
            ->where('service_type', $serviceType)
            ->where('rate_type', $legType)
            ->where(function ($query) use ($zoneId, $legType) {
                if ($legType === 'delivery') {
                    $query->where('destination_zone_id', $zoneId);
                } else {
                    $query->where('origin_zone_id', $zoneId);
                }
            })
            ->pluck('partner_id');

        return $assignmentIds->merge($rateIds)->unique()->values()->all();
    }

    public function resolve(
        int $zoneId,
        string $serviceType,
        string $legType,
        ?int $preferredPartnerId = null
    ): ?array {
        $assignments = DomesticPartnerAssignment::available()
            ->with('partner')
            ->where('zone_id', $zoneId)
            ->where('leg_type', $legType)
            ->where('service_type', $serviceType)
            ->orderByDesc('is_default')
            ->orderBy('priority')
            ->get();

        if ($preferredPartnerId !== null) {
            $eligibleIds = $this->eligiblePartnerIds($zoneId, $serviceType, $legType);
            if (! in_array($preferredPartnerId, $eligibleIds, true)) {
                throw ValidationException::withMessages([
                    'preferred_partner_id' => 'The selected partner is not approved for this service and territory.',
                ]);
            }

            $partner = User::query()
                ->whereKey($preferredPartnerId)
                ->where('user_type', 'partner')
                ->where('verification_status', 'approved')
                ->firstOrFail();

            return [
                'partner' => $partner,
                'source' => 'partner_selected',
                'assignment' => $assignments->firstWhere('partner_id', $preferredPartnerId),
            ];
        }

        $assignment = $assignments->first(fn ($item) => $item->partner?->verification_status === 'approved');
        if ($assignment) {
            return [
                'partner' => $assignment->partner,
                'source' => $assignment->is_default ? 'default' : 'approved_alternative',
                'assignment' => $assignment,
            ];
        }

        $partnerId = $this->eligiblePartnerIds($zoneId, $serviceType, $legType)[0] ?? null;
        $partner = $partnerId ? User::whereKey($partnerId)->where('verification_status', 'approved')->first() : null;

        return $partner ? ['partner' => $partner, 'source' => 'rate_match', 'assignment' => null] : null;
    }
}
