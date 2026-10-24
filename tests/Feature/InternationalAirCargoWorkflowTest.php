<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\AgencyStaff;
use App\Models\LastMileCarrier;
use App\Models\MAWB;
use App\Models\Manifest;
use App\Models\OverseasHub;
use App\Models\Shipment;
use App\Models\User;
use App\Services\InternationalManifestService;
use App\Services\ShipmentScanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InternationalAirCargoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private OverseasHub $dxbHub;
    private Agency $dxbAgency;
    private MAWB $mawb;
    private LastMileCarrier $canpar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'role' => 'admin',
            'email' => 'intl_admin@netpack.com',
        ]);

        $this->dxbHub = OverseasHub::create([
            'name' => 'DUBAI (DXB) - GULF & WORLDWIDE GATEWAY',
            'code' => 'DXB',
            'country' => 'United Arab Emirates',
            'city' => 'Dubai',
            'airport_name' => 'Dubai Cargo Village',
            'mode_type' => 'HYBRID',
            'coverage_countries' => ['AE', 'SA', 'QA', 'KW', 'BH', 'OM', 'CA'],
            'service_routes' => ['Gulf Express', 'UPS Worldwide Crossing', 'Canada DDP (Direct to Toronto)'],
            'is_active' => true,
        ]);

        $this->dxbAgency = Agency::create([
            'hub_id' => $this->dxbHub->id,
            'name' => 'Dubai Express Clearance Agency',
            'code' => 'DXB-EXP',
            'email' => 'ops@dxb-agency.ae',
            'notification_emails' => ['cargo@dxb-agency.ae', 'manifest@dxb-agency.ae'],
            'city' => 'Dubai',
            'country' => 'United Arab Emirates',
            'is_active' => true,
        ]);

        $this->mawb = MAWB::create([
            'mawb_number' => '176-99001122',
            'airline_name' => 'Emirates Airlines',
            'airline_code' => 'EK',
            'origin_airport' => 'KTM',
            'destination_airport' => 'DXB',
            'hub_id' => $this->dxbHub->id,
            'flight_number' => 'EK-565',
            'flight_date' => now()->toDateString(),
            'status' => 'unused',
        ]);

        $this->canpar = LastMileCarrier::create([
            'name' => 'Canpar Express',
            'code' => 'CANPAR',
            'hub_id' => $this->dxbHub->id,
            'country' => 'Canada',
            'service_mode' => 'Canada DDP Ground Delivery (Toronto)',
            'tracking_url_template' => 'https://www.canpar.com/en/track/tracking.jsp?track_number={tracking_number}',
            'is_active' => true,
        ]);
    }

    public function test_international_hubs_can_be_viewed_and_created(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('international.hubs.index'));
        $response->assertOk();
        $response->assertSee('DUBAI (DXB)');

        $newHubData = [
            'code' => 'FRA',
            'name' => 'FRANKFURT (FRA) - CENTRAL EUROPEAN GATEWAY',
            'country' => 'Germany',
            'city' => 'Frankfurt',
            'mode_type' => 'DDP',
            'sort_order' => 5,
            'is_active' => '1',
        ];

        $postResponse = $this->post(route('international.hubs.store'), $newHubData);
        $postResponse->assertRedirect(route('international.hubs.index'));

        $this->assertDatabaseHas('overseas_hubs', [
            'hub_code' => 'FRA',
            'country' => 'Germany',
        ]);
    }

    public function test_agency_format_settings_customizes_manifest_and_datasheet_schemas(): void
    {
        $this->actingAs($this->admin);

        $customFields = [
            'manifest_fields' => [
                'hawb_number' => ['enabled' => '1', 'label' => 'House Airway Bill'],
                'receiver_name' => ['enabled' => '1', 'label' => 'Consignee Full Name'],
                'actual_weight' => ['enabled' => '1', 'label' => 'Gross Kilos'],
            ],
            'datasheet_fields' => [
                'tracking_number' => ['enabled' => '1', 'label' => 'Netpack Barcode'],
                'receiver_tax_id' => ['enabled' => '1', 'label' => 'Consignee VAT / Tax'],
                'last_mile_carrier_name' => ['enabled' => '1', 'label' => 'Last Mile Forwarder'],
            ],
        ];

        $res = $this->post(route('international.agencies.update-format-settings', $this->dxbAgency->id), $customFields);
        $res->assertRedirect(route('international.agencies.format-settings', $this->dxbAgency->id));

        $this->dxbAgency->refresh();
        $this->assertEquals('House Airway Bill', $this->dxbAgency->getManifestFields()['hawb_number']);
        $this->assertEquals('Consignee VAT / Tax', $this->dxbAgency->getDataSheetFields()['receiver_tax_id']);
    }

    public function test_manifest_creation_automatically_binds_unused_mawb_and_packages(): void
    {
        $this->actingAs($this->admin);

        $customer = User::factory()->create(['user_type' => 'customer']);

        // Create 2 packaged shipments
        $shipment1 = Shipment::create([
            'tracking_number' => 'NPI-2026-DXB-001',
            'hawb_number' => 'HAWB-DXB-001',
            'customer_id' => $customer->id,
            'shipment_type' => 'international',
            'service_type' => 'economy',
            'hub_id' => $this->dxbHub->id,
            'current_agency_id' => $this->dxbAgency->id,
            'sender_name' => 'Kathmandu Exporter',
            'receiver_name' => 'Dubai Consignee LLC',
            'receiver_city' => 'Dubai',
            'receiver_country' => 'United Arab Emirates',
            'actual_weight' => 5.5,
            'chargeable_weight' => 6.0,
            'status' => 'packaging_completed',
            'agency_milestone' => 'packaging_completed',
            'customs_mode' => 'DDP',
        ]);

        $shipment2 = Shipment::create([
            'tracking_number' => 'NPI-2026-DXB-002',
            'hawb_number' => 'HAWB-DXB-002',
            'customer_id' => $customer->id,
            'shipment_type' => 'international',
            'service_type' => 'economy',
            'hub_id' => $this->dxbHub->id,
            'current_agency_id' => $this->dxbAgency->id,
            'sender_name' => 'Himalayan Handicrafts',
            'receiver_name' => 'Toronto Canadian Importer',
            'receiver_city' => 'Toronto',
            'receiver_country' => 'Canada',
            'actual_weight' => 10.0,
            'chargeable_weight' => 10.0,
            'status' => 'packaging_completed',
            'agency_milestone' => 'packaging_completed',
            'customs_mode' => 'DDP',
            'last_mile_carrier_name' => 'Canpar Express',
        ]);

        $manifestData = [
            'hub_id' => $this->dxbHub->id,
            'agency_id' => $this->dxbAgency->id,
            'mawb_id' => $this->mawb->id,
            'service_type' => 'economy',
            'flight_number' => 'EK-565',
            'flight_date' => now()->toDateString(),
            'last_mile_carrier_name' => 'Canpar Express',
            'shipment_ids' => [$shipment1->id, $shipment2->id],
        ];

        $response = $this->post(route('international.manifests.store'), $manifestData);

        $manifest = Manifest::where('mawb_id', $this->mawb->id)->first();
        $this->assertNotNull($manifest);
        $response->assertRedirect(route('international.manifests.show', $manifest->id));

        // Verify MAWB is marked assigned
        $this->mawb->refresh();
        $this->assertEquals('assigned', $this->mawb->status);
        $this->assertEquals($manifest->id, $this->mawb->assigned_manifest_id);

        // Verify manifest contains 2 shipments
        $this->assertEquals(2, $manifest->shipments()->count());
        $this->assertEquals(15.5, (float)$manifest->total_weight);

        // Verify shipments moved to in_transit
        $shipment1->refresh();
        $this->assertEquals('in_transit', $shipment1->status);
        $this->assertEquals('airline_departed', $shipment1->agency_milestone);
    }

    public function test_comprehensive_data_sheet_generation_and_csv_export(): void
    {
        $this->actingAs($this->admin);

        $service = app(InternationalManifestService::class);
        $customer = User::factory()->create(['user_type' => 'customer']);

        $shipment = Shipment::create([
            'tracking_number' => 'NPI-DATA-001',
            'hawb_number' => 'HAWB-DATA-001',
            'customer_id' => $customer->id,
            'shipment_type' => 'international',
            'service_type' => 'economy',
            'sender_name' => 'Kathmandu Shipper',
            'receiver_name' => 'Canadian Consignee',
            'receiver_city' => 'Toronto',
            'receiver_country' => 'Canada',
            'actual_weight' => 8.0,
            'customs_mode' => 'DDP',
            'last_mile_carrier_name' => 'Canpar Express',
            'last_mile_tracking_number' => 'CANPAR-TOR-9988',
        ]);

        $manifest = $service->createManifest([
            'hub_id' => $this->dxbHub->id,
            'agency_id' => $this->dxbAgency->id,
            'mawb_id' => $this->mawb->id,
            'service_type' => 'economy',
            'flight_number' => 'EK-565',
            'flight_date' => now()->toDateString(),
        ], [$shipment->id], $this->admin);

        // Test HTML Data Sheet view
        $viewRes = $this->get(route('international.manifests.data-sheet', $manifest->id));
        $viewRes->assertOk();
        $viewRes->assertSee('Data Sheet: ' . $manifest->manifest_number);
        $viewRes->assertSee('NPI-DATA-001');

        // Test CSV download stream
        $csvRes = $this->get(route('international.manifests.data-sheet', [$manifest->id, 'export' => 'csv']));
        $csvRes->assertOk();
        $this->assertTrue(str_contains($csvRes->headers->get('content-type'), 'text/csv'));
    }

    public function test_one_click_manifest_email_dispatch_to_agency_inboxes(): void
    {
        Mail::fake();

        $this->actingAs($this->admin);
        $customer = User::factory()->create(['user_type' => 'customer']);
        $shipment = Shipment::create([
            'tracking_number' => 'NPI-DISPATCH-001',
            'customer_id' => $customer->id,
            'shipment_type' => 'international',
            'service_type' => 'economy',
            'receiver_country' => 'UAE',
        ]);

        $service = app(InternationalManifestService::class);
        $manifest = $service->createManifest([
            'hub_id' => $this->dxbHub->id,
            'agency_id' => $this->dxbAgency->id,
            'mawb_id' => $this->mawb->id,
            'service_type' => 'economy',
            'flight_number' => 'EK-565',
            'flight_date' => now()->toDateString(),
        ], [$shipment->id], $this->admin);

        $res = $this->post(route('international.manifests.send-agency-email', $manifest->id), [
            'custom_notes' => 'Please clear customs as soon as EK-565 lands.',
        ]);

        $res->assertRedirect(route('international.manifests.show', $manifest->id));

        $manifest->refresh();
        $this->assertNotNull($manifest->agency_emails_sent_at);
        $this->assertContains('ops@dxb-agency.ae', $manifest->agency_emails_sent_to);
        $this->assertContains('cargo@dxb-agency.ae', $manifest->agency_emails_sent_to);
    }

    public function test_agency_staff_arrival_notice_whole_and_partial_with_remarks(): void
    {
        $staffUser = User::factory()->create([
            'name' => 'Rashid Al-Maktoum',
            'user_type' => 'staff',
            'role' => 'admin',
            'email' => 'rashid@agency.com',
        ]);

        $this->actingAs($staffUser);

        $service = app(InternationalManifestService::class);
        $customer = User::factory()->create(['user_type' => 'customer']);

        $s1 = Shipment::create([
            'tracking_number' => 'ARR-PKG-001',
            'customer_id' => $customer->id,
            'shipment_type' => 'international',
            'receiver_country' => 'UAE',
            'actual_weight' => 2.0,
            'status' => 'in_transit',
        ]);
        $s2 = Shipment::create([
            'tracking_number' => 'ARR-PKG-002',
            'customer_id' => $customer->id,
            'shipment_type' => 'international',
            'receiver_country' => 'UAE',
            'actual_weight' => 3.5,
            'status' => 'in_transit',
        ]);

        $manifest = $service->createManifest([
            'hub_id' => $this->dxbHub->id,
            'agency_id' => $this->dxbAgency->id,
            'mawb_id' => $this->mawb->id,
            'service_type' => 'economy',
        ], [$s1->id, $s2->id], $this->admin);

        // Test Partial Arrival Notice: s1 arrived, s2 missing/short-landed with remarks
        $noticeData = [
            'arrival_mode' => 'partial',
            'arrival_date' => '2026-09-10',
            'arrival_time' => '14:30',
            'arrival_location' => 'Dubai Cargo Village DAFZA',
            'arrived_shipment_ids' => [$s1->id], // s2 not included
            'non_arrival_remarks' => [
                $s2->id => 'Box short-landed; airline notified for search',
            ],
        ];

        $res = $this->post(route('agency.manifests.process-arrival-notice', $manifest->id), $noticeData);
        $res->assertRedirect(route('agency.manifests.index'));

        // Check manifest shipment arrival records
        $item1 = $manifest->shipments()->where('shipment_id', $s1->id)->first();
        $this->assertEquals('arrived', $item1->arrival_status);
        $this->assertEquals('Dubai Cargo Village DAFZA', $item1->arrived_location);
        $this->assertEquals('Rashid Al-Maktoum', $item1->staff_name);

        $item2 = $manifest->shipments()->where('shipment_id', $s2->id)->first();
        $this->assertEquals('not_arrived', $item2->arrival_status);
        $this->assertEquals('Box short-landed; airline notified for search', $item2->non_arrival_remarks);

        // Check shipment 1 milestone updated
        $s1->refresh();
        $this->assertEquals('arrival_notice', $s1->agency_milestone);
    }

    public function test_qr_box_scan_captures_instant_telemetry(): void
    {
        $staffUser = User::factory()->create([
            'name' => 'Tariq Scanner',
            'user_type' => 'staff',
            'role' => 'admin',
        ]);
        $this->actingAs($staffUser);

        $customer = User::factory()->create(['user_type' => 'customer']);
        $shipment = Shipment::create([
            'tracking_number' => 'QR-TEST-BOX-77',
            'hawb_number' => 'HAWB-QR-77',
            'customer_id' => $customer->id,
            'shipment_type' => 'international',
            'receiver_country' => 'UAE',
            'status' => 'in_transit',
        ]);

        $scanData = [
            'hawb_number' => 'HAWB-QR-77',
            'action' => 'arrival',
            'location' => 'Dubai Hub Inbound Bay #4',
            'status_note' => 'Scanned at terminal conveyor',
        ];

        $response = $this->postJson(route('agency.process-scan'), $scanData);
        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $shipment->refresh();
        $this->assertEquals('arrival_notice', $shipment->agency_milestone);
        $this->assertEquals('Dubai Hub Inbound Bay #4', $shipment->current_location);
    }

    public function test_public_tracking_displays_express_vs_economy_and_last_mile_handover(): void
    {
        $customer = User::factory()->create(['user_type' => 'customer']);

        $shipment = Shipment::create([
            'tracking_number' => 'NPI-CAN-999',
            'hawb_number' => 'HAWB-CAN-999',
            'customer_id' => $customer->id,
            'shipment_type' => 'international',
            'service_type' => 'economy',
            'hub_id' => $this->dxbHub->id,
            'current_agency_id' => $this->dxbAgency->id,
            'mawb_id' => $this->mawb->id,
            'sender_name' => 'Nepal Shipper',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'Toronto Consignee',
            'receiver_city' => 'Toronto',
            'receiver_country' => 'Canada',
            'customs_mode' => 'DDP',
            'last_mile_carrier_id' => $this->canpar->id,
            'last_mile_carrier_name' => 'Canpar Express',
            'last_mile_tracking_number' => 'CANPAR-TOR-12345',
            'agency_milestone' => 'handed_over_last_mile',
            'status' => 'out_for_delivery',
        ]);

        $response = $this->get(route('tracking.public', ['tracking_number' => $shipment->tracking_number]));
        $response->assertOk();
        $response->assertSee('ECONOMY AIR-CARGO');
        $response->assertSee('DXB (DDP)');
        $response->assertSee('176-99001122');
        $response->assertSee('Canpar Express');
        $response->assertSee('CANPAR-TOR-12345');
        $response->assertSee('Track on Canpar Express Portal');
    }
}
