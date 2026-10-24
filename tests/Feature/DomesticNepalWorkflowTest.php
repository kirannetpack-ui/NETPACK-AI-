<?php

namespace Tests\Feature;

use App\Models\Bag;
use App\Models\DeliveryReminder;
use App\Models\DomesticPartner;
use App\Models\LogisticsService;
use App\Models\Manifest;
use App\Models\ManifestShipment;
use App\Models\ReminderLog;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomesticNepalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $partnerUser;
    private DomesticPartner $partner;
    private User $downstreamPartnerUser;
    private DomesticPartner $downstreamPartner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Kiran Admin',
            'user_type' => User::TYPE_SUPER_ADMIN,
            'role' => 'admin',
            'verification_status' => 'approved',
        ]);

        $this->partnerUser = User::factory()->create([
            'name' => 'Gandaki Express Pokhara',
            'user_type' => User::TYPE_PARTNER,
            'role' => 'admin',
            'email' => 'pokhara.hub@netpack.test',
            'verification_status' => 'approved',
        ]);

        $this->partner = DomesticPartner::create([
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
            'kyc_verified' => true,
        ]);

        $this->downstreamPartnerUser = User::factory()->create([
            'name' => 'Sudurpashchim Dhangadhi Depot',
            'user_type' => User::TYPE_PARTNER,
            'role' => 'admin',
            'email' => 'dhangadhi.hub@netpack.test',
            'verification_status' => 'approved',
        ]);

        $this->downstreamPartner = DomesticPartner::create([
            'name' => 'Sudurpashchim Express',
            'code' => 'DHI-DEPOT-07',
            'company_name' => 'Sudurpashchim Cargo Pvt Ltd',
            'email' => 'dhangadhi.hub@netpack.test',
            'password' => bcrypt('partner123'),
            'phone' => '9858000002',
            'address' => 'Main Road, Dhangadhi',
            'city' => 'Dhangadhi',
            'district' => 'Kailali',
            'province' => 'Sudurpashchim',
            'is_active' => true,
            'kyc_verified' => true,
        ]);

        LogisticsService::firstOrCreate(
            ['code' => 'standard'],
            [
                'name' => 'Domestic Standard Overland',
                'category' => 'domestic',
                'transit_time_hours' => 24.0,
                'transit_time_days' => 1.0,
                'reminder_intervals' => [50, 75],
                'base_rate' => 150.00,
                'per_kg_rate' => 30.00,
                'is_active' => true,
            ]
        );
    }

    public function test_domestic_manifest_creation_automatically_schedules_partner_delivery_reminders(): void
    {
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER, 'verification_status' => 'approved']);
        $shipment = Shipment::create([
            'hawb_number' => 'HAWB-NEP-KTM-PKR-001',
            'tracking_number' => 'NPD-KTM-PKR-001',
            'customer_id' => $customer->id,
            'sender_name' => 'Kathmandu Merchant',
            'sender_phone' => '9841000001',
            'sender_address' => 'New Road, Kathmandu',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'Pokhara Retailer',
            'receiver_phone' => '9856000002',
            'receiver_address' => 'Lakeside, Pokhara',
            'receiver_city' => 'Pokhara',
            'receiver_country' => 'Nepal',
            'service_type' => 'standard',
            'shipment_type' => 'domestic',
            'actual_weight' => 2.5,
            'chargeable_weight' => 2.5,
            'shipping_cost' => 225.00,
            'total_amount' => 225.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('domestic.manifests.store'), [
            'load_type' => 'consolidated',
            'partner_id' => $this->partnerUser->id,
            'origin_city' => 'Kathmandu',
            'destination_city' => 'Pokhara',
            'delivery_type' => 'door_delivery',
            'payment_status' => 'pending',
            'bags' => [[
                'bag_type' => 'consolidated',
                'weight' => 2.5,
                'shipments' => [$shipment->id],
            ]],
        ]);

        $response->assertRedirect();

        $manifest = Manifest::firstOrFail();
        $this->assertSame('Kathmandu', $manifest->origin_city);
        $this->assertSame('Pokhara', $manifest->destination_city);

        // Verify automated partner SLA delivery reminders were scheduled in database
        $reminders = DeliveryReminder::where('manifest_id', $manifest->id)->get();
        $this->assertNotEmpty($reminders, 'Partner SLA delivery reminders should be scheduled for the manifest');
        $this->assertTrue(
            $reminders->contains(fn ($r) => $r->service_tier === 'domestic_manifest'),
            'Reminders must contain domestic_manifest service tier reminders'
        );
    }

    public function test_domestic_arrival_notice_form_renders_nepal_hubs_and_checklist(): void
    {
        $manifest = $this->createTestManifest();

        $response = $this->actingAs($this->admin)->get(route('domestic.manifests.arrival-notice', $manifest->id));
        $response->assertOk();
        $response->assertSee('Domestic Hub Inbound Arrival Notice');
        $response->assertSee('Pokhara Regional Depot (Gandaki)');
        $response->assertSee('Kathmandu Central Sortation Gateway');
    }

    public function test_domestic_arrival_notice_whole_manifest_marks_all_arrived(): void
    {
        $manifest = $this->createTestManifest();
        $manifestShipment = $manifest->shipments->first();

        $response = $this->actingAs($this->admin)->post(route('domestic.manifests.process-arrival-notice', $manifest->id), [
            'arrival_mode' => 'whole',
            'arrival_date' => now()->toDateString(),
            'arrival_time' => '10:30',
            'arrival_location' => 'Pokhara Regional Hub (Gandaki Province)',
            'operator_name' => 'Ramesh Inbound Officer',
            'remarks' => 'Direct night truck arrived on schedule, bag seal intact',
        ]);

        $response->assertRedirect(route('domestic.manifests.show', $manifest->id));

        $manifestShipment->refresh();
        $this->assertSame('arrived', $manifestShipment->arrival_status);
        $this->assertSame('Pokhara Regional Hub (Gandaki Province)', $manifestShipment->arrived_location);
        $this->assertSame('Ramesh Inbound Officer', $manifestShipment->staff_name);

        $shipment = $manifestShipment->shipment;
        $this->assertSame('in_transit', $shipment->status);
        $this->assertSame('Pokhara Regional Hub (Gandaki Province)', $shipment->current_location);
    }

    public function test_domestic_arrival_notice_partial_with_non_arrival_remarks(): void
    {
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER, 'verification_status' => 'approved']);
        $s1 = $this->makeShipment($customer, 'HAWB-PARTIAL-001', 'NPD-PARTIAL-001');
        $s2 = $this->makeShipment($customer, 'HAWB-PARTIAL-002', 'NPD-PARTIAL-002');

        $manifest = Manifest::create([
            'manifest_number' => 'MNF-NEP-PARTIAL',
            'load_type' => 'consolidated',
            'created_by' => $this->admin->id,
            'partner_id' => $this->partnerUser->id,
            'origin_city' => 'Kathmandu',
            'destination_city' => 'Pokhara',
            'total_shipments' => 2,
            'total_weight' => 5.0,
            'status' => 'dispatched',
        ]);

        $line1 = ManifestShipment::create([
            'manifest_id' => $manifest->id,
            'shipment_id' => $s1->id,
            'partner_id' => $this->partnerUser->id,
            'arrival_status' => 'pending',
        ]);

        $line2 = ManifestShipment::create([
            'manifest_id' => $manifest->id,
            'shipment_id' => $s2->id,
            'partner_id' => $this->partnerUser->id,
            'arrival_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('domestic.manifests.process-arrival-notice', $manifest->id), [
            'arrival_mode' => 'partial',
            'arrival_date' => now()->toDateString(),
            'arrival_time' => '11:15',
            'arrival_location' => 'Pokhara Regional Hub (Gandaki Province)',
            'operator_name' => 'Ramesh Inbound Officer',
            'arrived_shipment_ids' => [$line1->id],
            'non_arrival_remarks' => [
                $line2->id => 'Box short-shipped from Kathmandu depot; pending next shuttle',
            ],
        ]);

        $response->assertRedirect(route('domestic.manifests.show', $manifest->id));

        $line1->refresh();
        $this->assertSame('arrived', $line1->arrival_status);
        $this->assertSame('Pokhara Regional Hub (Gandaki Province)', $line1->arrived_location);

        $line2->refresh();
        $this->assertSame('non_arrival', $line2->arrival_status);
        $this->assertSame('Box short-shipped from Kathmandu depot; pending next shuttle', $line2->non_arrival_remarks);
    }

    public function test_domestic_scan_desk_records_barcode_and_qr_telemetry(): void
    {
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER, 'verification_status' => 'approved']);
        $shipment = $this->makeShipment($customer, 'HAWB-SCAN-NEP-88', 'NPD-SCAN-NEP-88');

        $response = $this->actingAs($this->admin)->get(route('domestic.manifests.scan'));
        $response->assertOk();
        $response->assertSee('Domestic Scan Desk: Nepal-Wide Inbound');
        $response->assertSee('Scanner Ready');

        $scanResponse = $this->actingAs($this->admin)->postJson(route('domestic.manifests.process-scan'), [
            'barcode' => 'NPD-SCAN-NEP-88',
            'action' => 'arrival',
            'location' => 'Biratnagar Transit Hub (Koshi Province)',
            'status_note' => 'Scanned at conveyor belt inbound scan',
        ]);

        $scanResponse->assertOk();
        $scanResponse->assertJsonFragment(['success' => true]);

        $shipment->refresh();
        $this->assertSame('Biratnagar Transit Hub (Koshi Province)', $shipment->current_location);
        $this->assertSame('in_transit', $shipment->status);
    }

    public function test_bulk_re_manifest_transships_arrived_shipments_to_downstream_nepal_depot(): void
    {
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER, 'verification_status' => 'approved']);
        $s1 = $this->makeShipment($customer, 'HAWB-TRANS-01', 'NPD-TRANS-01');
        $s2 = $this->makeShipment($customer, 'HAWB-TRANS-02', 'NPD-TRANS-02');

        $initialManifest = Manifest::create([
            'manifest_number' => 'MNF-ORIGIN-KTM-PKR',
            'load_type' => 'consolidated',
            'created_by' => $this->admin->id,
            'partner_id' => $this->partnerUser->id,
            'origin_city' => 'Kathmandu',
            'destination_city' => 'Pokhara',
            'total_shipments' => 2,
            'total_weight' => 6.0,
            'status' => 'arrived',
        ]);

        ManifestShipment::create([
            'manifest_id' => $initialManifest->id,
            'shipment_id' => $s1->id,
            'partner_id' => $this->partnerUser->id,
            'arrival_status' => 'arrived',
        ]);

        ManifestShipment::create([
            'manifest_id' => $initialManifest->id,
            'shipment_id' => $s2->id,
            'partner_id' => $this->partnerUser->id,
            'arrival_status' => 'arrived',
        ]);

        // Bulk re-manifest from Pokhara forward to Dhangadhi
        $response = $this->actingAs($this->admin)->post(route('domestic.manifests.bulk-re-manifest'), [
            'original_manifest_id' => $initialManifest->id,
            'shipment_ids' => [$s1->id, $s2->id],
            'partner_id' => $this->downstreamPartnerUser->id,
            'origin_city' => 'Pokhara',
            'destination_city' => 'Dhangadhi',
            'load_type' => 'consolidated',
            'notes' => 'Transshipped from Pokhara Gandaki Depot to Sudurpashchim Dhangadhi Hub',
        ]);

        $response->assertRedirect();

        // New re-manifest should exist
        $reManifest = Manifest::where('manifest_number', '!=', 'MNF-ORIGIN-KTM-PKR')->latest('id')->firstOrFail();
        $this->assertSame('consolidated', $reManifest->load_type);
        $this->assertSame('Pokhara', $reManifest->origin_city);
        $this->assertSame('Dhangadhi', $reManifest->destination_city);
        $this->assertSame(2, $reManifest->total_shipments);

        // Re-manifest shipments and bag
        $this->assertDatabaseHas('manifest_shipments', [
            'manifest_id' => $reManifest->id,
            'shipment_id' => $s1->id,
            'partner_id' => $this->downstreamPartnerUser->id,
        ]);

        $this->assertDatabaseHas('manifest_shipments', [
            'manifest_id' => $reManifest->id,
            'shipment_id' => $s2->id,
            'partner_id' => $this->downstreamPartnerUser->id,
        ]);

        // Reminders scheduled for the new re-manifest
        $this->assertDatabaseHas('delivery_reminders', [
            'manifest_id' => $reManifest->id,
        ]);
    }

    public function test_manual_partner_delivery_reminder_dispatch(): void
    {
        $manifest = $this->createTestManifest();

        $response = $this->actingAs($this->admin)
            ->from(route('domestic.manifests.show', $manifest->id))
            ->post(route('domestic.manifests.send-partner-reminder', $manifest->id), [
                'note' => 'Express load arrived at depot; please dispatch immediate delivery vans.',
            ]);

        $response->assertRedirect(route('domestic.manifests.show', $manifest->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reminder_logs', [
            'reminder_type' => 'partner',
        ]);
    }

    public function test_strict_hawb_price_absence_rule_across_domestic_and_international(): void
    {
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER, 'verification_status' => 'approved']);

        $domesticShipment = Shipment::create([
            'hawb_number' => 'HAWB-DOM-TEST-99',
            'tracking_number' => 'NPD-DOM-TEST-99',
            'customer_id' => $customer->id,
            'sender_name' => 'Kathmandu Trader',
            'sender_phone' => '9841112233',
            'sender_address' => 'Baluwatar',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'Butwal Store',
            'receiver_phone' => '9857112233',
            'receiver_address' => 'Traffic Chowk',
            'receiver_city' => 'Butwal',
            'receiver_country' => 'Nepal',
            'service_type' => 'standard',
            'shipment_type' => 'domestic',
            'actual_weight' => 4.0,
            'chargeable_weight' => 4.0,
            'shipping_cost' => 485.50,
            'total_amount' => 485.50,
            'status' => 'in_transit',
        ]);

        $intlShipment = Shipment::create([
            'hawb_number' => 'HAWB-INTL-TEST-99',
            'tracking_number' => 'NPI-INTL-TEST-99',
            'customer_id' => $customer->id,
            'sender_name' => 'Nepal Handicrafts',
            'sender_phone' => '9841998877',
            'sender_address' => 'Patan',
            'sender_city' => 'Lalitpur',
            'sender_country' => 'Nepal',
            'receiver_name' => 'London Importer',
            'receiver_phone' => '+44 20 7946 0991',
            'receiver_address' => '10 Downing St',
            'receiver_city' => 'London',
            'receiver_country' => 'United Kingdom',
            'service_type' => 'express',
            'shipment_type' => 'international',
            'actual_weight' => 5.0,
            'chargeable_weight' => 5.0,
            'shipping_cost' => 9500.00,
            'total_amount' => 9500.00,
            'status' => 'in_transit',
        ]);

        // 1. Check Domestic HAWB View
        $domResponse = $this->actingAs($this->admin)->get(route('hawb.domestic', $domesticShipment->id));
        $domResponse->assertOk();
        $domContent = $domResponse->getContent();

        // Must NOT contain price or currency
        $this->assertStringNotContainsString('485.50', $domContent, 'Domestic HAWB must not show shipping cost');
        $this->assertStringNotContainsString('NPR', $domContent, 'Domestic HAWB must not show currency code NPR');
        $this->assertStringNotContainsString('Rs.', $domContent, 'Domestic HAWB must not show currency prefix Rs.');
        $this->assertStringNotContainsString('Freight Charge', $domContent, 'Domestic HAWB must not show Freight Charge label');
        $this->assertStringNotContainsString('Total Amount', $domContent, 'Domestic HAWB must not show Total Amount label');

        // 2. Check International HAWB View
        $intlResponse = $this->actingAs($this->admin)->get(route('hawb.international', $intlShipment->id));
        $intlResponse->assertOk();
        $intlContent = $intlResponse->getContent();

        // Must NOT contain price or currency
        $this->assertStringNotContainsString('9500.00', $intlContent, 'International HAWB must not show shipping cost');
        $this->assertStringNotContainsString('USD', $intlContent, 'International HAWB must not show USD currency');
        $this->assertStringNotContainsString('NPR', $intlContent, 'International HAWB must not show NPR currency');
        $this->assertStringNotContainsString('Freight Charge', $intlContent, 'International HAWB must not show Freight Charge label');
        $this->assertStringNotContainsString('Total Amount', $intlContent, 'International HAWB must not show Total Amount label');

        // 3. Check Print-popup View
        $popupDom = $this->actingAs($this->admin)->get(route('hawb.print', ['id' => $domesticShipment->id, 'type' => 'domestic']));
        $popupDom->assertOk();
        $popupContent = $popupDom->getContent();
        $this->assertStringNotContainsString('485.50', $popupContent);
        $this->assertStringNotContainsString('NPR', $popupContent);
        $this->assertStringNotContainsString('Rs.', $popupContent);
    }

    private function createTestManifest(): Manifest
    {
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER, 'verification_status' => 'approved']);
        $shipment = $this->makeShipment($customer, 'HAWB-TEST-001', 'NPD-TEST-001');

        $manifest = Manifest::create([
            'manifest_number' => 'MNF-TEST-' . strtoupper(substr(uniqid(), 0, 8)),
            'load_type' => 'consolidated',
            'created_by' => $this->admin->id,
            'partner_id' => $this->partnerUser->id,
            'origin_city' => 'Kathmandu',
            'destination_city' => 'Pokhara',
            'total_shipments' => 1,
            'total_weight' => 3.0,
            'status' => 'dispatched',
        ]);

        ManifestShipment::create([
            'manifest_id' => $manifest->id,
            'shipment_id' => $shipment->id,
            'partner_id' => $this->partnerUser->id,
            'arrival_status' => 'pending',
        ]);

        return $manifest;
    }

    private function makeShipment(User $customer, string $hawb, string $tracking): Shipment
    {
        return Shipment::create([
            'hawb_number' => $hawb,
            'tracking_number' => $tracking,
            'customer_id' => $customer->id,
            'sender_name' => 'Sender',
            'sender_phone' => '9800000001',
            'sender_address' => 'Kathmandu',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'Receiver',
            'receiver_phone' => '9800000002',
            'receiver_address' => 'Pokhara',
            'receiver_city' => 'Pokhara',
            'receiver_country' => 'Nepal',
            'service_type' => 'standard',
            'shipment_type' => 'domestic',
            'actual_weight' => 1.5,
            'chargeable_weight' => 1.5,
            'shipping_cost' => 150.00,
            'total_amount' => 150.00,
            'status' => 'in_transit',
        ]);
    }
}
