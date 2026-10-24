<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedTrackingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_ecommerce_public_page_is_safe_and_authorized_customer_can_poll_real_rider_gps(): void
    {
        $customer = User::factory()->create(['user_type' => 'customer']);
        $rider = User::factory()->create(['user_type' => 'rider']);
        $outsider = User::factory()->create(['user_type' => 'customer']);

        $order = Order::create([
            'order_number' => 'ORD-LIVE-1001',
            'tracking_number' => 'NPE-2026-100001-8',
            'customer_id' => $customer->id,
            'rider_id' => $rider->id,
            'customer_name' => 'Private Customer',
            'customer_phone' => '9800001111',
            'shipping_address' => 'Private Delivery Address',
            'delivery_latitude' => 27.7001,
            'delivery_longitude' => 85.3333,
            'status' => 'out_for_delivery',
            'payment_status' => 'paid',
            'payment_method' => 'prepaid',
            'rider_assigned_at' => now()->subHour(),
            'picked_up_at' => now()->subMinutes(45),
            'out_for_delivery_at' => now()->subMinutes(20),
        ]);

        $this->get(route('tracking.show', $order->tracking_number))
            ->assertOk()
            ->assertSee('E-Commerce Delivery')
            ->assertSee('Live location is privacy protected')
            ->assertDontSee('9800001111')
            ->assertDontSee('Private Delivery Address');

        $this->actingAs($rider)->get(route('rider.orders.my'))
            ->assertOk()
            ->assertSee('secure GPS sharing')
            ->assertSee('navigator.geolocation.watchPosition', false);

        $this->actingAs($rider)->postJson(route('rider.orders.update-location'), [
            'order_id' => $order->id,
            'latitude' => 27.7055,
            'longitude' => 85.3299,
            'accuracy' => 8.5,
            'speed' => 7.2,
            'bearing' => 120,
        ])->assertOk();

        $this->actingAs($customer)
            ->getJson(route('tracking.orders.live', $order))
            ->assertOk()
            ->assertJsonPath('data.latitude', 27.7055)
            ->assertJsonPath('data.longitude', 85.3299)
            ->assertJsonPath('data.accuracy', 8.5)
            ->assertJsonPath('data.is_stale', false);

        $this->actingAs($outsider)
            ->getJson(route('tracking.orders.live', $order))
            ->assertForbidden();
    }

    public function test_verified_scans_follow_the_international_milestone_sequence_and_store_audit_data(): void
    {
        $admin = User::factory()->create(['user_type' => 'super_admin']);
        $shipment = $this->internationalShipment();

        $this->actingAs($admin)->postJson(route('hawb.update-from-scan'), [
            'tracking' => $shipment->tracking_number,
            'event_code' => 'booking_confirmed',
            'location' => 'Kathmandu Gateway',
        ])->assertOk();

        $this->actingAs($admin)->postJson(route('hawb.update-from-scan'), [
            'tracking' => $shipment->tracking_number,
            'event_code' => 'customs_hold',
            'location' => 'Customs',
        ])->assertUnprocessable();

        foreach ([
            ['shipment_picked_up', 'Kathmandu'],
            ['origin_facility_departure', 'Kathmandu Gateway'],
            ['export_departure', 'Tribhuvan International Airport'],
        ] as [$eventCode, $location]) {
            $this->actingAs($admin)->postJson(route('hawb.update-from-scan'), [
                'tracking' => $shipment->tracking_number,
                'event_code' => $eventCode,
                'location' => $location,
            ])->assertOk();
        }

        $this->actingAs($admin)->postJson(route('hawb.update-from-scan'), [
            'tracking' => $shipment->tracking_number,
            'event_code' => 'customs_cleared',
            'location' => 'Dubai Import Gateway',
        ])->assertUnprocessable();

        foreach (['customs_hold', 'customs_cleared'] as $eventCode) {
            $this->actingAs($admin)->postJson(route('hawb.update-from-scan'), [
                'tracking' => $shipment->tracking_number,
                'event_code' => $eventCode,
                'location' => 'Dubai Import Gateway',
            ])->assertOk();
        }

        $shipment->refresh();
        $latest = collect($shipment->tracking_history)->last();

        $this->assertSame('in_transit', $shipment->status);
        $this->assertSame('customs_cleared', $latest['event_code']);
        $this->assertSame('qr', $latest['scan_source']);
        $this->assertSame($admin->id, $latest['scanned_by_user_id']);
        $this->assertNotNull($shipment->customs_cleared_at);
    }

    private function internationalShipment(): Shipment
    {
        $customer = User::factory()->create(['user_type' => 'customer']);

        return Shipment::create([
            'tracking_number' => 'NPI-2026-200001-3',
            'hawb_number' => 'AUNP-2026-001',
            'customer_id' => $customer->id,
            'sender_name' => 'NETPACK Nepal',
            'sender_phone' => '9800000000',
            'sender_address' => 'Kathmandu',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'Test Receiver',
            'receiver_phone' => '5550100',
            'receiver_address' => 'Sydney',
            'receiver_city' => 'Sydney',
            'receiver_country' => 'Australia',
            'service_type' => 'express',
            'shipment_type' => 'international',
            'actual_weight' => 2,
            'chargeable_weight' => 2,
            'shipping_cost' => 100,
            'total_amount' => 110,
            'payment_method' => 'online',
            'payment_status' => 'paid',
            'status' => 'pending',
        ]);
    }
}
