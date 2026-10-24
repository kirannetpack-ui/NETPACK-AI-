<?php

namespace Tests\Feature;

use App\Models\OverseasTransitPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransitPointAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_overseas_partner_cannot_toggle_another_partners_transit_point(): void
    {
        $partner = User::factory()->create(['user_type' => User::TYPE_OVERSEAS]);
        $otherPartner = User::factory()->create(['user_type' => User::TYPE_OVERSEAS]);
        $transitPoint = OverseasTransitPoint::create([
            'partner_id' => $otherPartner->id,
            'name' => 'Restricted Hub',
            'type' => OverseasTransitPoint::TYPE_HUB,
            'location' => 'Kathmandu',
            'country' => 'Nepal',
            'is_mandatory' => false,
            'is_active' => true,
        ]);

        $this->actingAs($partner)
            ->patch(route('overseas.transit-points.toggle', $transitPoint))
            ->assertNotFound();

        $this->assertTrue($transitPoint->fresh()->is_active);
    }

    public function test_overseas_partner_can_toggle_own_transit_point(): void
    {
        $partner = User::factory()->create(['user_type' => User::TYPE_OVERSEAS]);
        $transitPoint = OverseasTransitPoint::create([
            'partner_id' => $partner->id,
            'name' => 'Partner Hub',
            'type' => OverseasTransitPoint::TYPE_HUB,
            'location' => 'Kathmandu',
            'country' => 'Nepal',
            'is_mandatory' => false,
            'is_active' => true,
        ]);

        $this->actingAs($partner)
            ->patch(route('overseas.transit-points.toggle', $transitPoint))
            ->assertRedirect(route('overseas.transit-points.index'));

        $this->assertFalse($transitPoint->fresh()->is_active);
    }
}
