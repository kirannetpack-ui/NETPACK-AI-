<?php

namespace Tests\Feature;

use App\Models\InternationalRate;
use App\Models\InternationalZone;
use App\Models\OverseasHub;
use App\Models\PackagingMaterial;
use App\Models\Shipment;
use App\Models\User;
use App\Services\InternationalRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternationalRateCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed standard overseas hubs and international rate matrices
        $this->seed(\Database\Seeders\InternationalHubsAndAgenciesSeeder::class);
        $this->seed(\Database\Seeders\InternationalRatesSeeder::class);
    }

    /**
     * Test precision air cargo weight calculations and rounding brackets.
     */
    public function test_weight_calculation_and_rounding_rules(): void
    {
        $service = new InternationalRateService();

        // Under 10kg: exact integer
        $w1 = $service->calculateWeight(1.0);
        $this->assertEquals(1.0, $w1['chargeable_weight']);

        // Under 10kg: 0.01 to 0.50 fraction rounds to next 0.5kg slab
        $w2 = $service->calculateWeight(1.2);
        $this->assertEquals(1.5, $w2['chargeable_weight']);

        // Under 10kg: >0.50 fraction rounds to next whole integer
        $w3 = $service->calculateWeight(1.7);
        $this->assertEquals(2.0, $w3['chargeable_weight']);

        // Above 10kg: any fraction rounds up to the next integer kg ceiling
        $w4 = $service->calculateWeight(10.2);
        $this->assertEquals(11.0, $w4['chargeable_weight']);

        $w5 = $service->calculateWeight(14.8);
        $this->assertEquals(15.0, $w5['chargeable_weight']);

        // Volumetric weight: (50 x 40 x 30 cm) / 5000 = 12.0 kg vs 4.0 kg gross
        $w6 = $service->calculateWeight(4.0, 50, 40, 30);
        $this->assertTrue($w6['is_volumetric']);
        $this->assertEquals(12.0, $w6['volumetric_weight']);
        $this->assertEquals(12.0, $w6['chargeable_weight']);
    }

    /**
     * Test rate quotation engine with itemized breakdown and packaging fees.
     */
    public function test_international_quote_generation(): void
    {
        $service = new InternationalRateService();

        $quote = $service->quote('United States', 2.5, null, null, null, 'small_box');

        $this->assertEquals('United States', $quote['country']);
        $this->assertEquals(2.5, $quote['weight_info']['chargeable_weight']);
        $this->assertEquals('small_box', $quote['packaging']['id']);
        $this->assertGreaterThan(0, $quote['packaging']['price']);
        $this->assertNotEmpty($quote['quotes']);

        $firstQuote = $quote['quotes'][0];
        $this->assertArrayHasKey('service_type', $firstQuote);
        $this->assertArrayHasKey('itemized', $firstQuote);
        $this->assertGreaterThan(0, $firstQuote['itemized']['base_freight']);
        $this->assertGreaterThan(0, $firstQuote['itemized']['total_cost']);
        $this->assertEquals($quote['packaging']['price'], $firstQuote['itemized']['packaging_fee']);
    }

    /**
     * Test public / client Rate Inquiry Desk page loads with quotes.
     */
    public function test_rate_inquiry_page_loads_successfully(): void
    {
        $response = $this->get(route('rates.inquiry'));
        $response->assertOk();
        $response->assertSee('International Air Cargo', false);
        $response->assertSee('Rate Inquiry', false);
        $response->assertSee('Shipment Specifications', false);
        $response->assertSee('United States', false);
        $response->assertSee('x-data="rateInquiryDesk()"', false);
        $response->assertDontSee('c.toLowerCase().includes(q));', false);
    }

    /**
     * Test live AJAX rate calculation endpoint.
     */
    public function test_live_rate_calculate_endpoint(): void
    {
        $response = $this->postJson(route('rates.calculate'), [
            'country' => 'United States',
            'weight' => 3.5,
            'length' => 20,
            'width' => 20,
            'height' => 20,
            'packaging' => 'small_box',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'country',
                'weight_info' => [
                    'gross_weight',
                    'volumetric_weight',
                    'chargeable_weight',
                    'explanation',
                ],
                'packaging',
                'quotes_count',
                'quotes',
            ]
        ]);
        $response->assertJson([
            'success' => true,
            'data' => [
                'country' => 'United States',
            ]
        ]);
    }

    /**
     * Test admin tariff matrices management: index, create, and toggle.
     */
    public function test_admin_can_manage_international_rate_matrices(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'super_admin',
            'password_changed' => true,
        ]);

        // Index page
        $response = $this->actingAs($admin)->get(route('admin.international-rates.index'));
        $response->assertOk();
        $response->assertSee('International Sector Rate Management');
        $response->assertSee('United States');

        // Create page
        $createResponse = $this->actingAs($admin)->get(route('admin.international-rates.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('New international rate matrix');

        // Toggle active status
        $firstRate = InternationalRate::first();
        $this->assertNotNull($firstRate);
        $toggleResponse = $this->actingAs($admin)->patchJson(route('admin.international-rates.toggle', $firstRate->id));
        $toggleResponse->assertOk();
        $this->assertFalse((bool) $firstRate->fresh()->is_active);
    }

    /**
     * Test flow from Rate Inquiry Desk booking to consignment creation.
     */
    public function test_rate_inquiry_booking_flow_prefills_shipment_form_and_computes_chargeable_weight(): void
    {
        $client = User::factory()->create([
            'user_type' => 'client',
            'password_changed' => true,
        ]);

        // 1. Visit shipment creation with prefilled rate inquiry parameters
        $params = [
            'shipment_type' => 'international',
            'receiver_country' => 'United States',
            'weight' => 2.5,
            'chargeable_weight' => 2.5,
            'service_type' => 'express',
            'packaging' => 'small_box',
            'quoted_rate' => 6176,
        ];

        $viewResponse = $this->actingAs($client)->get(route('shipments.create', $params));
        $viewResponse->assertOk();
        $viewResponse->assertSee('Verified Rate Applied');
        $viewResponse->assertSee('6,176');

        // 2. Submit shipment creation for international sector
        $this->withoutExceptionHandling();
        $storeResponse = $this->actingAs($client)->post(route('shipments.store'), [
            'shipment_type' => 'international',
            'service_type' => 'express',
            'express_carrier' => 'DHL',
            'package_type' => 'box',
            'weight' => 2.5,
            'length' => 25,
            'width' => 20,
            'height' => 15, // Volumetric = 25*20*15/5000 = 1.5kg < 2.5kg gross -> Chargeable = 2.5kg
            'pickup_name' => ['Sender Contact'],
            'pickup_phone' => ['9800000000'],
            'pickup_address' => ['Kathmandu Cargo Terminal'],
            'receiver_name' => 'John Doe',
            'receiver_phone' => '+1-555-0199',
            'receiver_street' => '123 Market St',
            'receiver_city' => 'New York',
            'receiver_state' => 'NY',
            'receiver_postal_code' => '10001',
            'receiver_country' => 'United States',
        ]);

        $storeResponse->assertSessionHasNoErrors();

        $shipment = Shipment::where('receiver_country', 'United States')->latest('id')->first();
        $this->assertNotNull($shipment);
        $this->assertEquals(2.5, (float)$shipment->actual_weight);
        $this->assertEquals(2.5, (float)$shipment->chargeable_weight);
        $this->assertGreaterThan(0, (float)$shipment->shipping_cost);
        $storeResponse->assertRedirect(route('tracking.show', $shipment->tracking_number));
    }

    /**
     * Test dynamic packaging materials catalog and dynamic pricing updates.
     */
    public function test_dynamic_packaging_materials_catalog_and_pricing_updates(): void
    {
        $service = new InternationalRateService();

        // 1. Verify dynamic catalog loaded from database
        $catalog = $service->getPackagingCatalog();
        $this->assertArrayHasKey('small_box', $catalog);
        $this->assertArrayHasKey('wooden_crate', $catalog);
        $this->assertEquals(350.0, $catalog['small_box']['price']);

        // 2. Dynamically update price in database
        $box = \App\Models\PackagingMaterial::where('code', 'small_box')->first();
        $this->assertNotNull($box);
        $box->update(['price' => 475.00]);

        // 3. New quote should immediately reflect dynamic database price
        $newQuote = $service->quote('United States', 2.0, null, null, null, 'small_box');
        $this->assertEquals(475.00, $newQuote['packaging']['price']);
        $this->assertEquals(475.00, $newQuote['quotes'][0]['itemized']['packaging_fee']);
    }

    /**
     * Test Super Admin dynamic Customs Clearance and Godown Charges feed.
     */
    public function test_super_admin_dynamic_customs_clearance_and_godown_charges_feed(): void
    {
        $superAdmin = User::factory()->create([
            'user_type' => 'super_admin',
            'password_changed' => true,
        ]);

        $service = new InternationalRateService();

        // Initial baseline quote
        $initialQuote = $service->quote('United States', 1.0);
        $this->assertArrayHasKey('global_tariff_inclusions', $initialQuote);
        $this->assertEquals(500.00, $initialQuote['global_tariff_inclusions']['customs_clearance']);
        $this->assertEquals(300.00, $initialQuote['global_tariff_inclusions']['godown_charge']);

        // Super admin updates dynamic tariff settings with bulk sync
        $postResponse = $this->actingAs($superAdmin)->post(route('admin.international-rates.settings.tariff'), [
            'default_customs_clearance_charge' => 750.00,
            'default_godown_charge' => 450.00,
            'customs_charge_notice' => 'Updated TIA customs clearance export fee.',
            'godown_charge_notice' => 'Updated TIA terminal godown fee.',
            'sync_all_matrices' => 1,
        ]);

        $postResponse->assertSessionHasNoErrors();
        $postResponse->assertRedirect(route('admin.international-rates.settings'));

        // Verify quote immediately reflects new Super Admin dynamic charges
        $updatedQuote = $service->quote('United States', 1.0);
        $this->assertEquals(750.00, $updatedQuote['global_tariff_inclusions']['customs_clearance']);
        $this->assertEquals(450.00, $updatedQuote['global_tariff_inclusions']['godown_charge']);
        $this->assertEquals(750.00, $updatedQuote['quotes'][0]['itemized']['customs_clearance']);
        $this->assertEquals(450.00, $updatedQuote['quotes'][0]['itemized']['godown_charge']);

        // Verify all existing rate matrices were updated
        $this->assertEquals(0, InternationalRate::where('customs_clearance_charge', '!=', 750.00)->count());
    }

    /**
     * Test dynamic destination country typing and admin settings management.
     */
    public function test_dynamic_country_typing_and_admin_settings_management(): void
    {
        $superAdmin = User::factory()->create([
            'user_type' => 'super_admin',
            'password_changed' => true,
        ]);

        // 1. Test admin settings page loads
        $settingsView = $this->actingAs($superAdmin)->get(route('admin.international-rates.settings'));
        $settingsView->assertOk();
        $settingsView->assertSee('Dynamic Tariff Settings & Packaging Catalog', false);
        $settingsView->assertSee('Global Customs Clearance', false);
        $settingsView->assertSee('Reinforced Document Envelope', false);

        // 2. Admin adds a new packaging type dynamically
        $addPkgResponse = $this->actingAs($superAdmin)->post(route('admin.international-rates.settings.packaging.store'), [
            'code' => 'thermocol_insulated_box',
            'name' => 'Thermocol Insulated Cold Cargo Box',
            'price' => 850.00,
            'description' => 'Insulated temperature-safe packaging for perishable exports.',
            'icon' => 'snowflake',
            'sort_order' => 8,
            'is_active' => 1,
        ]);

        $addPkgResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('packaging_materials', [
            'code' => 'thermocol_insulated_box',
            'price' => 850.00,
        ]);

        // 3. Test dynamic country typing in rate inquiry calculate endpoint
        // Client types 'Australia' dynamically
        $calcResponse = $this->postJson(route('rates.calculate'), [
            'country' => 'Australia',
            'weight' => 2.0,
            'packaging' => 'thermocol_insulated_box',
        ]);

        $calcResponse->assertOk();
        $calcResponse->assertJsonPath('data.country', 'Australia');
        $calcResponse->assertJsonPath('data.packaging.code', 'thermocol_insulated_box');
        $calcResponse->assertJsonPath('data.packaging.price', 850);
        $this->assertNotEmpty($calcResponse->json('data.quotes'));
    }

    /**
     * Test that Super Admin and International Staff can configure rate matrices according to hubs.
     */
    public function test_super_admin_and_staff_can_enter_rates_according_to_hubs_and_quote_properly(): void
    {
        $superAdmin = User::factory()->create(['user_type' => 'super_admin']);
        $staff = User::factory()->create([
            'user_type' => 'staff',
            'service_scope' => 'international',
        ]);

        $hub = OverseasHub::create([
            'hub_code' => 'SYDTEST',
            'hub_name' => 'Sydney Oceania Hub',
            'country' => 'Australia',
            'location' => 'Sydney Airport',
            'mode_type' => 'DDP',
            'coverage_countries' => ['Australia', 'New Zealand', 'Fiji'],
            'service_routes' => ['Pacific Express', 'Trans-Tasman Linehaul'],
            'is_active' => true,
            'sort_order' => 10,
        ]);

        // 1. Visit create page with ?hub_id=... as Super Admin
        $createView = $this->actingAs($superAdmin)->get(route('admin.international-rates.create', ['hub_id' => $hub->id]));
        $createView->assertOk();
        $createView->assertSee('SYDTEST');
        $createView->assertSee('Sydney Oceania Hub');
        $createView->assertSee('Defined Clearance & Delivery Countries for this Hub', false);

        // 2. International Staff visits create page
        $staffCreateView = $this->actingAs($staff)->get(route('admin.international-rates.create', ['hub_id' => $hub->id]));
        $staffCreateView->assertOk();

        // 3. Super Admin creates a rate matrix for a country covered by this hub
        $storeResponse = $this->actingAs($superAdmin)->post(route('admin.international-rates.store'), [
            'rate_type' => 'country',
            'country' => 'New Zealand',
            'country_code' => 'NZ',
            'hub_id' => $hub->id,
            'service_type' => 'economy',
            'transit_days_min' => 6,
            'transit_days_max' => 8,
            'weight_tiers' => [
                '0.5' => 2200,
                '1.0' => 2600,
                '1.5' => 3000,
                '2.0' => 3400,
                '10.0' => 9800,
            ],
            'per_kg_tiers' => [
                [
                    'min_weight' => 10.1,
                    'max_weight' => 45.0,
                    'rate_per_kg' => 850,
                ],
                [
                    'min_weight' => 45.1,
                    'max_weight' => 9999.0,
                    'rate_per_kg' => 750,
                ],
            ],
            'customs_clearance_charge' => 450.00,
            'godown_charge' => 250.00,
            'fuel_surcharge_percent' => 0,
            'doc_fee' => 0,
            'notes' => 'Direct DDP clearance via Sydney Oceania Hub to Auckland/Wellington.',
            'is_active' => 1,
        ]);

        $storeResponse->assertRedirect(route('admin.international-rates.index'));
        $storeResponse->assertSessionHas('success');

        $this->assertDatabaseHas('international_rates', [
            'country' => 'New Zealand',
            'hub_id' => $hub->id,
            'service_type' => 'economy',
            'customs_clearance_charge' => 450.00,
        ]);

        // 4. Rate inquiry quote calculation for New Zealand under this hub
        $quoteResponse = $this->postJson(route('rates.calculate'), [
            'country' => 'New Zealand',
            'weight' => 1.5,
        ]);

        $quoteResponse->assertOk();
        $quotes = $quoteResponse->json('data.quotes');
        $this->assertNotEmpty($quotes);

        $nzQuote = collect($quotes)->firstWhere('service_type', 'economy');
        $this->assertNotNull($nzQuote);
        $this->assertEquals(3000, $nzQuote['itemized']['base_freight']);
        $this->assertEquals(450, $nzQuote['itemized']['customs_clearance']);
        $this->assertEquals(375, $nzQuote['itemized']['godown_charge']); // 1.5 KG * Rs. 250/kg = Rs. 375
        $this->assertEquals(3825, $nzQuote['itemized']['total_cost']);
    }

    /**
     * Test Godown / Terminal Handling is calculated strictly per kilo.
     */
    public function test_godown_terminal_handling_is_calculated_strictly_per_kilo(): void
    {
        $service = new InternationalRateService();

        // United States matrix in seeder has godown_charge = 200 NPR / kg
        // 0.5kg shipment: 0.5 * 200 = 100
        $quoteHalf = $service->quote('United States', 0.5);
        $this->assertEquals(100.00, $quoteHalf['quotes'][0]['itemized']['godown_charge']);
        $this->assertEquals(200.00, $quoteHalf['quotes'][0]['itemized']['godown_rate_per_kg']);

        // 2.0kg shipment: 2.0 * 200 = 400
        $quoteTwo = $service->quote('United States', 2.0);
        $this->assertEquals(400.00, $quoteTwo['quotes'][0]['itemized']['godown_charge']);

        // 5.5kg shipment: 5.5 * 200 = 1100
        $quoteFiveHalf = $service->quote('United States', 5.5);
        $this->assertEquals(1100.00, $quoteFiveHalf['quotes'][0]['itemized']['godown_charge']);

        // 12.0kg shipment: 12.0 * 200 = 2400
        $quoteTwelve = $service->quote('United States', 12.0);
        $this->assertEquals(2400.00, $quoteTwelve['quotes'][0]['itemized']['godown_charge']);

        // United Kingdom rate in seeder has godown_charge = 300 NPR / kg
        $quoteUK = $service->quote('United Kingdom', 3.0);
        $this->assertEquals(900.00, $quoteUK['quotes'][0]['itemized']['godown_charge']); // 3.0 * 300 = 900
        $this->assertEquals(300.00, $quoteUK['quotes'][0]['itemized']['godown_rate_per_kg']);

        // Test manual zero entry (e.g. promotional free godown handling)
        $hub = OverseasHub::first();
        InternationalRate::create([
            'rate_type' => 'country',
            'country' => 'Switzerland',
            'country_code' => 'CH',
            'hub_id' => $hub->id,
            'service_type' => 'economy',
            'weight_tiers' => ['0.5' => 3200, '1.0' => 3800],
            'per_kg_tiers' => [['min_weight' => 10.1, 'max_weight' => 20.0, 'rate_per_kg' => 950]],
            'customs_clearance_charge' => 500,
            'godown_charge' => 0.0, // Manually entered as 0 (free terminal handling)
            'transit_days_min' => 4,
            'transit_days_max' => 7,
            'is_active' => true,
        ]);

        $quoteSwiss = $service->quote('Switzerland', 2.5);
        $this->assertEquals(0.00, $quoteSwiss['quotes'][0]['itemized']['godown_charge']);
        $this->assertEquals(0.00, $quoteSwiss['quotes'][0]['itemized']['godown_rate_per_kg']);
    }

    /**
     * Test settings view renders Per KG badge and packaging material editing works.
     */
    public function test_settings_view_and_packaging_editing(): void
    {
        $superAdmin = User::factory()->create([
            'user_type' => 'super_admin',
            'password_changed' => true,
        ]);

        // 1. Visit settings page
        $res = $this->actingAs($superAdmin)->get(route('admin.international-rates.settings'));
        $res->assertOk();
        $res->assertSee('Per KG');
        $res->assertSee('tariffSettingsPage()');

        // 2. Update packaging material
        $pack = PackagingMaterial::where('code', 'bubble_flyer')->first();
        $this->assertNotNull($pack);

        $updateRes = $this->actingAs($superAdmin)->put(route('admin.international-rates.settings.packaging.update', $pack->id), [
            'name' => 'Premium Metallic Bubble Flyer Bag',
            'price' => 280.00,
            'description' => 'Heavy duty waterproof metallic bubble flyer.',
            'icon' => 'shield-check',
            'sort_order' => 5,
            'is_active' => 1,
        ]);

        $updateRes->assertRedirect(route('admin.international-rates.settings'));
        $this->assertDatabaseHas('packaging_materials', [
            'id' => $pack->id,
            'name' => 'Premium Metallic Bubble Flyer Bag',
            'price' => 280.00,
        ]);
    }

    /**
     * Test Godown / Terminal Handling is manually entered and can be custom provided in live calculation.
     */
    public function test_manual_godown_terminal_handling_entry(): void
    {
        $superAdmin = User::factory()->create([
            'user_type' => 'super_admin',
            'password_changed' => true,
        ]);

        // 1. Visit create rate matrix page: verify Godown input is ready for manual entry
        $createPage = $this->actingAs($superAdmin)->get(route('admin.international-rates.create'));
        $createPage->assertOk();
        $createPage->assertSee('Terminal handling (NPR/kg) *', false);
        $createPage->assertSee('placeholder="Enter rate per kg (e.g. 50)"', false);
        $createPage->assertSee('Manually entered per-kilo handling fee', false);

        // 2. Live calculate endpoint with manual godown_rate_per_kg = 50
        $resManual = $this->postJson(route('rates.calculate'), [
            'country' => 'United States',
            'weight' => 2.0,
            'godown_rate_per_kg' => 50.0,
        ]);
        $resManual->assertOk();
        $quote = $resManual->json('data.quotes.0');
        $this->assertEquals(50.0, $quote['itemized']['godown_rate_per_kg']);
        $this->assertEquals(100.0, $quote['itemized']['godown_charge']); // 2.0 KG * Rs. 50/kg = Rs. 100

        // 3. Create rate matrix with manually entered godown_charge = 85.00
        $hub = OverseasHub::first();
        $storeRes = $this->actingAs($superAdmin)->post(route('admin.international-rates.store'), [
            'rate_type' => 'country',
            'country' => 'Norway',
            'country_code' => 'NO',
            'hub_id' => $hub->id,
            'service_type' => 'express',
            'weight_tiers' => ['0.5' => 3500, '1.0' => 4200],
            'per_kg_tiers' => [['min_weight' => 10.1, 'max_weight' => 20.0, 'rate_per_kg' => 950]],
            'customs_clearance_charge' => 500,
            'godown_charge' => 85.00, // Manually entered by user
            'transit_days_min' => 3,
            'transit_days_max' => 5,
            'is_active' => 1,
        ]);
        $storeRes->assertRedirect(route('admin.international-rates.index'));

        // Query rate for Norway: verify it uses manually entered 85 NPR/kg
        $service = new InternationalRateService();
        $quoteNorway = $service->quote('Norway', 1.0);
        $this->assertEquals(85.0, $quoteNorway['quotes'][0]['itemized']['godown_rate_per_kg']);
        $this->assertEquals(85.0, $quoteNorway['quotes'][0]['itemized']['godown_charge']);
    }
}
