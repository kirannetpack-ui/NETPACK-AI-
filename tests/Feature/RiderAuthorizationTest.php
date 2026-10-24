<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiderAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_rider_cannot_access_rider_portal(): void
    {
        $seller = User::factory()->create(['user_type' => User::TYPE_SELLER]);

        $this->actingAs($seller)
            ->get(route('rider.dashboard'))
            ->assertForbidden();
    }

    public function test_rider_cannot_read_another_riders_live_order_tracking(): void
    {
        $assignedRider = User::factory()->create(['user_type' => User::TYPE_RIDER]);
        $otherRider = User::factory()->create(['user_type' => User::TYPE_RIDER]);
        $order = Order::create([
            'order_number' => 'ORD-AUTH-001',
            'rider_id' => $assignedRider->id,
            'customer_name' => 'Private Customer',
            'customer_phone' => '9800000000',
            'shipping_address' => 'Private address',
            'total_amount' => 1000,
            'status' => 'assigned',
            'tracking_number' => 'NPE-2026-000001-6',
        ]);

        $this->actingAs($otherRider)
            ->get(route('rider.orders.tracking', $order))
            ->assertNotFound();
    }
}
