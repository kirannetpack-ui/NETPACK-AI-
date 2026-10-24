<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\GlobalTariffSetting;
use App\Models\LastMileCarrier;
use App\Models\OverseasHub;
use App\Models\OverseasRate;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientBookingNoHubRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        GlobalTariffSetting::updateOrCreate(['setting_key' => 'customs_clearance_charge'], [
            'setting_value' => '500',
            'display_name' => 'Customs Clearance Baseline',
        ]);
        GlobalTariffSetting::updateOrCreate(['setting_key' => 'godown_charge'], [
            'setting_value' => '300',
            'display_name' => 'Godown Baseline',
        ]);
    }

    public function test_client_booking_form_shows_no_hubs_or_carrier_routing_fields()
    {
        $client = User::factory()->create([
            'user_type' => 'client',
            'role' => 'client',
        ]);

        $response = $this->actingAs($client)->get(route('shipments.create', [
            'shipment_type' => 'international',
            'receiver_country' => 'Canada',
            'service_type' => 'economy',
        ]));

        $response->assertOk();

        // Must NOT show hubs, partner agencies, or last mile carriers in client booking
        $response->assertDontSee('Designated Transit Hub');
        $response->assertDontSee('Partner Receiving Agency');
        $response->assertDontSee('Booked Express Carrier Partner');
        $response->assertDontSee('booking_hub_id');
        $response->assertDontSee('booking_agency_id');

        // Must show clean service types
        $response->assertSee('Priority Express Service (3–4 Working Days)');
        $response->assertSee('Economy Air Cargo Service (6–8 Working Days)');
    }

    public function test_client_books_service_without_hubs_and_admin_defines_routing_post_confirmation()
    {
        $client = User::factory()->create([
            'user_type' => 'client',
            'role' => 'client',
        ]);

        $admin = User::factory()->create([
            'user_type' => 'super_admin',
            'role' => 'super_admin',
        ]);

        // Create available hub, agency, carrier
        $hub = OverseasHub::create([
            'code' => 'DXB',
            'name' => 'Dubai Gateway Hub',
            'country' => 'United Arab Emirates',
            'city' => 'Dubai',
            'is_active' => true,
        ]);

        $agency = Agency::create([
            'hub_id' => $hub->id,
            'code' => 'DXB-DESK',
            'name' => 'Central Hub Desk',
            'email' => 'dxbdesk@netpack.com',
            'country' => 'United Arab Emirates',
            'city' => 'Dubai',
            'is_active' => true,
        ]);

        $carrier = LastMileCarrier::create([
            'hub_id' => $hub->id,
            'code' => 'CANPAR',
            'name' => 'Canpar Express',
            'country' => 'Canada',
            'is_active' => true,
        ]);

        // Rate matrix for Canada
        \App\Models\InternationalRate::create([
            'country' => 'Canada',
            'service_type' => 'economy',
            'rate_type' => 'country',
            'hub_id' => $hub->id,
            'weight_from' => 0.5,
            'weight_to' => 30.0,
            'rate_per_kg' => 1200,
            'min_charge' => 2000,
            'transit_days_min' => 6,
            'transit_days_max' => 8,
            'is_active' => true,
        ]);

        // 1. Client Books Economy Air Cargo without any hub or carrier routing
        $storeResponse = $this->actingAs($client)->post(route('shipments.store'), [
            'shipment_type' => 'international',
            'service_type' => 'economy',
            'package_type' => 'box',
            'weight' => 5.0,
            'pickup_name' => ['Shipper Kathmandu'],
            'pickup_phone' => ['9841000000'],
            'pickup_address' => ['Thamel, Kathmandu'],
            'receiver_name' => 'Jane Smith',
            'receiver_phone' => '+1-416-555-0199',
            'receiver_street' => '100 Queen St W',
            'receiver_city' => 'Toronto',
            'receiver_state' => 'ON',
            'receiver_postal_code' => 'M5H 2N2',
            'receiver_country' => 'Canada',
        ]);

        $storeResponse->assertSessionHasNoErrors();

        $shipment = Shipment::where('receiver_country', 'Canada')->latest('id')->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('pending', $shipment->status);
        $this->assertNull($shipment->current_hub_id);
        $this->assertNull($shipment->current_agency_id);
        $this->assertNull($shipment->last_mile_carrier_name);

        // 2. Admin views shipment details
        $adminView = $this->actingAs($admin)->get(route('international.shipments.show', $shipment->id));
        $adminView->assertOk();
        $adminView->assertSee('International Gateway Routing', false);
        $adminView->assertSee('Designated International Hub');
        $adminView->assertSee('DXB - Dubai Gateway Hub');

        // 3. Admin defines the routing and confirms the booking
        $routingResponse = $this->actingAs($admin)->put(route('international.shipments.update-routing', $shipment->id), [
            'current_hub_id' => $hub->id,
            'current_agency_id' => $agency->id,
            'customs_mode' => 'DDP',
            'last_mile_carrier_name' => 'Canpar Express',
            'last_mile_tracking_number' => 'CAN-TOR-998811',
            'status' => 'confirmed',
            'routing_notes' => 'Routing confirmed via DXB Hub with Canpar Toronto last mile delivery.',
        ]);

        $routingResponse->assertRedirect(route('international.shipments.show', $shipment->id));
        $routingResponse->assertSessionHas('success');

        $shipment->refresh();
        $this->assertEquals('confirmed', $shipment->status);
        $this->assertEquals($hub->id, $shipment->current_hub_id);
        $this->assertEquals($agency->id, $shipment->current_agency_id);
        $this->assertEquals('DDP', $shipment->customs_mode);
        $this->assertEquals('Canpar Express', $shipment->last_mile_carrier_name);
        $this->assertEquals('CAN-TOR-998811', $shipment->last_mile_tracking_number);
    }

    public function test_rate_inquiry_does_not_expose_hubs_or_mode_types_to_client()
    {
        $hub = OverseasHub::create([
            'code' => 'LHR',
            'name' => 'London Heathrow Hub',
            'country' => 'United Kingdom',
            'city' => 'London',
            'is_active' => true,
        ]);

        \App\Models\InternationalRate::create([
            'country' => 'United Kingdom',
            'service_type' => 'economy',
            'rate_type' => 'country',
            'hub_id' => $hub->id,
            'weight_from' => 0.5,
            'weight_to' => 30.0,
            'rate_per_kg' => 1500,
            'min_charge' => 2500,
            'transit_days_min' => 6,
            'transit_days_max' => 8,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('rates.calculate'), [
            'country' => 'United Kingdom',
            'weight' => 2.0,
        ]);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertNotEmpty($data['quotes']);
        
        $quote = $data['quotes'][0];
        // Service label should NOT have "Gateway Hub"
        $this->assertStringNotContainsString('Gateway Hub', $quote['service_label']);
        $this->assertStringContainsString('Economy Air Cargo', $quote['service_label']);
    }

    public function test_admin_can_update_hub_with_string_coverage_countries_without_json_error(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'super_admin',
        ]);

        $hub = OverseasHub::create([
            'hub_code' => 'TESTSYD',
            'hub_name' => 'Sydney Test Gateway',
            'country' => 'Australia',
            'location' => 'Sydney Mascot',
            'mode_type' => 'HYBRID',
            'coverage_countries' => ['Australia'],
            'is_active' => true,
        ]);

        // Submit form with raw string 'Australia' just like the web form did
        $response = $this->actingAs($admin)->put(route('international.hubs.update', $hub->id), [
            'code' => 'TESTSYD',
            'name' => 'Sydney Test Gateway Updated',
            'country' => 'Australia',
            'city' => 'Sydney',
            'airport_name' => 'Sydney Kingsford Smith Airport',
            'mode_type' => 'DDP',
            'coverage_countries' => 'Australia, New Zealand',
            'service_routes' => 'Trans-Tasman, Pacific Direct',
            'sort_order' => 5,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('international.hubs.index'));
        $response->assertSessionHas('success');

        $hub->refresh();
        $this->assertEquals('DDP', $hub->mode_type);
        $this->assertEquals(5, $hub->sort_order);
        $this->assertIsArray($hub->coverage_countries);
        $this->assertEquals(['Australia', 'New Zealand'], $hub->coverage_countries);
    }
}
