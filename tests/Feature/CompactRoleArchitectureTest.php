<?php

namespace Tests\Feature;

use App\Models\PickupRequest;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompactRoleArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_access_compact_components(): void
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
            'email' => 'client@netpack.test',
        ]);

        $response = $this->actingAs($client)->get(route('client.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Shipment Inquiries');
        $response->assertSee('Rate Calculator');
        $response->assertSee('History & Tracking', false);

        // Verify Rate Inquiry Desk
        $rateResponse = $this->actingAs($client)->get(route('rates.inquiry'));
        $rateResponse->assertStatus(200);

        // Verify Inquiries Desk
        $inquiriesResponse = $this->actingAs($client)->get(route('client.inquiries'));
        $inquiriesResponse->assertStatus(200);
        $inquiriesResponse->assertSee('Submit New Consignment Pickup Inquiry');

        // Verify Scoped History
        $historyResponse = $this->actingAs($client)->get(route('client.history'));
        $historyResponse->assertStatus(200);
        $historyResponse->assertSee('Shipment History & Radar Tracking', false);
    }

    public function test_client_can_submit_shipment_inquiry(): void
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
            'email' => 'inquiry_client@netpack.test',
        ]);

        $response = $this->actingAs($client)->post(route('client.inquiries.store'), [
            'destination_scope' => 'inside_valley',
            'pickup_address' => 'Baluwatar Ward 4, Kathmandu',
            'contact_phone' => '9841000000',
            'delivery_address' => 'Jawalakhel, Lalitpur',
            'delivery_city' => 'Lalitpur',
            'recipient_name' => 'Sita Sharma',
            'recipient_phone' => '9801000000',
            'package_type' => 'Standard Parcel / Goods',
            'estimated_weight_kg' => 2.5,
            'service_tier' => 'standard',
            'instructions' => 'Call recipient on delivery',
        ]);

        $response->assertRedirect(route('client.inquiries'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pickup_requests', [
            'seller_id' => $client->id,
            'customer_name' => 'Sita Sharma',
            'delivery_city' => 'Lalitpur',
            'status' => 'pending',
        ]);
    }

    public function test_client_tracking_and_history_is_scoped_to_client(): void
    {
        $clientA = User::factory()->create(['user_type' => User::TYPE_CLIENT]);
        $clientB = User::factory()->create(['user_type' => User::TYPE_CLIENT]);

        // Shipment belonging to Client A
        $shipmentA = Shipment::create([
            'tracking_number' => 'NP-EXP-AAA111',
            'customer_id' => $clientA->id,
            'status' => 'in_transit',
            'origin' => 'Kathmandu',
            'destination' => 'Pokhara',
            'sender_name' => 'Client A',
            'receiver_name' => 'Recipient A',
            'receiver_country' => 'Nepal',
            'actual_weight' => 2.0,
            'chargeable_weight' => 2.0,
            'service_type' => 'express',
            'shipment_type' => 'domestic',
        ]);

        // Shipment belonging to Client B
        $shipmentB = Shipment::create([
            'tracking_number' => 'NP-EXP-BBB222',
            'customer_id' => $clientB->id,
            'status' => 'delivered',
            'origin' => 'Kathmandu',
            'destination' => 'Biratnagar',
            'sender_name' => 'Client B',
            'receiver_name' => 'Recipient B',
            'receiver_country' => 'Nepal',
            'actual_weight' => 3.0,
            'chargeable_weight' => 3.0,
            'service_type' => 'standard',
            'shipment_type' => 'domestic',
        ]);

        // Client A accesses history
        $responseA = $this->actingAs($clientA)->get(route('client.history'));
        $responseA->assertStatus(200);
        $responseA->assertSee('NP-EXP-AAA111');
        $responseA->assertDontSee('NP-EXP-BBB222');

        // Client B accesses history
        $responseB = $this->actingAs($clientB)->get(route('client.history'));
        $responseB->assertStatus(200);
        $responseB->assertSee('NP-EXP-BBB222');
        $responseB->assertDontSee('NP-EXP-AAA111');
    }

    public function test_super_admin_has_monitoring_and_international_rate_feeding(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::TYPE_SUPER_ADMIN,
            'email' => 'super@netpack.test',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('International Tariffs');
        $response->assertSee('Master Consignments');
        $response->assertSee('Rider GPS Fleet');

        // Super Admin can feed international rates
        $ratesResponse = $this->actingAs($admin)->get(route('admin.international-rates.index'));
        $ratesResponse->assertStatus(200);

        // Sidebar does NOT contain micro-operational user portals
        $response->assertDontSee('Merchant Seller');
        $response->assertDontSee('Rider Dispatch');
        $response->assertDontSee('Overseas Portal');
    }
}
