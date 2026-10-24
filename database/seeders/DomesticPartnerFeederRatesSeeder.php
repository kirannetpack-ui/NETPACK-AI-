<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use App\Models\DomesticPartner;
use App\Models\DomesticRate;
use App\Models\User;
use Illuminate\Database\Seeder;

class DomesticPartnerFeederRatesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure KTM Central Partner User and DomesticPartner exist
        $ktmUser = User::firstOrCreate(
            ['email' => 'partner.ktm@netpack.com.np'],
            [
                'name' => 'Bikash Shrestha',
                'password' => bcrypt('Netpack!2026#Partner'),
                'user_type' => 'partner',
                'role' => 'partner',
                'phone' => '9851000000',
            ]
        );

        $ktmPartner = DomesticPartner::firstOrCreate(
            ['code' => 'KTM-HUB'],
            [
                'name' => 'Bikash Shrestha',
                'company_name' => 'Kathmandu Central Gateway Logistics',
                'email' => 'partner.ktm@netpack.com.np',
                'password' => bcrypt('Netpack!2026#Partner'),
                'phone' => '9851000000',
                'address' => 'Tribhuvan International Airport Cargo Complex',
                'city' => 'Kathmandu',
                'district' => 'Kathmandu',
                'province' => 'Bagmati',
                'service_type' => 'all',
                'margin_percentage' => 10.00,
                'is_active' => true,
                'kyc_verified' => true,
            ]
        );

        // 2. Ensure Regional Partners exist
        $regionalPartnersData = [
            [
                'code' => 'PKR-EXP',
                'name' => 'Gandaki Express Partner',
                'company_name' => 'Pokhara Depot Logistics P. Ltd',
                'email' => 'partner.pkr@netpack.com.np',
                'city' => 'Pokhara',
                'district' => 'Kaski',
                'province' => 'Gandaki',
            ],
            [
                'code' => 'BRT-EXP',
                'name' => 'Koshi Provincial Transport',
                'company_name' => 'Biratnagar Linehaul Hub',
                'email' => 'partner.brt@netpack.com.np',
                'city' => 'Biratnagar',
                'district' => 'Morang',
                'province' => 'Koshi',
            ],
            [
                'code' => 'BRG-EXP',
                'name' => 'Madhesh Commercial Transit',
                'company_name' => 'Birgunj Border Gateway Logistics',
                'email' => 'partner.brg@netpack.com.np',
                'city' => 'Birgunj',
                'district' => 'Parsa',
                'province' => 'Madhesh',
            ],
            [
                'code' => 'BTW-EXP',
                'name' => 'Lumbini Express Hub',
                'company_name' => 'Butwal Inter-District Cargo',
                'email' => 'partner.btw@netpack.com.np',
                'city' => 'Butwal',
                'district' => 'Rupandehi',
                'province' => 'Lumbini',
            ],
            [
                'code' => 'CTN-EXP',
                'name' => 'Chitwan Central Depot',
                'company_name' => 'Bharatpur Express Logistics',
                'email' => 'partner.ctn@netpack.com.np',
                'city' => 'Chitwan',
                'district' => 'Chitwan',
                'province' => 'Bagmati',
            ],
        ];

        $partnersByCity = ['Kathmandu' => $ktmPartner];
        $usersByCity = ['Kathmandu' => $ktmUser];

        foreach ($regionalPartnersData as $pData) {
            $user = User::firstOrCreate(
                ['email' => $pData['email']],
                [
                    'name' => $pData['name'],
                    'password' => bcrypt('Netpack!2026#Partner'),
                    'user_type' => 'partner',
                    'role' => 'partner',
                    'phone' => '9851000001',
                ]
            );

            $partner = DomesticPartner::firstOrCreate(
                ['code' => $pData['code']],
                [
                    'name' => $pData['name'],
                    'company_name' => $pData['company_name'],
                    'email' => $pData['email'],
                    'password' => bcrypt('Netpack!2026#Partner'),
                    'phone' => '9851000001',
                    'address' => "Main Depot, {$pData['city']}",
                    'city' => $pData['city'],
                    'district' => $pData['district'],
                    'province' => $pData['province'],
                    'service_type' => 'all',
                    'margin_percentage' => 10.00,
                    'is_active' => true,
                    'kyc_verified' => true,
                ]
            );
            $partnersByCity[$pData['city']] = $partner;
            $usersByCity[$pData['city']] = $user;
        }

        // 3. Ensure Delivery Zones exist
        $ktmZone = DeliveryZone::firstOrCreate(
            ['zone_code' => 'KTM-CENTRAL'],
            [
                'partner_id' => $ktmPartner->id,
                'partner_user_id' => $ktmUser->id,
                'zone_name' => 'Kathmandu Valley Central Hub',
                'zone_type' => 'urban',
                'districts' => ['Kathmandu', 'Lalitpur', 'Bhaktapur'],
                'is_active' => true,
            ]
        );

        $regionalZones = [
            'Pokhara' => ['code' => 'PKR-DEPOT', 'province' => 'Gandaki', 'districts' => ['Kaski', 'Tanahun']],
            'Biratnagar' => ['code' => 'BRT-DEPOT', 'province' => 'Koshi', 'districts' => ['Morang', 'Sunsari']],
            'Birgunj' => ['code' => 'BRG-DEPOT', 'province' => 'Madhesh', 'districts' => ['Parsa', 'Bara']],
            'Butwal' => ['code' => 'BTW-DEPOT', 'province' => 'Lumbini', 'districts' => ['Rupandehi', 'Palpa']],
            'Chitwan' => ['code' => 'CTN-DEPOT', 'province' => 'Bagmati', 'districts' => ['Chitwan', 'Nawalpur']],
        ];

        $zonesByCity = ['Kathmandu' => $ktmZone];

        foreach ($regionalZones as $cityName => $zInfo) {
            $partner = $partnersByCity[$cityName] ?? $ktmPartner;
            $zone = DeliveryZone::firstOrCreate(
                ['zone_code' => $zInfo['code']],
                [
                    'partner_id' => $partner->id,
                    'partner_user_id' => $usersByCity[$cityName]->id,
                    'zone_name' => "{$cityName} Regional Depot",
                    'zone_type' => 'urban',
                    'districts' => $zInfo['districts'],
                    'is_active' => true,
                ]
            );
            $zonesByCity[$cityName] = $zone;
        }

        // 4. Seed Domestic Partner Rates from each regional hub to Kathmandu
        $corridorRates = [
            [
                'origin' => 'Pokhara',
                'base' => 150.00,
                'per_kg' => 35.00,
                'logistical' => 50.00,
                'service_name' => 'Gandaki-Kathmandu Linehaul',
                'hours' => 24,
                'days' => 1,
            ],
            [
                'origin' => 'Biratnagar',
                'base' => 180.00,
                'per_kg' => 40.00,
                'logistical' => 60.00,
                'service_name' => 'Koshi-Kathmandu Linehaul',
                'hours' => 48,
                'days' => 2,
            ],
            [
                'origin' => 'Birgunj',
                'base' => 160.00,
                'per_kg' => 35.00,
                'logistical' => 50.00,
                'service_name' => 'Madhesh-Kathmandu Linehaul',
                'hours' => 24,
                'days' => 1,
            ],
            [
                'origin' => 'Butwal',
                'base' => 170.00,
                'per_kg' => 38.00,
                'logistical' => 50.00,
                'service_name' => 'Lumbini-Kathmandu Linehaul',
                'hours' => 36,
                'days' => 2,
            ],
            [
                'origin' => 'Chitwan',
                'base' => 140.00,
                'per_kg' => 30.00,
                'logistical' => 40.00,
                'service_name' => 'Chitwan-Kathmandu Linehaul',
                'hours' => 18,
                'days' => 1,
            ],
        ];

        foreach ($corridorRates as $cRate) {
            $originCity = $cRate['origin'];
            $partner = $partnersByCity[$originCity] ?? $ktmPartner;
            $originZone = $zonesByCity[$originCity] ?? $ktmZone;

            DomesticRate::updateOrCreate(
                [
                    'partner_id' => $usersByCity[$originCity]->id,
                    'origin_city' => $originCity,
                    'destination_city' => 'Kathmandu',
                    'service_type' => 'standard',
                ],
                [
                    'origin_zone_id' => $originZone->id,
                    'destination_zone_id' => $ktmZone->id,
                    'service_name' => $cRate['service_name'],
                    'base_rate' => $cRate['base'],
                    'per_kg_rate' => $cRate['per_kg'],
                    'rate_per_kg' => $cRate['per_kg'],
                    'minimum_rate' => $cRate['base'],
                    'logistical_charge' => $cRate['logistical'],
                    'additional_charge' => 0.00,
                    'weight_from' => 0.00,
                    'weight_to' => 1000.00,
                    'estimated_hours' => $cRate['hours'],
                    'estimated_days' => $cRate['days'],
                    'is_active' => true,
                    'effective_from' => now()->subMonths(1),
                ]
            );
        }
    }
}
