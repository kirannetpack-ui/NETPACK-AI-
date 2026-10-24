<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InternationalZone;
use App\Models\InternationalRate;
use App\Models\OverseasHub;
use App\Models\User;

class InternationalRatesSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('user_type', 'super_admin')->first() 
            ?? User::where('user_type', 'admin')->first();
        $adminId = $superAdmin ? $superAdmin->id : null;

        $dxbHub = OverseasHub::where('hub_code', 'DXB')->first();
        $lhrHub = OverseasHub::where('hub_code', 'LHR')->first();
        $sydHub = OverseasHub::where('hub_code', 'SYD')->first();
        $aklHub = OverseasHub::where('hub_code', 'AKL')->first();

        // 1. Seed International Zones
        $zonesData = [
            [
                'name' => 'Zone 1: Middle East & Gulf Hub',
                'code' => 'ZONE_ME_GULF',
                'hub_id' => $dxbHub?->id,
                'countries' => ['United Arab Emirates', 'Saudi Arabia', 'Qatar', 'Kuwait', 'Oman', 'Bahrain'],
                'description' => 'Direct connections via DXB Cargo Terminal with Gulf distribution linehaul.',
            ],
            [
                'name' => 'Zone 2: UK & Western Europe Gateway',
                'code' => 'ZONE_UK_EU',
                'hub_id' => $lhrHub?->id,
                'countries' => ['United Kingdom', 'Germany', 'France', 'Netherlands', 'Italy', 'Spain', 'Belgium', 'Switzerland', 'Austria'],
                'description' => 'Direct London Heathrow airfreight with continental EU DDP linehauls.',
            ],
            [
                'name' => 'Zone 3: North America Direct & Crossing',
                'code' => 'ZONE_NORTH_AMERICA',
                'hub_id' => $lhrHub?->id,
                'countries' => ['United States', 'Canada'],
                'description' => 'USA DDU & Canada Toronto DDP direct injection via Canpar / Obibox.',
            ],
            [
                'name' => 'Zone 4: Australia & New Zealand',
                'code' => 'ZONE_OCEANIA',
                'hub_id' => $sydHub?->id,
                'countries' => ['Australia', 'New Zealand'],
                'description' => 'Nationwide Australia distribution via AusPost and StarTrack, NZ via CourierPost.',
            ],
            [
                'name' => 'Zone 5: Asia-Pacific & East Asia',
                'code' => 'ZONE_ASIA_PACIFIC',
                'hub_id' => null,
                'countries' => ['Japan', 'South Korea', 'Singapore', 'Malaysia', 'Thailand', 'India', 'China', 'Hong Kong'],
                'description' => 'Direct carrier flights from Tribhuvan International Airport (Kathmandu).',
            ],
        ];

        $createdZones = [];
        foreach ($zonesData as $zd) {
            $createdZones[$zd['code']] = InternationalZone::updateOrCreate(
                ['code' => $zd['code']],
                array_merge($zd, ['is_active' => true])
            );
        }

        // Helper to generate realistic 0.5kg -> 10.0kg slabs (20 steps)
        $generateTiers = function (float $baseHalfKg, float $incrementPerHalfKg): array {
            $tiers = [];
            for ($w = 0.5; $w <= 10.0; $w += 0.5) {
                $key = number_format($w, 1, '.', '');
                $stepsAboveHalf = ($w - 0.5) / 0.5;
                $tiers[$key] = round($baseHalfKg + ($stepsAboveHalf * $incrementPerHalfKg), 2);
            }
            return $tiers;
        };

        // Standard Dynamic Ranges above 10kg
        $generatePerKgRanges = function (float $rate10To20, float $rate20To45, float $rate45To70, float $rate70To100, float $rate100Plus): array {
            return [
                ['min_weight' => 10.1, 'max_weight' => 20.0, 'rate_per_kg' => $rate10To20],
                ['min_weight' => 20.1, 'max_weight' => 45.0, 'rate_per_kg' => $rate20To45],
                ['min_weight' => 45.1, 'max_weight' => 70.0, 'rate_per_kg' => $rate45To70],
                ['min_weight' => 70.1, 'max_weight' => 100.0, 'rate_per_kg' => $rate70To100],
                ['min_weight' => 100.1, 'max_weight' => 9999.0, 'rate_per_kg' => $rate100Plus],
            ];
        };

        // 2. Seed Rates Matrix
        $ratesSeed = [
            // A. USA Express Direct
            [
                'country' => 'United States',
                'country_code' => 'US',
                'rate_type' => 'country',
                'hub_id' => null,
                'service_type' => 'express',
                'weight_tiers' => $generateTiers(3200, 480), // 0.5kg: 3200, 1.0kg: 3680 ... 10kg: 12320
                'per_kg_tiers' => $generatePerKgRanges(1150, 1050, 950, 850, 780),
                'customs_clearance_charge' => 500.00,
                'godown_charge' => 300.00,
                'fuel_surcharge_percent' => 5.0,
                'transit_days_min' => 3,
                'transit_days_max' => 5,
                'notes' => 'Direct Express Air Courier with DHL / FedEx / UPS direct linehaul.',
            ],
            // B. USA Economy Hub via DXB/LHR
            [
                'country' => 'United States',
                'country_code' => 'US',
                'rate_type' => 'country',
                'hub_id' => $lhrHub?->id,
                'service_type' => 'economy',
                'weight_tiers' => $generateTiers(2400, 380), // 0.5kg: 2400, 1.0kg: 2780 ... 10kg: 9620
                'per_kg_tiers' => $generatePerKgRanges(920, 840, 760, 680, 620),
                'customs_clearance_charge' => 500.00,
                'godown_charge' => 300.00,
                'fuel_surcharge_percent' => 0.0,
                'transit_days_min' => 6,
                'transit_days_max' => 9,
                'notes' => 'Consolidated Gateway Hub Cargo routing via London Heathrow to US destination points.',
            ],
            // C. United Kingdom Express
            [
                'country' => 'United Kingdom',
                'country_code' => 'GB',
                'rate_type' => 'country',
                'hub_id' => null,
                'service_type' => 'express',
                'weight_tiers' => $generateTiers(2800, 420),
                'per_kg_tiers' => $generatePerKgRanges(1020, 920, 840, 760, 700),
                'customs_clearance_charge' => 500.00,
                'godown_charge' => 300.00,
                'fuel_surcharge_percent' => 4.0,
                'transit_days_min' => 3,
                'transit_days_max' => 4,
                'notes' => 'Priority Express direct delivery across Great Britain.',
            ],
            // D. United Kingdom Economy (via LHR Gateway Hub)
            [
                'country' => 'United Kingdom',
                'country_code' => 'GB',
                'rate_type' => 'country',
                'hub_id' => $lhrHub?->id,
                'service_type' => 'economy',
                'weight_tiers' => $generateTiers(2100, 320),
                'per_kg_tiers' => $generatePerKgRanges(820, 740, 660, 590, 530),
                'customs_clearance_charge' => 450.00,
                'godown_charge' => 250.00,
                'fuel_surcharge_percent' => 0.0,
                'transit_days_min' => 5,
                'transit_days_max' => 7,
                'notes' => 'LHR Hub direct DDP customs clearance and Royal Mail/DPD last mile.',
            ],
            // E. Australia Express
            [
                'country' => 'Australia',
                'country_code' => 'AU',
                'rate_type' => 'country',
                'hub_id' => null,
                'service_type' => 'express',
                'weight_tiers' => $generateTiers(3000, 450),
                'per_kg_tiers' => $generatePerKgRanges(1100, 990, 890, 810, 740),
                'customs_clearance_charge' => 500.00,
                'godown_charge' => 300.00,
                'fuel_surcharge_percent' => 4.5,
                'transit_days_min' => 3,
                'transit_days_max' => 5,
                'notes' => 'Priority direct air carriage to Sydney, Melbourne, Brisbane.',
            ],
            // F. Australia Economy (via SYD Central Hub)
            [
                'country' => 'Australia',
                'country_code' => 'AU',
                'rate_type' => 'country',
                'hub_id' => $sydHub?->id,
                'service_type' => 'economy',
                'weight_tiers' => $generateTiers(2200, 340),
                'per_kg_tiers' => $generatePerKgRanges(860, 780, 700, 630, 560),
                'customs_clearance_charge' => 450.00,
                'godown_charge' => 250.00,
                'fuel_surcharge_percent' => 0.0,
                'transit_days_min' => 6,
                'transit_days_max' => 8,
                'notes' => 'Sydney Airport Cargo Village linehaul with AusPost and StarTrack.',
            ],
            // G. UAE Direct / Gulf Hub
            [
                'country' => 'United Arab Emirates',
                'country_code' => 'AE',
                'rate_type' => 'country',
                'hub_id' => $dxbHub?->id,
                'service_type' => 'express',
                'weight_tiers' => $generateTiers(1900, 280),
                'per_kg_tiers' => $generatePerKgRanges(720, 640, 580, 510, 460),
                'customs_clearance_charge' => 400.00,
                'godown_charge' => 200.00,
                'fuel_surcharge_percent' => 3.0,
                'transit_days_min' => 2,
                'transit_days_max' => 3,
                'notes' => 'Direct Dubai Mega Cargo Terminal delivery.',
            ],
            // H. Canada (Country-specific DDP via DXB/Toronto)
            [
                'country' => 'Canada',
                'country_code' => 'CA',
                'rate_type' => 'country',
                'hub_id' => $dxbHub?->id,
                'service_type' => 'economy',
                'weight_tiers' => $generateTiers(2500, 390),
                'per_kg_tiers' => $generatePerKgRanges(950, 860, 780, 700, 640),
                'customs_clearance_charge' => 500.00,
                'godown_charge' => 300.00,
                'fuel_surcharge_percent' => 0.0,
                'transit_days_min' => 6,
                'transit_days_max' => 9,
                'notes' => 'Canada DDP service direct to Toronto with Canpar/Obibox delivery.',
            ],
            // I. Zone 1: Gulf Regional Zone
            [
                'zone_id' => $createdZones['ZONE_ME_GULF']->id,
                'rate_type' => 'zone',
                'hub_id' => $dxbHub?->id,
                'service_type' => 'economy',
                'weight_tiers' => $generateTiers(2000, 300),
                'per_kg_tiers' => $generatePerKgRanges(750, 680, 600, 540, 480),
                'customs_clearance_charge' => 400.00,
                'godown_charge' => 250.00,
                'fuel_surcharge_percent' => 0.0,
                'transit_days_min' => 4,
                'transit_days_max' => 6,
                'notes' => 'Covers Saudi Arabia, Qatar, Kuwait, Oman, Bahrain via DXB Hub.',
            ],
            // J. Zone 2: Western Europe Regional Zone
            [
                'zone_id' => $createdZones['ZONE_UK_EU']->id,
                'rate_type' => 'zone',
                'hub_id' => $lhrHub?->id,
                'service_type' => 'economy',
                'weight_tiers' => $generateTiers(2300, 350),
                'per_kg_tiers' => $generatePerKgRanges(880, 800, 720, 640, 580),
                'customs_clearance_charge' => 500.00,
                'godown_charge' => 300.00,
                'fuel_surcharge_percent' => 0.0,
                'transit_days_min' => 5,
                'transit_days_max' => 8,
                'notes' => 'Continental Western Europe (Germany, France, Netherlands, Italy, Spain, etc.).',
            ],
        ];

        foreach ($ratesSeed as $rd) {
            $matchKey = ($rd['rate_type'] === 'country')
                ? ['rate_type' => 'country', 'country' => $rd['country'], 'service_type' => $rd['service_type']]
                : ['rate_type' => 'zone', 'zone_id' => $rd['zone_id'], 'service_type' => $rd['service_type']];

            InternationalRate::updateOrCreate(
                $matchKey,
                array_merge($rd, [
                    'created_by' => $adminId,
                    'is_active' => true,
                ])
            );
        }
    }
}
