<?php

namespace Tests\Feature;

use App\Models\DomesticPartner;
use App\Models\DomesticRate;
use App\Models\GlobalTariffSetting;
use App\Models\InternationalRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternationalRateFeederPickupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic settings
        GlobalTariffSetting::setValue('default_customs_clearance_charge', 500.00);
        GlobalTariffSetting::setValue('default_godown_charge', 300.00);

        // Create standard international rate for USA
        InternationalRate::create([
            'rate_type' => 'country',
            'country' => 'United States',
            'service_type' => 'express',
            'weight_from' => 0.5,
            'weight_to' => 10.0,
            'weight_tiers' => [
                '0.5' => 2500,
                '1.0' => 3200,
                '1.5' => 3900,
                '2.0' => 4600,
                '2.5' => 5300,
                '3.0' => 6000,
            ],
            'transit_days_min' => 3,
            'transit_days_max' => 5,
            'fuel_surcharge_percent' => 0,
            'doc_fee' => 0,
            'is_active' => true,
        ]);

        // Seed standard domestic partner feeder rates
        $this->seed(\Database\Seeders\DomesticPartnerFeederRatesSeeder::class);
    }

    /**
     * Test inquiry page loads with pickup origin controls and regional cities.
     */
    public function test_inquiry_page_renders_origin_selector_and_regional_cities(): void
    {
        $response = $this->get(route('rates.inquiry'));

        $response->assertOk();
        $response->assertSee('Pickup Origin (Nepal)', false);
        $response->assertSee('Inside Valley (KTM)', false);
        $response->assertSee('Outside Kathmandu', false);
        $response->assertSee('Pokhara Depot (Gandaki)', false);
        $response->assertSee('Biratnagar / Itahari Hub (Koshi)', false);
        $response->assertSee('Birgunj Gateway (Madhesh)', false);
    }

    /**
     * Test quote calculation inside Kathmandu Valley (no domestic feeder charge).
     */
    public function test_calculate_inside_kathmandu_valley_has_zero_feeder_charge(): void
    {
        $response = $this->postJson(route('rates.calculate'), [
            'country' => 'United States',
            'weight' => 2.0,
            'pickup_location_type' => 'inside_ktm',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'country' => 'United States',
                'domestic_feeder' => [
                    'is_applicable' => false,
                    'total_feeder_charge' => 0,
                ],
            ],
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['quotes']);
        $quote = $data['quotes'][0];

        // 2.0kg rate = 4600 + customs 500 + godown (2.0 * 300 = 600) = 5700
        $this->assertEquals(0, $quote['itemized']['domestic_feeder_charge']);
        $this->assertEquals(5700, $quote['itemized']['total_cost']);
    }

    /**
     * Test quote calculation outside Kathmandu Valley (Pokhara partner rate applied).
     */
    public function test_calculate_outside_kathmandu_pokhara_adds_partner_feeder_charge(): void
    {
        $response = $this->postJson(route('rates.calculate'), [
            'country' => 'United States',
            'weight' => 2.0,
            'pickup_location_type' => 'outside_ktm',
            'pickup_city' => 'Pokhara',
        ]);

        $response->assertOk();
        $data = $response->json('data');

        $this->assertTrue($data['domestic_feeder']['is_applicable']);
        $this->assertEquals('Pokhara', $data['domestic_feeder']['pickup_city']);
        
        // Pokhara partner rate:
        // base_rate = 150
        // weight_charge = 2.0kg * 35 = 70
        // logistical_charge = 50
        // total feeder = 150 + 70 + 50 = 270
        $feederCharge = $data['domestic_feeder']['total_feeder_charge'];
        $this->assertEquals(270.0, $feederCharge);

        $this->assertNotEmpty($data['quotes']);
        $quote = $data['quotes'][0];

        // Total should equal intl total (5700) + feeder charge (270) = 5970
        $this->assertEquals(270.0, $quote['itemized']['domestic_feeder_charge']);
        $this->assertEquals(5970.0, $quote['itemized']['total_cost']);
        $this->assertStringContainsString('Gandaki Express Partner', $quote['itemized']['domestic_feeder']['partner_name']);
    }

    /**
     * Test quote calculation outside Kathmandu Valley with fallback regional grid for unseeded cities.
     */
    public function test_calculate_outside_kathmandu_fallback_grid(): void
    {
        $response = $this->postJson(route('rates.calculate'), [
            'country' => 'United States',
            'weight' => 1.0,
            'pickup_location_type' => 'outside_ktm',
            'pickup_city' => 'Dhangadhi',
        ]);

        $response->assertOk();
        $data = $response->json('data');

        $this->assertTrue($data['domestic_feeder']['is_applicable']);
        $this->assertEquals('Dhangadhi', $data['domestic_feeder']['pickup_city']);
        $this->assertGreaterThan(0, $data['domestic_feeder']['total_feeder_charge']);

        $quote = $data['quotes'][0];
        $this->assertGreaterThan(0, $quote['itemized']['domestic_feeder_charge']);
        $this->assertEquals(
            $quote['itemized']['base_freight'] + $quote['itemized']['customs_clearance'] + $quote['itemized']['godown_charge'] + $quote['itemized']['domestic_feeder_charge'],
            $quote['itemized']['total_cost']
        );
    }

    /**
     * Test shipment creation page displays regional feeder callout when redirected from Rate Inquiry.
     */
    public function test_shipment_create_displays_feeder_callout_banner(): void
    {
        $user = User::factory()->create(['user_type' => 'customer']);

        $response = $this->actingAs($user)->get(route('shipments.create', [
            'shipment_type' => 'international',
            'receiver_country' => 'United States',
            'weight' => 2.0,
            'chargeable_weight' => 2.0,
            'service_type' => 'express',
            'quoted_rate' => 5935,
            'pickup_location_type' => 'outside_ktm',
            'pickup_city' => 'Pokhara',
            'domestic_feeder_charge' => 235,
        ]));

        $response->assertOk();
        $response->assertSee('Verified Rate Applied', false);
        $response->assertSee('Regional Feeder: Pokhara', false);
        $response->assertSee('Includes Rs. 235 Feeder Linehaul', false);
    }
}
