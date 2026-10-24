<?php

namespace Tests\Feature;

use App\Models\DomesticPartner;
use App\Models\DomesticShipment;
use App\Models\MAWB;
use App\Models\OverseasHub;
use App\Models\PickupRequest;
use App\Models\Shipment;
use App\Models\User;
use App\Services\AutomatedTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomatedTrackingSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    private User $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'user_type' => 'international_admin',
            'role' => 'admin',
        ]);

        $this->customer = User::factory()->create([
            'user_type' => 'customer',
            'role' => 'customer',
        ]);

        $this->partner = User::factory()->create([
            'user_type' => 'partner',
            'role' => 'partner',
        ]);
    }

    public function test_universal_tracking_resolves_via_tracking_hawb_mawb_and_carrier_number(): void
    {
        $hub = OverseasHub::create([
            'hub_name' => 'London Heathrow Gateway',
            'hub_code' => 'LHR',
            'location' => 'London',
            'country' => 'United Kingdom',
            'is_active' => true,
        ]);

        $mawb = MAWB::create([
            'mawb_number' => '176-98765432',
            'airline_name' => 'Emirates SkyCargo',
            'airline_code' => 'EK',
            'origin_airport' => 'KTM',
            'destination_airport' => 'LHR',
            'hub_id' => $hub->id,
            'flight_number' => 'EK-235',
            'status' => 'assigned',
        ]);

        $shipment = Shipment::create([
            'customer_id' => $this->customer->id,
            'tracking_number' => 'NPI-2026-000099-1',
            'hawb_number' => 'UKNP-2026-099',
            'mawb_id' => $mawb->id,
            'mawb_number' => $mawb->mawb_number,
            'current_hub_id' => $hub->id,
            'last_mile_carrier_name' => 'Royal Mail',
            'last_mile_tracking_number' => 'RM123456789GB',
            'service_type' => 'express',
            'shipment_type' => 'international',
            'sender_name' => 'Himalayan Exports',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'London Consignee',
            'receiver_city' => 'London',
            'receiver_country' => 'United Kingdom',
            'actual_weight' => 2.5,
            'chargeable_weight' => 2.5,
            'status' => 'in_transit',
        ]);

        // Test resolution by Netpack tracking number
        $response1 = $this->get(route('tracking.show', 'NPI-2026-000099-1'));
        $response1->assertOk()->assertSee('NPI-2026-000099-1')->assertSee('UKNP-2026-099');

        // Test resolution by regional HAWB number
        $response2 = $this->get(route('tracking.show', 'UKNP-2026-099'));
        $response2->assertOk()->assertSee('NPI-2026-000099-1');

        // Test resolution by Last-Mile carrier waybill
        $response3 = $this->get(route('tracking.show', 'RM123456789GB'));
        $response3->assertOk()->assertSee('NPI-2026-000099-1')->assertSee('RM123456789GB');

        // Test resolution by Master Air Waybill (MAWB)
        $response4 = $this->get(route('tracking.show', '176-98765432'));
        $response4->assertOk()->assertSee('176-98765432');
    }

    public function test_public_tracking_api_returns_clean_privacy_safe_json(): void
    {
        $shipment = Shipment::create([
            'customer_id' => $this->customer->id,
            'tracking_number' => 'NPI-2026-000088-2',
            'hawb_number' => 'USNP-2026-088',
            'service_type' => 'express',
            'shipment_type' => 'international',
            'sender_name' => 'Sender Withheld',
            'sender_phone' => '+977-9841000000',
            'sender_city' => 'Kathmandu',
            'receiver_name' => 'Receiver Name',
            'receiver_phone' => '+1-555-9876543',
            'receiver_city' => 'New York',
            'receiver_country' => 'United States',
            'actual_weight' => 1.5,
            'status' => 'in_transit',
        ]);

        $response = $this->getJson(route('api.v1.track', 'NPI-2026-000088-2'));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tracking_number', 'NPI-2026-000088-2')
            ->assertJsonPath('data.hawb_number', 'USNP-2026-088')
            ->assertJsonPath('data.origin.city', 'Kathmandu')
            ->assertJsonPath('data.destination.city', 'New York');

        // Verify privacy safety: Private phone numbers must not be exposed in public API
        $response->assertJsonMissing(['+977-9841000000', '+1-555-9876543']);
    }

    public function test_mawb_status_cascade_automates_child_shipment_milestones(): void
    {
        $hub = OverseasHub::create([
            'hub_name' => 'Dubai Aviation Hub',
            'hub_code' => 'DXB',
            'location' => 'Dubai',
            'country' => 'United Arab Emirates',
            'is_active' => true,
        ]);

        $mawb = MAWB::create([
            'mawb_number' => '176-11223344',
            'airline_name' => 'Emirates',
            'airline_code' => 'EK',
            'origin_airport' => 'KTM',
            'destination_airport' => 'DXB',
            'hub_id' => $hub->id,
            'flight_number' => 'EK-235',
            'status' => 'assigned',
        ]);

        $shipment = Shipment::create([
            'customer_id' => $this->customer->id,
            'tracking_number' => 'NPI-2026-000077-3',
            'hawb_number' => 'EUNP-2026-077',
            'mawb_id' => $mawb->id,
            'mawb_number' => $mawb->mawb_number,
            'current_hub_id' => $hub->id,
            'service_type' => 'economy',
            'shipment_type' => 'international',
            'status' => 'confirmed',
            'sender_city' => 'Kathmandu',
            'receiver_city' => 'Frankfurt',
            'receiver_country' => 'Germany',
            'actual_weight' => 5.0,
        ]);

        // Updating MAWB to in_transit cascades flight departure to child shipments
        $autoService = app(AutomatedTrackingService::class);
        $result = $autoService->cascadeMawbMilestone($mawb, 'in_transit');

        $this->assertEquals(1, $result['updated_count']);

        $shipment->refresh();
        $this->assertEquals('in_transit', $shipment->status);
        $this->assertEquals('in_transit_airline', $shipment->agency_milestone);

        // Tracking history must contain airline flight departure checkpoint
        $history = $shipment->tracking_history;
        $latest = end($history);
        $this->assertEquals('in_transit_airline', $latest['event_code']);
        $this->assertStringContainsString('Flight EK EK-235', $latest['description']);
    }

    public function test_carrier_webhook_ingests_and_updates_shipment_status(): void
    {
        $shipment = Shipment::create([
            'customer_id' => $this->customer->id,
            'tracking_number' => 'NPI-2026-000066-4',
            'hawb_number' => 'USNP-2026-066',
            'last_mile_carrier_name' => 'FedEx',
            'last_mile_tracking_number' => 'FX9876543210',
            'service_type' => 'express',
            'shipment_type' => 'international',
            'status' => 'in_transit',
            'sender_city' => 'Kathmandu',
            'receiver_city' => 'Chicago',
            'receiver_country' => 'USA',
            'actual_weight' => 2.0,
        ]);

        $payload = [
            'tracking_number' => 'FX9876543210',
            'status' => 'DELIVERED',
            'location' => 'Chicago IL, USA',
            'description' => 'Delivered to front porch. Direct signature confirmed.',
            'timestamp' => now()->toIso8601String(),
        ];

        $response = $this->postJson(route('api.webhooks.carrier', 'fedex'), $payload, $this->carrierWebhookHeaders($payload, 'fedex'));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.milestone', 'delivered');

        $shipment->refresh();
        $this->assertEquals('delivered', $shipment->status);
    }

    public function test_pickup_request_automatically_initializes_tracking(): void
    {
        $pickup = PickupRequest::create([
            'seller_id' => $this->customer->id,
            'pickup_address' => 'Baluwatar, Kathmandu',
            'pickup_ward_no' => '4',
            'pickup_municipality' => 'Kathmandu',
            'pickup_district' => 'Kathmandu',
            'pickup_province' => 'Bagmati',
            'delivery_address' => 'Lakeside, Pokhara',
            'delivery_ward_no' => '6',
            'delivery_municipality' => 'Pokhara',
            'delivery_district' => 'Kaski',
            'delivery_province' => 'Gandaki',
            'scheduled_pickup_time' => now()->addHours(3),
            'items_description' => 'Pashmina Shawls Box',
            'estimated_weight_kg' => 3.5,
            'service_tier' => 'standard',
            'status' => 'pending',
        ]);

        // Verifies tracking number was auto-generated by booted model hook
        $this->assertNotEmpty($pickup->tracking_number);
        $this->assertStringStartsWith('NPD-', $pickup->tracking_number);

        // Verifies status_history contains initial pickup_scheduled milestone
        $this->assertNotEmpty($pickup->status_history);
        $this->assertEquals('pickup_scheduled', $pickup->status_history[0]['event_code']);
    }

    public function test_customer_can_subscribe_to_tracking_milestone_alerts(): void
    {
        $response = $this->post(route('tracking.subscribe'), [
            'tracking_number' => 'NPI-2026-000011-5',
            'email' => 'consignee@london-logistics.test',
            'phone' => '+447911123456',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tracking_subscriptions', [
            'tracking_number' => 'NPI-2026-000011-5',
            'email' => 'consignee@london-logistics.test',
            'is_active' => true,
        ]);
    }

    public function test_domestic_scan_desk_updates_shipment_telemetry(): void
    {
        $partner = DomesticPartner::create([
            'name' => 'Gandaki Pokhara Depot',
            'code' => 'PKR-DEPOT-01',
            'company_name' => 'Gandaki Express Logistics Pvt Ltd',
            'email' => 'pokhara.hub@netpack.test',
            'password' => bcrypt('partner123'),
            'phone' => '9856000001',
            'address' => 'Prithvi Chowk, Pokhara',
            'city' => 'Pokhara',
            'district' => 'Kaski',
            'province' => 'Gandaki',
            'is_active' => true,
        ]);

        $dom = DomesticShipment::create([
            'partner_id' => $partner->id,
            'tracking_number' => 'NPD-2026-000055-5',
            'client_id' => $this->customer->id,
            'service_type' => 'standard',
            'service_name' => 'Standard Express',
            'sender_name' => 'Kathmandu Shipper',
            'sender_phone' => '+977-9841000000',
            'sender_address' => 'Thamel, Kathmandu',
            'sender_city' => 'Kathmandu',
            'receiver_name' => 'Pokhara Consignee',
            'receiver_phone' => '+977-9856000000',
            'receiver_address' => 'Lakeside, Pokhara',
            'receiver_city' => 'Pokhara',
            'receiver_ward' => '3',
            'receiver_zone' => 'Gandaki',
            'weight' => 2.0,
            'base_rate' => 250.00,
            'per_kg_rate' => 50.00,
            'total_amount' => 350.00,
            'status' => 'pending',
        ]);

        $domesticAdmin = User::factory()->create([
            'user_type' => 'domestic_admin',
            'role' => 'admin',
        ]);
        $this->actingAs($domesticAdmin);

        // Scan consignment as arrived at Pokhara Hub
        $response = $this->postJson(route('domestic.manifests.process-scan'), [
            'barcode' => 'NPD-2026-000055-5',
            'action' => 'arrival',
            'location' => 'Pokhara Regional Sorting Hub',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('type', 'shipment');

        $dom->refresh();
        $this->assertEquals('in_transit', $dom->status);
        $this->assertNotEmpty($dom->tracking_history);
    }

    public function test_tracking_shipment_provides_printable_hawb_copy(): void
    {
        $shipment = Shipment::create([
            'customer_id' => $this->customer->id,
            'tracking_number' => 'NPI-2026-HAWBPRINT-1',
            'hawb_number' => 'UKNP-2026-901',
            'service_type' => 'express',
            'shipment_type' => 'international',
            'sender_name' => 'Himalayan Arts',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'London Gallery',
            'receiver_city' => 'London',
            'receiver_country' => 'United Kingdom',
            'actual_weight' => 3.5,
            'chargeable_weight' => 3.5,
            'shipping_cost' => 4500.00,
            'total_amount' => 4500.00,
            'status' => 'in_transit',
        ]);

        // 1. Verify Public Tracking Page contains the Print HAWB action and link
        $trackingPage = $this->get(route('tracking.show', 'NPI-2026-HAWBPRINT-1'));
        $trackingPage->assertOk()
            ->assertSee('Print HAWB')
            ->assertSee(route('tracking.hawb.print', 'NPI-2026-HAWBPRINT-1'));

        // 2. Verify Universal Public HAWB copy loads without authentication
        $this->get(route('tracking.hawb.print', 'NPI-2026-HAWBPRINT-1'))->assertRedirect(route('login'));
        $hawbResponse = $this->actingAs($this->customer)->get(route('tracking.hawb.print', 'NPI-2026-HAWBPRINT-1'));
        $hawbResponse->assertOk()
            ->assertSee('HOUSE AIR WAYBILL')
            ->assertSee('UKNP-2026-901')
            ->assertSee('NPI-2026-HAWBPRINT-1')
            ->assertSee('London Gallery')
            ->assertSee('Print House Air Waybill')
            ->assertDontSee('4500') // Zero charges compliance
            ->assertDontSee('Rs.')
            ->assertDontSee('$');

        // 3. Verify Single-slip popup format works
        $popupResponse = $this->get(route('tracking.hawb.popup', 'NPI-2026-HAWBPRINT-1'));
        $popupResponse->assertOk()
            ->assertSee('HOUSE AIR WAYBILL')
            ->assertSee('NPI-2026-HAWBPRINT-1')
            ->assertSee('Print HAWB Document');
    }

    public function test_domestic_tracking_shipment_provides_printable_waybill_copy(): void
    {
        $partner = DomesticPartner::create([
            'name' => 'Pokhara Depot Hub',
            'code' => 'PKR-DEPOT-99',
            'company_name' => 'Pokhara Courier Pvt Ltd',
            'email' => 'pokhara99.hub@netpack.test',
            'password' => bcrypt('partner123'),
            'phone' => '9856000099',
            'address' => 'Lakeside, Pokhara',
            'city' => 'Pokhara',
            'district' => 'Kaski',
            'province' => 'Gandaki',
            'is_active' => true,
        ]);

        $dom = DomesticShipment::create([
            'partner_id' => $partner->id,
            'tracking_number' => 'NPD-2026-DOMPRINT-2',
            'client_id' => $this->customer->id,
            'service_type' => 'standard',
            'service_name' => 'Standard Delivery',
            'sender_name' => 'Kathmandu Wholesale',
            'sender_phone' => '+977-9841000001',
            'sender_address' => 'Asan, Kathmandu',
            'sender_city' => 'Kathmandu',
            'receiver_name' => 'Pokhara Retail Store',
            'receiver_phone' => '+977-9856000002',
            'receiver_address' => 'Mahendrapool, Pokhara',
            'receiver_city' => 'Pokhara',
            'receiver_ward' => '9',
            'receiver_zone' => 'Gandaki',
            'weight' => 4.0,
            'base_rate' => 300.00,
            'per_kg_rate' => 60.00,
            'total_amount' => 540.00,
            'status' => 'in_transit',
        ]);

        // 1. Verify Domestic Public Tracking Page contains Print Waybill action
        $trackingPage = $this->get(route('tracking.show', 'NPD-2026-DOMPRINT-2'));
        $trackingPage->assertOk()
            ->assertSee('Print Waybill')
            ->assertSee(route('tracking.hawb.print', 'NPD-2026-DOMPRINT-2'));

        // 2. Verify Universal Public Waybill/HAWB copy loads
        $this->get(route('tracking.hawb.print', 'NPD-2026-DOMPRINT-2'))->assertRedirect(route('login'));
        $waybillResponse = $this->actingAs($this->customer)->get(route('tracking.hawb.print', 'NPD-2026-DOMPRINT-2'));
        $waybillResponse->assertOk()
            ->assertSee('OFFICIAL FREIGHT MANIFEST')
            ->assertSee('NPD-2026-DOMPRINT-2')
            ->assertSee('Pokhara Retail Store')
            ->assertSee('Print Domestic Consignment Note');
    }
}
