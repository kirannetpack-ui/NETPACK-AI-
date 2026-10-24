<?php

namespace App\Services;

use App\Models\GlobalTariffSetting;
use App\Models\InternationalRate;
use App\Models\InternationalZone;
use App\Models\OverseasHub;
use App\Models\PackagingMaterial;

class InternationalRateService
{
    /**
     * Default fallback packaging catalog if database table is unavailable.
     */
    public const DEFAULT_PACKAGING_CATALOG = [
        'none' => [
            'id' => 'none',
            'code' => 'none',
            'name' => 'No Packaging Required (Customer Pre-Packed)',
            'price' => 0.00,
            'description' => 'Shipper provides own IATA-compliant packaging.',
            'icon' => 'box',
        ],
        'document_envelope' => [
            'id' => 'document_envelope',
            'code' => 'document_envelope',
            'name' => 'Reinforced Document Envelope / Waterproof Pouch',
            'price' => 150.00,
            'description' => 'Security-sealed, moisture-resistant envelope for legal and commercial papers.',
            'icon' => 'envelope-open-text',
        ],
        'bubble_flyer' => [
            'id' => 'bubble_flyer',
            'code' => 'bubble_flyer',
            'name' => 'Tamper-Evident Bubble Flyer Bag',
            'price' => 200.00,
            'description' => 'Padded shock-absorbing poly flyer with tamper-evident seal.',
            'icon' => 'shield-halved',
        ],
        'small_box' => [
            'id' => 'small_box',
            'code' => 'small_box',
            'name' => 'Small Corrugated Cargo Box (Up to 3 KG)',
            'price' => 350.00,
            'description' => 'Double-ply export box with corner edge protectors.',
            'icon' => 'box-archive',
        ],
        'medium_box' => [
            'id' => 'medium_box',
            'code' => 'medium_box',
            'name' => 'Medium Air Cargo Box (3 KG to 10 KG)',
            'price' => 600.00,
            'description' => 'Heavy-duty 5-ply corrugated carton with internal bubble lining.',
            'icon' => 'boxes-stacked',
        ],
        'large_box' => [
            'id' => 'large_box',
            'code' => 'large_box',
            'name' => 'Heavy Duty Export Master Box (10 KG to 25 KG)',
            'price' => 1200.00,
            'description' => 'Export-grade reinforced double-wall carton with nylon strapping.',
            'icon' => 'dolly',
        ],
        'wooden_crate' => [
            'id' => 'wooden_crate',
            'code' => 'wooden_crate',
            'name' => 'Custom Wooden Crate / Palletized Protection',
            'price' => 3500.00,
            'description' => 'ISPM-15 heat-treated fumigated timber crating for high-value/fragile cargo.',
            'icon' => 'pallet',
        ],
    ];

    /**
     * Backward-compatible alias for PACKAGING_CATALOG.
     */
    public const PACKAGING_CATALOG = self::DEFAULT_PACKAGING_CATALOG;

    /**
     * Retrieve dynamic packaging materials catalog from the database.
     */
    public function getPackagingCatalog(): array
    {
        try {
            $materials = PackagingMaterial::active()->ordered()->get();
            if ($materials->isNotEmpty()) {
                $catalog = [];
                foreach ($materials as $m) {
                    $catalog[$m->code] = [
                        'id' => $m->code,
                        'code' => $m->code,
                        'name' => $m->name,
                        'price' => (float) $m->price,
                        'description' => $m->description ?? '',
                        'icon' => $m->icon ?? 'box',
                        'is_active' => (bool) $m->is_active,
                    ];
                }
                return $catalog;
            }
        } catch (\Throwable $e) {
            // Fallback to default catalog if table unmigrated
        }

        return self::DEFAULT_PACKAGING_CATALOG;
    }

    /**
     * Precision Chargeable Weight & Rounding Engine.
     *
     * Rules:
     * - Under 10kg:
     *   - Any fraction from 0.01 to 0.50 (fraction of 0.1) rounds to next 0.5 kg slab.
     *   - Any fraction > 0.50 (0.6 additional) rounds to the next whole integer.
     * - Above 10kg:
     *   - Any fraction above 0.0 (fraction of 0.1) rounds to the next integer kg.
     */
    public function calculateWeight(float $grossWeight, ?float $length = null, ?float $width = null, ?float $height = null): array
    {
        $grossWeight = max(0.1, round($grossWeight, 3));
        
        $volumetricWeight = 0.0;
        $isVolumetric = false;

        if ($length > 0 && $width > 0 && $height > 0) {
            // IATA standard volumetric formula: (L x W x H in cm) / 5000
            $volumetricWeight = round(($length * $width * $height) / 5000, 3);
            if ($volumetricWeight > $grossWeight) {
                $isVolumetric = true;
            }
        }

        $rawWeight = max($grossWeight, $volumetricWeight);

        // Apply Precision Air Freight Rounding Rules
        if ($rawWeight <= 10.0) {
            $whole = floor($rawWeight);
            $fraction = round($rawWeight - $whole, 4);

            if ($fraction <= 0.0001) {
                $chargeable = (float) max(0.5, $whole);
                $step = 'Exact Integer / 0.5 KG';
            } elseif ($fraction <= 0.50) {
                $chargeable = (float) ($whole + 0.5);
                $step = '0.5 KG Slab Step';
            } else {
                $chargeable = (float) ($whole + 1.0);
                $step = 'Next Integer Step (>0.5 fraction)';
            }
        } else {
            // Above 10 kg
            $whole = floor($rawWeight);
            $fraction = round($rawWeight - $whole, 4);

            if ($fraction <= 0.0001) {
                $chargeable = (float) $whole;
                $step = 'Exact Integer KG';
            } else {
                $chargeable = (float) ceil($rawWeight);
                $step = 'Next Whole KG Ceiling (>10KG Rule)';
            }
        }

        return [
            'gross_weight' => $grossWeight,
            'volumetric_weight' => $volumetricWeight,
            'raw_weight' => $rawWeight,
            'chargeable_weight' => $chargeable,
            'is_volumetric' => $isVolumetric,
            'rounding_step' => $step,
            'explanation' => $this->getExplanationText($grossWeight, $volumetricWeight, $chargeable, $isVolumetric),
        ];
    }

    private function getExplanationText(float $gross, float $vol, float $chargeable, bool $isVol): string
    {
        $basis = $isVol ? "Volumetric ({$vol} kg) exceeds Gross ({$gross} kg)" : "Gross Weight: {$gross} kg";
        if ($chargeable <= 10.0) {
            return "{$basis} → Chargeable: {$chargeable} kg (Applied 0.5kg air freight slab bracket)";
        }
        return "{$basis} → Chargeable: {$chargeable} kg (Applied per-kg round-up bracket above 10kg)";
    }

    /**
     * Calculate Domestic Feeder Linehaul Charge from Outside Kathmandu Valley to Kathmandu Hub.
     *
     * Determined by active Domestic Partner tariff rates or provincial distance grids.
     */
    public function calculateDomesticFeederCharge(
        float $weight,
        string $pickupLocationType = 'inside_ktm',
        ?string $pickupCity = null,
        ?int $partnerId = null,
        string $serviceType = 'standard'
    ): array {
        if ($pickupLocationType !== 'outside_ktm' || empty($pickupCity) || strcasecmp(trim($pickupCity), 'Kathmandu') === 0 || strcasecmp(trim($pickupCity), 'Kathmandu Valley') === 0) {
            return [
                'is_applicable' => false,
                'pickup_location_type' => 'inside_ktm',
                'pickup_city' => 'Kathmandu Valley',
                'destination_city' => 'Kathmandu (TIA Central Cargo Gateway)',
                'additional_charge' => 0.0,
                'total_feeder_charge' => 0.0,
                'partner_name' => 'Kathmandu Central Gateway (Direct Drop / Local Courier)',
                'notice' => 'Direct drop or local courier at Kathmandu TIA Cargo Terminal (No inter-district linehaul surcharge).',
            ];
        }

        $pickupCity = trim($pickupCity);

        // 1. Check for active DomesticRate from database matching origin and destination Kathmandu
        try {
            $rateQuery = \App\Models\DomesticRate::active()
                ->where(function ($q) use ($pickupCity) {
                    $q->where('origin_city', 'LIKE', "%{$pickupCity}%")
                      ->orWhereHas('originZone', function ($zq) use ($pickupCity) {
                          $zq->where('zone_name', 'LIKE', "%{$pickupCity}%")
                             ->orWhereJsonContains('districts', $pickupCity);
                      });
                })
                ->where(function ($q) {
                    $q->where('destination_city', 'LIKE', '%Kathmandu%')
                      ->orWhereHas('destinationZone', function ($zq) {
                          $zq->where('zone_name', 'LIKE', '%Kathmandu%');
                      });
                });

            if ($partnerId) {
                $rateQuery->where('partner_id', $partnerId);
            }

            $domesticRate = $rateQuery->first();

            if ($domesticRate) {
                $calc = $domesticRate->calculateRate($weight);
                $partner = $domesticRate->partner;
                $partnerName = $partner ? ($partner->company_name ?? $partner->name) : 'Authorized Domestic Logistics Partner';

                return [
                    'is_applicable' => true,
                    'pickup_location_type' => 'outside_ktm',
                    'pickup_city' => $pickupCity,
                    'destination_city' => 'Kathmandu (TIA Air Cargo Gateway)',
                    'partner_id' => $domesticRate->partner_id,
                    'partner_name' => $partnerName,
                    'rate_id' => $domesticRate->id,
                    'service_name' => $domesticRate->service_name ?? 'Domestic Inter-District Linehaul',
                    'base_rate' => (float) $calc['base_rate'],
                    'per_kg_rate' => (float) ($calc['breakdown']['per_kg_rate'] ?? $domesticRate->per_kg_rate),
                    'weight_charge' => (float) $calc['weight_charge'],
                    'logistical_charge' => (float) $calc['logistical_charge'],
                    'additional_charge' => (float) $calc['additional_charge'],
                    'total_feeder_charge' => (float) $calc['total'],
                    'estimated_days' => $domesticRate->estimated_days ?? 2,
                    'source' => 'partner_rate_matrix',
                    'notice' => "Provided by {$partnerName} from {$pickupCity} to Kathmandu Central Air Cargo Gateway.",
                ];
            }
        } catch (\Throwable $e) {
            // Fallback to regional grid if query encounters issue
        }

        // 2. Fallback Standard Regional Linehaul Grid based on city/region
        $regionalGrid = [
            'pokhara' => ['base' => 150.0, 'per_kg' => 35.0, 'logistical' => 50.0, 'days' => 1, 'partner' => 'Pokhara Depot Logistics Partner (Gandaki)'],
            'chitwan' => ['base' => 140.0, 'per_kg' => 30.0, 'logistical' => 40.0, 'days' => 1, 'partner' => 'Bharatpur Express Logistics Partner (Bagmati)'],
            'narayangarh' => ['base' => 140.0, 'per_kg' => 30.0, 'logistical' => 40.0, 'days' => 1, 'partner' => 'Bharatpur Express Logistics Partner (Bagmati)'],
            'bharatpur' => ['base' => 140.0, 'per_kg' => 30.0, 'logistical' => 40.0, 'days' => 1, 'partner' => 'Bharatpur Express Logistics Partner (Bagmati)'],
            'hetauda' => ['base' => 150.0, 'per_kg' => 30.0, 'logistical' => 40.0, 'days' => 1, 'partner' => 'Makwanpur Surface Cargo Partner'],
            'birgunj' => ['base' => 160.0, 'per_kg' => 35.0, 'logistical' => 50.0, 'days' => 1, 'partner' => 'Birgunj Border Gateway Logistics (Madhesh)'],
            'butwal' => ['base' => 170.0, 'per_kg' => 38.0, 'logistical' => 50.0, 'days' => 2, 'partner' => 'Butwal Inter-District Cargo Partner (Lumbini)'],
            'bhairahawa' => ['base' => 170.0, 'per_kg' => 38.0, 'logistical' => 50.0, 'days' => 2, 'partner' => 'Butwal Inter-District Cargo Partner (Lumbini)'],
            'biratnagar' => ['base' => 180.0, 'per_kg' => 40.0, 'logistical' => 60.0, 'days' => 2, 'partner' => 'Biratnagar Linehaul Hub Partner (Koshi)'],
            'itahari' => ['base' => 180.0, 'per_kg' => 40.0, 'logistical' => 60.0, 'days' => 2, 'partner' => 'Biratnagar Linehaul Hub Partner (Koshi)'],
            'dharan' => ['base' => 190.0, 'per_kg' => 42.0, 'logistical' => 60.0, 'days' => 2, 'partner' => 'Koshi Provincial Transport Partner'],
            'janakpur' => ['base' => 180.0, 'per_kg' => 40.0, 'logistical' => 50.0, 'days' => 2, 'partner' => 'Mithila Regional Express Partner'],
            'nepalgunj' => ['base' => 220.0, 'per_kg' => 45.0, 'logistical' => 70.0, 'days' => 2, 'partner' => 'Western Nepal Freight Hub Partner (Lumbini)'],
            'surkhet' => ['base' => 260.0, 'per_kg' => 55.0, 'logistical' => 75.0, 'days' => 3, 'partner' => 'Karnali Regional Depot Partner (Karnali)'],
            'dhangadhi' => ['base' => 280.0, 'per_kg' => 60.0, 'logistical' => 80.0, 'days' => 3, 'partner' => 'Sudurpashchim Gateway Partner (Sudurpashchim)'],
        ];

        $lookupKey = strtolower(preg_replace('/[^a-zA-Z]/', '', $pickupCity));
        $matchedTier = null;
        foreach ($regionalGrid as $key => $tier) {
            if (str_contains($lookupKey, $key) || str_contains($key, $lookupKey)) {
                $matchedTier = $tier;
                break;
            }
        }

        if (!$matchedTier) {
            $matchedTier = ['base' => 200.0, 'per_kg' => 45.0, 'logistical' => 60.0, 'days' => 2, 'partner' => 'Nepal National Domestic Partner Network'];
        }

        $baseRate = (float) $matchedTier['base'];
        $perKg = (float) $matchedTier['per_kg'];
        $weightCharge = round($perKg * $weight, 2);
        $logistical = (float) $matchedTier['logistical'];
        $total = round($baseRate + $weightCharge + $logistical, 2);
        $partnerName = $matchedTier['partner'];

        return [
            'is_applicable' => true,
            'pickup_location_type' => 'outside_ktm',
            'pickup_city' => $pickupCity,
            'destination_city' => 'Kathmandu (TIA Air Cargo Gateway)',
            'partner_id' => null,
            'partner_name' => $partnerName,
            'rate_id' => null,
            'service_name' => 'Inter-District Feeder Linehaul',
            'base_rate' => $baseRate,
            'per_kg_rate' => $perKg,
            'weight_charge' => $weightCharge,
            'logistical_charge' => $logistical,
            'additional_charge' => 0.0,
            'total_feeder_charge' => $total,
            'estimated_days' => $matchedTier['days'],
            'source' => 'domestic_partner_grid',
            'notice' => "Provided by {$partnerName} for linehaul pickup from {$pickupCity} to Kathmandu Central Air Cargo Hub.",
        ];
    }

    /**
     * Generate Comprehensive Rate Quotation.
     */
    public function quote(
        string $country,
        float $grossWeight,
        ?float $length = null,
        ?float $width = null,
        ?float $height = null,
        string $packaging = 'none',
        ?string $serviceType = null,
        ?float $manualGodownRatePerKg = null,
        string $pickupLocationType = 'inside_ktm',
        ?string $pickupCity = null,
        ?int $domesticPartnerId = null
    ): array {
        $weightInfo = $this->calculateWeight($grossWeight, $length, $width, $height);
        $chargeableWeight = $weightInfo['chargeable_weight'];

        // Compute domestic feeder linehaul charge if picked up outside Kathmandu Valley
        $domesticFeeder = $this->calculateDomesticFeederCharge(
            $chargeableWeight,
            $pickupLocationType,
            $pickupCity,
            $domesticPartnerId
        );
        $domesticFeederCharge = $domesticFeeder['is_applicable'] ? (float)$domesticFeeder['total_feeder_charge'] : 0.0;

        // Get packaging details dynamically from active database catalog
        $packagingCatalog = $this->getPackagingCatalog();
        $packagingInfo = $packagingCatalog[$packaging] ?? ($packagingCatalog['none'] ?? [
            'id' => 'none',
            'code' => 'none',
            'name' => 'No Packaging Required (Customer Pre-Packed)',
            'price' => 0.00,
            'description' => 'Shipper provides own IATA-compliant packaging.',
            'icon' => 'box',
        ]);
        $packagingFee = (float) $packagingInfo['price'];

        // Dynamic Baseline Customs Clearance & Godown Terminal Handling charges fed by Super Admin
        $defaultCustoms = (float) GlobalTariffSetting::getValue('default_customs_clearance_charge', 500.00);
        $defaultGodown = (float) GlobalTariffSetting::getValue('default_godown_charge', 300.00);
        $customsNotice = (string) GlobalTariffSetting::getValue(
            'customs_charge_notice', 
            'Mandatory origin export customs inspection & clearance at Tribhuvan International Airport Cargo Customs desk.'
        );
        $godownNotice = (string) GlobalTariffSetting::getValue(
            'godown_charge_notice', 
            'TIA Cargo Terminal handling, weighing, security screening, and godown storage fee.'
        );

        // Query active rates for this country (country-wise or zone-wise)
        $ratesQuery = InternationalRate::active()->with(['hub', 'zone'])->forCountry($country);

        if ($serviceType) {
            $ratesQuery->where('service_type', $serviceType);
        }

        $rates = $ratesQuery->get();

        // Fallback: If no specific rate exists for this country, check for general/all-world rates
        if ($rates->isEmpty()) {
            $rates = InternationalRate::active()
                ->with(['hub', 'zone'])
                ->where(function ($q) {
                    $q->where('country', 'Rest of World')
                      ->orWhere('country', 'Worldwide')
                      ->orWhereNull('country');
                })
                ->get();
        }

        $quotes = [];

        foreach ($rates as $rate) {
            $baseFreight = $rate->getFreightForWeight($chargeableWeight);
            if ($baseFreight === null || $baseFreight <= 0) {
                continue;
            }

            // Dynamically apply rate-specific charge, or fall back to Super Admin baseline feed
            $customsClearance = ($rate->customs_clearance_charge !== null && (float)$rate->customs_clearance_charge > 0)
                ? (float) $rate->customs_clearance_charge
                : $defaultCustoms;

            // Godown / terminal handling is priced per kilo multiplied by chargeable weight
            // Manually entered rate (from calculator or rate matrix) takes strict precedence
            if ($manualGodownRatePerKg !== null && $manualGodownRatePerKg >= 0) {
                $godownRatePerKg = (float) $manualGodownRatePerKg;
            } elseif ($rate->godown_charge !== null) {
                $godownRatePerKg = (float) $rate->godown_charge;
            } else {
                $godownRatePerKg = $defaultGodown;
            }

            $godownCharge = round($chargeableWeight * $godownRatePerKg, 2);

            $fuelPercent = (float) $rate->fuel_surcharge_percent;
            $fuelSurcharge = $fuelPercent > 0 ? round(($baseFreight * $fuelPercent) / 100, 2) : 0.0;
            $docFee = (float) $rate->doc_fee;

            // Total includes base freight + customs + godown + packaging + fuel + doc + domestic feeder linehaul
            $total = round($baseFreight + $customsClearance + $godownCharge + $packagingFee + $fuelSurcharge + $docFee + $domesticFeederCharge, 2);

            $hubName = $rate->hub ? $rate->hub->hub_name : 'Direct Express Air (Nepal Origin)';
            $hubCode = $rate->hub ? $rate->hub->hub_code : 'KTM-DIR';
            $modeType = $rate->hub ? $rate->hub->mode_type : 'Direct Carrier Priority';

            $quotes[] = [
                'rate_id' => $rate->id,
                'service_type' => $rate->service_type,
                'service_label' => $rate->service_type === 'express' ? '⚡ Priority Express (3–4 Days)' : '🌐 Economy Air Cargo (6–8 Days)',
                'rate_scope' => $rate->rate_type === 'zone' ? ($rate->zone->name ?? 'Regional Zone') : 'Country Direct',
                'hub_id' => $rate->hub_id,
                'hub_name' => $hubName,
                'hub_code' => $hubCode,
                'mode_type' => $modeType,
                'transit_days' => "{$rate->transit_days_min}–{$rate->transit_days_max} Working Days",
                'itemized' => [
                    'base_freight' => $baseFreight,
                    'customs_clearance' => $customsClearance,
                    'customs_notice' => $customsNotice,
                    'godown_rate_per_kg' => $godownRatePerKg,
                    'godown_charge' => $godownCharge,
                    'godown_notice' => $godownNotice,
                    'packaging_fee' => $packagingFee,
                    'fuel_surcharge' => $fuelSurcharge,
                    'doc_fee' => $docFee,
                    'domestic_feeder_charge' => $domesticFeederCharge,
                    'domestic_feeder' => $domesticFeeder,
                    'total_cost' => $total,
                ],
                'packaging_selected' => $packagingInfo,
            ];
        }

        // Sort quotes so Express comes first or lowest price first
        usort($quotes, function ($a, $b) {
            if ($a['service_type'] === 'express' && $b['service_type'] !== 'express') return -1;
            if ($a['service_type'] !== 'express' && $b['service_type'] === 'express') return 1;
            return $a['itemized']['total_cost'] <=> $b['itemized']['total_cost'];
        });

        return [
            'country' => $country,
            'weight_info' => $weightInfo,
            'packaging' => $packagingInfo,
            'domestic_feeder' => $domesticFeeder,
            'global_tariff_inclusions' => [
                'customs_clearance' => $defaultCustoms,
                'customs_notice' => $customsNotice,
                'godown_charge' => ($manualGodownRatePerKg !== null && $manualGodownRatePerKg >= 0) ? (float)$manualGodownRatePerKg : $defaultGodown,
                'godown_rate_per_kg' => ($manualGodownRatePerKg !== null && $manualGodownRatePerKg >= 0) ? (float)$manualGodownRatePerKg : $defaultGodown,
                'godown_charge_unit' => 'per_kg',
                'godown_notice' => $godownNotice,
            ],
            'quotes_count' => count($quotes),
            'quotes' => $quotes,
        ];
    }
}
