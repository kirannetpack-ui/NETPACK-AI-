<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\DomesticPartnerAssignment;
use App\Models\DomesticRate;
use App\Models\Shipment;
use App\Models\ShipmentLeg;
use App\Models\User;
use App\Services\DomesticPartnerRoutingService;
use App\Services\DomesticRateQuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DomesticPartnerOrchestrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_rate_requires_admin_approval_before_it_can_be_quoted(): void
    {
        [$admin, $partner, $customer, $origin, $destination] = $this->actorsAndZones();

        $this->actingAs($partner)->post(route('partner.rates.store'), $this->ratePayload($origin, $destination))
            ->assertRedirect(route('partner.rates.index'));

        $rate = DomesticRate::firstOrFail();
        $this->assertSame('pending', $rate->approval_status);
        $this->assertFalse($rate->is_active);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $admin->id]);

        try {
            app(DomesticRateQuoteService::class)->quote($origin->id, $destination->id, 'standard', 2);
            $this->fail('A pending partner rate was unexpectedly quoted.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        $this->actingAs($admin)->post(route('admin.domestic.rates.approve', $rate))->assertRedirect();

        $quote = app(DomesticRateQuoteService::class)->quote($origin->id, $destination->id, 'standard', 2);
        $this->assertSame(154.0, $quote['customer_price']);
        $this->assertDatabaseHas('domestic_rate_events', ['domestic_rate_id' => $rate->id, 'event_type' => 'approved']);
    }

    public function test_default_and_approved_alternative_partner_selection_is_controlled(): void
    {
        [$admin, $defaultPartner, $customer, $origin] = $this->actorsAndZones();
        $alternative = User::factory()->create(['user_type' => 'partner', 'verification_status' => 'approved']);
        $unapproved = User::factory()->create(['user_type' => 'partner', 'verification_status' => 'pending']);

        foreach ([[$defaultPartner, true, 1], [$alternative, false, 2]] as [$partner, $isDefault, $priority]) {
            DomesticPartnerAssignment::create([
                'zone_id' => $origin->id, 'partner_id' => $partner->id, 'leg_type' => 'pickup',
                'service_type' => 'standard', 'priority' => $priority, 'is_default' => $isDefault,
                'is_active' => true, 'created_by' => $admin->id,
            ]);
        }

        $routing = app(DomesticPartnerRoutingService::class);
        $this->assertSame($defaultPartner->id, $routing->resolve($origin->id, 'standard', 'pickup')['partner']->id);
        $this->assertSame($alternative->id, $routing->resolve($origin->id, 'standard', 'pickup', $alternative->id)['partner']->id);

        $this->expectException(ValidationException::class);
        $routing->resolve($origin->id, 'standard', 'pickup', $unapproved->id);
    }

    public function test_domestic_booking_creates_priced_route_legs_pickup_and_notifications(): void
    {
        [$admin, $partner, $customer, $origin, $destination] = $this->actorsAndZones();
        $rate = $this->approvedRate($partner, $origin, $destination);

        $response = $this->actingAs($customer)->post(route('shipments.store'), [
            'shipment_type' => 'domestic', 'service_type' => 'standard',
            'origin_zone_id' => $origin->id, 'destination_zone_id' => $destination->id,
            'pickup_name' => ['Sender'], 'pickup_phone' => ['9800000001'], 'pickup_address' => ['Ward 1, Kathmandu'],
            'delivery_name' => ['Receiver'], 'delivery_phone' => ['9800000002'], 'delivery_address' => ['Ward 2, Pokhara'],
            'weight' => 2, 'description' => 'Books', 'package_type' => 'parcel',
        ]);

        $shipment = Shipment::latest('id')->first();
        $response->assertRedirect(route('tracking.show', $shipment->tracking_number));
        $this->assertSame('168.00', $shipment->shipping_cost);
        $this->assertDatabaseHas('shipment_legs', ['shipment_id' => $shipment->id, 'partner_id' => $partner->id, 'domestic_rate_id' => $rate->id, 'status' => 'assigned']);
        $this->assertDatabaseHas('pickup_requests', ['shipment_id' => $shipment->id, 'partner_user_id' => $partner->id]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $partner->id]);
    }

    public function test_client_quote_returns_marked_up_price_without_partner_cost(): void
    {
        [$admin, $partner, $customer, $origin, $destination] = $this->actorsAndZones();
        $this->approvedRate($partner, $origin, $destination);

        $this->actingAs($customer)->postJson(route('domestic.quote'), [
            'origin_zone_id' => $origin->id,
            'destination_zone_id' => $destination->id,
            'service_type' => 'standard',
            'weight' => 2,
        ])->assertOk()
            ->assertJsonPath('customer_price', 168)
            ->assertJsonMissingPath('partner_cost')
            ->assertJsonMissingPath('partner_id');
    }

    public function test_partner_cannot_skip_required_leg_statuses(): void
    {
        [$admin, $partner, $customer, $origin, $destination] = $this->actorsAndZones();
        $shipment = $this->shipment($customer);
        $leg = ShipmentLeg::create([
            'shipment_id' => $shipment->id, 'sequence' => 1, 'leg_type' => 'pickup',
            'partner_id' => $partner->id, 'origin_zone_id' => $origin->id,
            'destination_zone_id' => $destination->id, 'status' => 'assigned',
        ]);

        $this->actingAs($partner)->patch(route('partner.shipment-legs.status', $leg), ['status' => 'completed'])
            ->assertSessionHasErrors('status');
        $this->assertSame('assigned', $leg->fresh()->status);

        $this->actingAs($partner)->patch(route('partner.shipment-legs.status', $leg), ['status' => 'accepted'])
            ->assertRedirect();
        $this->assertSame('accepted', $leg->fresh()->status);
        $this->assertDatabaseHas('shipment_leg_events', ['shipment_leg_id' => $leg->id, 'from_status' => 'assigned', 'to_status' => 'accepted']);
    }

    private function actorsAndZones(): array
    {
        $admin = User::factory()->create(['user_type' => 'super_admin', 'verification_status' => 'approved']);
        $partner = User::factory()->create(['user_type' => 'partner', 'verification_status' => 'approved']);
        $customer = User::factory()->create(['user_type' => 'customer', 'verification_status' => 'approved', 'phone' => '9800000000']);
        $origin = DeliveryZone::create(['partner_user_id' => $partner->id, 'zone_name' => 'Kathmandu Central', 'zone_code' => 'KTM-TEST', 'zone_type' => 'urban', 'district' => 'Kathmandu', 'province' => 'Bagmati', 'approval_status' => 'approved', 'is_active' => true]);
        $destination = DeliveryZone::create(['zone_name' => 'Pokhara Central', 'zone_code' => 'PKR-TEST', 'zone_type' => 'urban', 'district' => 'Kaski', 'province' => 'Gandaki', 'approval_status' => 'approved', 'is_active' => true]);

        return [$admin, $partner, $customer, $origin, $destination];
    }

    private function ratePayload(DeliveryZone $origin, DeliveryZone $destination): array
    {
        return [
            'origin_zone_id' => $origin->id, 'destination_zone_id' => $destination->id,
            'rate_type' => 'door_to_door', 'service_type' => 'standard',
            'weight_from' => 0, 'weight_to' => 10, 'base_rate' => 100,
            'per_kg_rate' => 20, 'currency' => 'NPR', 'effective_from' => now()->toDateString(),
        ];
    }

    private function approvedRate(User $partner, DeliveryZone $origin, DeliveryZone $destination): DomesticRate
    {
        return DomesticRate::create(array_merge($this->ratePayload($origin, $destination), [
            'partner_id' => $partner->id, 'service_name' => 'STANDARD',
            'origin_city' => $origin->zone_name, 'origin_zone' => $origin->zone_code,
            'destination_city' => $destination->zone_name, 'destination_zone' => $destination->zone_code,
            'rate_per_kg' => 20, 'is_active' => true, 'approval_status' => 'approved',
            'admin_margin_type' => 'percentage', 'admin_margin_value' => 20,
        ]));
    }

    private function shipment(User $customer): Shipment
    {
        return Shipment::create([
            'hawb_number' => 'HAWB-LEG-001', 'tracking_number' => 'TRK-LEG-001', 'customer_id' => $customer->id,
            'sender_name' => 'Sender', 'sender_phone' => '9800000001', 'sender_address' => 'Kathmandu', 'sender_city' => 'Kathmandu', 'sender_country' => 'Nepal',
            'receiver_name' => 'Receiver', 'receiver_phone' => '9800000002', 'receiver_address' => 'Pokhara', 'receiver_city' => 'Pokhara', 'receiver_country' => 'Nepal',
            'service_type' => 'standard', 'shipment_type' => 'domestic', 'actual_weight' => 1, 'chargeable_weight' => 1,
            'shipping_cost' => 100, 'total_amount' => 100, 'status' => 'pending',
        ]);
    }
}
