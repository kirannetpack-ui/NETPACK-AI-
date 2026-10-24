<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\DomesticRate;
use App\Models\LogisticsService;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomesticPartnerRateSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $partnerUser;
    private DeliveryZone $zone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partnerUser = User::factory()->create([
            'email' => 'ktm.partner@netpack.test',
            'user_type' => 'partner',
            'role' => 'partner',
            'verification_status' => 'approved',
        ]);

        \App\Models\DomesticPartner::firstOrCreate(
            ['id' => $this->partnerUser->id],
            [
                'code' => 'KTM-METRO',
                'name' => 'Kathmandu Metro Hub Partner',
                'company_name' => 'Kathmandu Metro Hub Logistics',
                'email' => $this->partnerUser->email,
                'password' => bcrypt('Password123!'),
                'phone' => '9851000000',
            ]
        );

        $this->zone = DeliveryZone::create([
            'partner_user_id' => $this->partnerUser->id,
            'zone_name' => 'Kathmandu Metro Hub',
            'zone_code' => 'KTM-METRO',
            'zone_type' => 'urban',
            'districts' => ['Kathmandu', 'Lalitpur'],
            'is_active' => true,
            'standard_base_rate' => 80.00,
            'standard_per_kg_rate' => 20.00,
            'standard_estimated_hours' => 24,
            'flash_base_rate' => 150.00,
            'flash_per_kg_rate' => 40.00,
            'flash_estimated_hours' => 4,
        ]);
    }

    public function test_partner_rates_index_renders_single_platform(): void
    {
        $response = $this->actingAs($this->partnerUser)->get(route('partner.rates.index'));

        $response->assertOk();
        $response->assertSee('Domestic Partner Rate Platform', false);
        $response->assertSee('Base Price (1.0 kg)', false);
        $response->assertSee('Weight-wise (+NPR/kg)', false);
        $response->assertSee('Offer New Custom Service', false);
        $response->assertSee('Kathmandu Metro Hub', false);
    }

    public function test_partner_cannot_read_or_change_another_partners_zone_rates(): void
    {
        $other = User::factory()->create(['user_type' => 'partner', 'verification_status' => 'approved']);
        $this->actingAs($other)->get(route('partner.rates.edit', $this->zone->id))->assertNotFound();
        $this->actingAs($other)->put(route('partner.rates.update', $this->zone->id), [
            'standard_base_rate' => 1,
        ])->assertNotFound();
        $this->assertEquals(80, $this->zone->fresh()->standard_base_rate);
    }

    public function test_partner_can_update_rates_with_base_price_and_weight_wise_rates(): void
    {
        $payload = [
            'rates' => [
                'standard' => [
                    'base_rate' => 100.00,
                    'per_kg_rate' => 25.00,
                    'estimated_hours' => 24,
                    'is_active' => '1',
                ],
                'flash' => [
                    'base_rate' => 180.00,
                    'per_kg_rate' => 50.00,
                    'estimated_hours' => 3,
                    'is_active' => '1',
                ],
            ],
            'standard_base_rate' => 100.00,
            'standard_per_kg_rate' => 25.00,
            'standard_estimated_hours' => 24,
            'flash_base_rate' => 180.00,
            'flash_per_kg_rate' => 50.00,
            'flash_estimated_hours' => 3,
        ];

        $response = $this->actingAs($this->partnerUser)
            ->put(route('partner.rates.update', $this->zone->id), $payload);

        $response->assertRedirect(route('partner.rates.index', ['zone' => $this->zone->id]));
        $response->assertSessionHas('success');

        // Verify DomesticRate was updated in database
        $this->assertDatabaseHas('domestic_rates', [
            'partner_id' => $this->partnerUser->id,
            'destination_zone_id' => $this->zone->id,
            'service_type' => 'standard',
            'base_rate' => 100.00,
            'per_kg_rate' => 25.00,
        ]);

        $this->assertDatabaseHas('domestic_rates', [
            'partner_id' => $this->partnerUser->id,
            'destination_zone_id' => $this->zone->id,
            'service_type' => 'flash',
            'base_rate' => 180.00,
            'per_kg_rate' => 50.00,
        ]);

        // Verify DeliveryZone legacy columns are in sync
        $this->zone->refresh();
        $this->assertEquals(100.00, (float)$this->zone->standard_base_rate);
        $this->assertEquals(25.00, (float)$this->zone->standard_per_kg_rate);
    }

    public function test_partner_can_introduce_new_custom_service(): void
    {
        $payload = [
            'name' => 'Overnight Cold Chain',
            'code' => 'overnight_cold_chain',
            'transit_time_hours' => 12,
            'base_rate' => 250.00,
            'per_kg_rate' => 60.00,
            'description' => 'Temperature controlled overnight courier for medicines and perishables',
        ];

        $response = $this->actingAs($this->partnerUser)
            ->post(route('partner.rates.custom-service'), $payload);

        $response->assertRedirect(route('partner.rates.index'));
        $response->assertSessionHas('success');

        // Verify LogisticsService was created with partner_id
        $this->assertDatabaseHas('logistics_services', [
            'partner_id' => $this->partnerUser->id,
            'name' => 'Overnight Cold Chain',
            'code' => 'overnight_cold_chain',
            'category' => 'domestic',
            'base_rate' => 250.00,
            'per_kg_rate' => 60.00,
        ]);

        // Verify DomesticRate was provisioned for this zone
        $this->assertDatabaseHas('domestic_rates', [
            'partner_id' => $this->partnerUser->id,
            'destination_zone_id' => $this->zone->id,
            'service_type' => 'overnight_cold_chain',
            'base_rate' => 250.00,
            'per_kg_rate' => 60.00,
        ]);

        // Verify custom service appears on rates index
        $viewResponse = $this->actingAs($this->partnerUser)->get(route('partner.rates.index'));
        $viewResponse->assertSee('OVERNIGHT COLD CHAIN', false);
        $viewResponse->assertSee('Custom Service', false);
    }

    public function test_domestic_rate_calculation_computes_base_and_weight_wise_cost(): void
    {
        // Rate: Base Rs. 120 (covers up to 1.0 kg) + Rs. 30 per additional kg
        $rate = DomesticRate::create([
            'partner_id' => $this->partnerUser->id,
            'origin_zone_id' => $this->zone->id,
            'origin_city' => 'Kathmandu',
            'origin_zone' => 'KTM-METRO',
            'destination_zone_id' => $this->zone->id,
            'destination_city' => 'Kathmandu',
            'destination_zone' => 'KTM-METRO',
            'service_type' => 'standard',
            'service_name' => 'STANDARD',
            'base_rate' => 120.00,
            'per_kg_rate' => 30.00,
            'rate_per_kg' => 30.00,
            'weight_from' => 0.00,
            'weight_to' => 1.00,
            'is_active' => true,
            'currency' => 'NPR',
            'effective_from' => now()->subDay(),
        ]);

        // Test calculation for 3.5 kg package
        // Base = 120 + 3.5 kg * 30 = 120 + 105 = 225
        $calculation = $rate->calculateRate(3.5);
        $this->assertEquals(120.00, $calculation['base_rate']);
        $this->assertEquals(105.00, $calculation['weight_charge']);
        $this->assertEquals(225.00, $calculation['total']);
    }

    public function test_admin_user_edit_renders_manageable_types_and_saves(): void
    {
        $admin = User::factory()->create([
            'email' => 'superadmin.test@netpack.test',
            'user_type' => 'admin',
            'verification_status' => 'approved',
        ]);

        // 1. Visit edit view for partner user
        $response = $this->actingAs($admin)->get(route('admin.users.edit', $this->partnerUser->id));
        $response->assertOk();
        $response->assertSee('Edit User Account', false);
        $response->assertSee('Account & Operational Scope', false);
        $response->assertSee('Domestic Partner', false);

        // 2. Submit update
        $updateResponse = $this->actingAs($admin)->put(route('admin.users.update', $this->partnerUser->id), [
            'name' => 'KTM Central Logistics Updated',
            'email' => $this->partnerUser->email,
            'user_type' => 'partner',
            'phone' => '9841122334',
            'verification_status' => 'approved',
            'service_scope' => 'domestic',
            'permanent_address' => 'Kathmandu Cargo Complex',
        ]);

        $updateResponse->assertRedirect(route('admin.users.index'));
        $this->partnerUser->refresh();
        $this->assertEquals('KTM Central Logistics Updated', $this->partnerUser->name);
        $this->assertEquals('9841122334', $this->partnerUser->phone);
        $this->assertEquals('domestic', $this->partnerUser->service_scope);
        $this->assertEquals('Kathmandu Cargo Complex', $this->partnerUser->permanent_address);
    }

    public function test_admin_can_view_and_filter_domestic_rates_with_custom_service(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.rates.filter@netpack.test',
            'user_type' => 'admin',
            'verification_status' => 'approved',
        ]);

        // Create a custom service rate
        $customRate = DomesticRate::create([
            'partner_id' => $this->partnerUser->id,
            'origin_zone_id' => $this->zone->id,
            'destination_zone_id' => $this->zone->id,
            'origin_city' => 'Kathmandu',
            'origin_zone' => 'KTM-METRO',
            'destination_city' => 'Kathmandu',
            'destination_zone' => 'KTM-METRO',
            'service_type' => 'cold_chain_pharma',
            'service_name' => 'Cold Chain Pharma Express',
            'base_rate' => 350.00,
            'per_kg_rate' => 75.00,
            'rate_per_kg' => 75.00,
            'weight_from' => 0.00,
            'weight_to' => 10.00,
            'is_active' => true,
            'currency' => 'NPR',
            'effective_from' => now()->subDay(),
        ]);

        // Visit admin domestic rates index
        $response = $this->actingAs($admin)->get(route('admin.domestic.rates'));
        $response->assertOk();
        $response->assertSee('Domestic Tariff & Corridor Rates', false);
        $response->assertSee('Cold Chain Pharma Express', false);
        $response->assertSee('Partner Custom', false);
        $response->assertSee('Rs. 350.00', false);
        $response->assertSee('+Rs. 75.00', false);

        // Filter by partner and service
        $filterResponse = $this->actingAs($admin)->get(route('admin.domestic.rates', [
            'partner_id' => $this->partnerUser->id,
            'service_type' => 'cold_chain_pharma',
        ]));
        $filterResponse->assertOk();
        $filterResponse->assertSee('Cold Chain Pharma Express', false);
    }

    public function test_shipments_create_includes_partner_custom_service_options(): void
    {
        // Register custom service in LogisticsService
        LogisticsService::create([
            'partner_id' => $this->partnerUser->id,
            'code' => 'overnight_priority_express',
            'name' => 'Overnight Priority Express',
            'category' => 'domestic',
            'transit_time_hours' => 12.0,
            'base_rate' => 200.00,
            'per_kg_rate' => 45.00,
            'is_active' => true,
        ]);

        $client = User::factory()->create([
            'email' => 'booking.client@netpack.test',
            'user_type' => 'client',
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($client)->get(route('shipments.create'));
        $response->assertOk();
        $response->assertSee('Overnight Priority Express', false);
        $response->assertSee('overnight_priority_express', false);
    }
}
