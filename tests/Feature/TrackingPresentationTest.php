<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_tracking_is_privacy_safe(): void
    {
        $shipment = $this->createShipment();

        $this->get(route('tracking.show', $shipment->tracking_number))
            ->assertOk()
            ->assertSee('NPI-2026-000101-7')
            ->assertSee('In Transit')
            ->assertSee('Kathmandu, Nepal')
            ->assertSee('New York, United States')
            ->assertDontSee('9800000000')
            ->assertDontSee('555-0100')
            ->assertDontSee('Preview Street')
            ->assertDontSee('$92.00')
            ->assertDontSee('View HAWB');

        $this->actingAs($shipment->customer)
            ->get(route('shipments.show', $shipment))
            ->assertOk()
            ->assertSee('NPI-2026-000101-7')
            ->assertDontSee('9800000000');
    }

    public function test_admin_can_open_shipment_list_and_private_details(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'super_admin',
            'verification_status' => 'approved',
            'registration_completed' => true,
            'password_changed' => true,
        ]);
        $shipment = $this->createShipment();

        $this->actingAs($admin)
            ->get(route('admin.shipments.index'))
            ->assertOk()
            ->assertSee($shipment->tracking_number);

        $this->actingAs($admin)
            ->get(route('admin.shipments.show', $shipment->id))
            ->assertOk()
            ->assertSee('9800000000')
            ->assertSee('Preview Street')
            ->assertSee('Update Tracking');
    }

    public function test_public_tracking_resolves_via_hawb_number(): void
    {
        $shipment = $this->createShipment();

        $this->get(route('tracking.show', 'USNP-2026-101'))
            ->assertOk()
            ->assertSee('NPI-2026-000101-7')
            ->assertSee('USNP-2026-101')
            ->assertSee('In Transit');
    }

    public function test_international_admin_can_open_shipment_details_and_view_hawb(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'international_admin',
            'verification_status' => 'approved',
            'registration_completed' => true,
        ]);
        $shipment = $this->createShipment();

        $this->actingAs($admin)
            ->get(route('international.shipments.show', $shipment->id))
            ->assertOk()
            ->assertSee($shipment->hawb_number)
            ->assertSee($shipment->tracking_number);

        $this->actingAs($admin)
            ->get(route('hawb.international', $shipment->id))
            ->assertOk()
            ->assertSee($shipment->hawb_number)
            ->assertSee('HOUSE AIR WAYBILL');
    }

    public function test_ecommerce_seeder_and_order_tracking_lookup(): void
    {
        $this->seed(\Database\Seeders\EcommerceTestSeeder::class);

        $order = \App\Models\Order::first();
        $this->assertNotNull($order);
        $this->assertNotEmpty($order->tracking_number);

        $this->get(route('tracking.show', $order->tracking_number))
            ->assertOk()
            ->assertSee($order->order_number);

        $this->get(route('tracking.show', $order->order_number))
            ->assertOk()
            ->assertSee($order->tracking_number);
    }

    public function test_client_sidebar_displays_ongoing_tracking_with_history_and_direct_links(): void
    {
        $client = User::factory()->create([
            'user_type' => 'client',
            'verification_status' => 'approved',
            'registration_completed' => true,
        ]);

        $activeShipment = $this->createShipment([
            'customer_id' => $client->id,
            'hawb_number' => 'HAWB-DOM-ONGOING-1',
            'tracking_number' => 'NPD-2026-ONGOING-1',
            'status' => 'in_transit',
            'current_location' => 'Sindhuli Highway Checkpoint',
        ]);

        $deliveredShipment = $this->createShipment([
            'customer_id' => $client->id,
            'hawb_number' => 'HAWB-DOM-PAST-1',
            'tracking_number' => 'NPD-2026-PAST-1',
            'status' => 'delivered',
            'delivered_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($client)->get(route('client.dashboard'));

        $response->assertOk()
            ->assertSee('Active Consignment Live Tracking Radar')
            ->assertSee('In Transit')
            ->assertSee('Delivered')
            ->assertSee('NPD-2026-ONGOING-1')
            ->assertSee(route('tracking.show', $activeShipment->tracking_number));
    }

    public function test_client_dashboard_always_shows_active_shipment_tracking_with_full_tracking_page_link(): void
    {
        $client = User::factory()->create([
            'user_type' => 'client',
            'verification_status' => 'approved',
            'registration_completed' => true,
        ]);

        $activeShipment = $this->createShipment([
            'customer_id' => $client->id,
            'hawb_number' => 'HAWB-DOM-777',
            'tracking_number' => 'NPD-2026-ACTIVE-777',
            'destination' => 'Pokhara Ward 8',
            'origin' => 'Kathmandu Central Hub',
            'service_type' => 'flash',
            'status' => 'in_transit',
            'current_location' => 'Mugling Checkpoint Corridor',
        ]);

        $response = $this->actingAs($client)->get(route('client.dashboard'));

        $response->assertOk()
            ->assertSee('Active Consignment Live Tracking')
            ->assertSee('NPD-2026-ACTIVE-777')
            ->assertSee('HAWB-DOM-777')
            ->assertSee('Mugling Checkpoint Corridor')
            ->assertSee('Open Full Tracking Page')
            ->assertSee(route('tracking.show', $activeShipment->tracking_number));

        // When clicked, the required full tracking page is opened
        $this->actingAs($client)
            ->get(route('tracking.show', $activeShipment->tracking_number))
            ->assertOk()
            ->assertSee('NPD-2026-ACTIVE-777');
    }

    public function test_admin_dashboard_shows_active_shipments_tracking_with_full_tracking_page_link(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'super_admin',
            'verification_status' => 'approved',
            'registration_completed' => true,
        ]);

        $activeShipment = $this->createShipment([
            'hawb_number' => 'HAWB-NET-888',
            'tracking_number' => 'NPI-2026-ACTIVE-888',
            'destination' => 'London, UK',
            'origin' => 'TIA Cargo Terminal',
            'service_type' => 'international',
            'shipment_type' => 'parcel',
            'status' => 'out_for_delivery',
            'current_location' => 'London Heathrow Customs Depot',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Active shipments')
            ->assertSee('NPI-2026-ACTIVE-888')
            ->assertSee('London Heathrow Customs Depot')
            ->assertSee(route('tracking.show', $activeShipment->tracking_number));

        // When clicked, the required full tracking page is opened
        $this->actingAs($admin)
            ->get(route('tracking.show', $activeShipment->tracking_number))
            ->assertOk()
            ->assertSee('NPI-2026-ACTIVE-888');
    }

    public function test_client_sidebar_has_shipment_details_and_hawb_print_actions(): void
    {
        $client = User::factory()->create([
            'user_type' => 'client',
            'verification_status' => 'approved',
            'registration_completed' => true,
        ]);

        $activeShipment = $this->createShipment([
            'customer_id' => $client->id,
            'tracking_number' => 'NPD-2026-SIDEBAR-99',
            'destination' => 'Chitwan Hub',
            'status' => 'in_transit',
        ]);

        $response = $this->actingAs($client)->get(route('client.history'));

        $response->assertOk()
            ->assertSee('Shipment History & Radar Tracking', false)
            ->assertSee('NPD-2026-SIDEBAR-99')
            ->assertSee(route('tracking.show', $activeShipment->tracking_number))
            ->assertSee(route('hawb.print', ['id' => $activeShipment->id, 'type' => 'international']));
    }

    public function test_hawb_copies_have_zero_charges_for_all_services(): void
    {
        $client = User::factory()->create([
            'user_type' => 'client',
            'verification_status' => 'approved',
            'registration_completed' => true,
        ]);

        $intlShipment = $this->createShipment([
            'customer_id' => $client->id,
            'tracking_number' => 'NPI-2026-NOCHARGE-1',
            'shipment_type' => 'parcel',
            'shipping_cost' => 1250.00,
            'total_amount' => 1350.00,
            'status' => 'in_transit',
        ]);

        $domShipment = $this->createShipment([
            'customer_id' => $client->id,
            'tracking_number' => 'NPD-2026-NOCHARGE-2',
            'shipment_type' => 'domestic',
            'shipping_cost' => 500.00,
            'total_amount' => 550.00,
            'status' => 'in_transit',
        ]);

        // 1. International HAWB
        $intlResponse = $this->actingAs($client)->get(route('hawb.international', $intlShipment->id));
        $intlResponse->assertOk()
            ->assertSee('HOUSE AIR WAYBILL')
            ->assertSee('NPI-2026-NOCHARGE-1')
            ->assertDontSee('1250')
            ->assertDontSee('1350')
            ->assertDontSee('रू')
            ->assertDontSee('Rs.')
            ->assertDontSee('$');

        // 2. Domestic HAWB
        $domResponse = $this->actingAs($client)->get(route('hawb.domestic', $domShipment->id));
        $domResponse->assertOk()
            ->assertSee('OFFICIAL FREIGHT MANIFEST')
            ->assertSee('NPD-2026-NOCHARGE-2')
            ->assertDontSee('500')
            ->assertDontSee('550')
            ->assertDontSee('रू')
            ->assertDontSee('Rs.')
            ->assertDontSee('$')
            ->assertDontSee('C.O.D');

        // 3. Print Popup HAWB
        $printResponse = $this->actingAs($client)->get(route('hawb.print', ['id' => $intlShipment->id, 'type' => 'international']));
        $printResponse->assertOk()
            ->assertSee('HOUSE AIR WAYBILL')
            ->assertSee('NPI-2026-NOCHARGE-1')
            ->assertDontSee('1250')
            ->assertDontSee('1350')
            ->assertDontSee('रू')
            ->assertDontSee('Rs.')
            ->assertDontSee('$');
    }

    public function test_client_can_view_shipments_list_with_details_and_hawb_links(): void
    {
        $client = User::factory()->create([
            'user_type' => 'client',
            'verification_status' => 'approved',
            'registration_completed' => true,
        ]);

        $shipment = $this->createShipment([
            'customer_id' => $client->id,
            'tracking_number' => 'NPI-2026-LIST-01',
            'status' => 'in_transit',
        ]);

        $response = $this->actingAs($client)->get(route('shipments.index'));
        $response->assertOk()
            ->assertSee('NPI-2026-LIST-01')
            ->assertSee(route('shipments.show', $shipment->id))
            ->assertSee(route('tracking.show', $shipment->tracking_number))
            ->assertSee(route('hawb.international', $shipment->id))
            ->assertSee(route('hawb.print', ['id' => $shipment->id, 'type' => 'international']));
    }

    private function createShipment(array $attributes = []): Shipment
    {
        $customer = $attributes['customer_id'] ?? null;
        if (!$customer) {
            $customerUser = User::factory()->create([
                'user_type' => 'customer',
                'verification_status' => 'approved',
                'registration_completed' => true,
            ]);
            $customerId = $customerUser->id;
        } else {
            $customerId = $customer;
        }

        $default = [
            'hawb_number' => 'USNP-2026-101',
            'tracking_number' => 'NPI-2026-000101-7',
            'customer_id' => $customerId,
            'sender_name' => 'NETPACK Kathmandu',
            'sender_phone' => '9800000000',
            'sender_address' => 'Thamel',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'Preview Customer',
            'receiver_phone' => '555-0100',
            'receiver_address' => 'Preview Street',
            'receiver_city' => 'New York',
            'receiver_country' => 'United States',
            'service_type' => 'express',
            'shipment_type' => 'parcel',
            'actual_weight' => 2.4,
            'chargeable_weight' => 3,
            'shipping_cost' => 85,
            'total_amount' => 92,
            'payment_method' => 'online',
            'payment_status' => 'paid',
            'status' => 'in_transit',
            'tracking_history' => [[
                'status' => 'in_transit',
                'status_label' => 'In Transit',
                'description' => 'Departed transit facility',
                'location' => 'Dubai Transit Hub',
                'time' => now()->toIso8601String(),
            ]],
        ];

        return Shipment::create(array_merge($default, $attributes));
    }
}
