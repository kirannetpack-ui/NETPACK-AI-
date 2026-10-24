<?php

namespace Tests\Feature;

use App\Models\RiderProfile;
use App\Models\ShipmentAssignment;
use App\Models\User;
use App\Services\EcommerceDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OperationalIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function booking(User $seller, float $cod = 1000): ShipmentAssignment
    {
        return app(EcommerceDispatchService::class)->createDirectDelivery($seller, [
            'delivery_name' => 'Recipient', 'delivery_phone' => '9841000001',
            'delivery_address' => 'Kathmandu', 'distance_km' => 3,
            'parcel_weight' => 1, 'cod_amount' => $cod,
        ]);
    }

    private function rider(array $attributes = []): RiderProfile
    {
        return RiderProfile::create(array_merge([
            'user_id' => User::factory()->create(['user_type' => 'rider', 'role' => 'rider'])->id,
            'verification_status' => 'verified', 'availability_status' => 'online',
            'cod_limit' => 1500,
        ], $attributes));
    }

    public function test_seller_cannot_view_or_rate_another_sellers_assignment_even_with_same_name(): void
    {
        $seller = User::factory()->create(['user_type' => 'seller', 'role' => 'seller', 'name' => 'Same Shop']);
        $other = User::factory()->create(['user_type' => 'seller', 'role' => 'seller', 'name' => 'Same Shop']);
        $assignment = $this->booking($seller);
        $this->actingAs($other)->get(route('seller.ecommerce.show', $assignment->id))->assertNotFound();
        $this->get(route('seller.ecommerce.index'))->assertDontSee($assignment->master_awb);
        $this->post(route('seller.ecommerce.rate', $assignment->id), ['overall_rating' => 5])->assertNotFound();
        $this->actingAs($seller)->get(route('seller.ecommerce.show', $assignment->id))->assertOk();
    }

    public function test_delivery_replays_cannot_duplicate_cash_earnings_or_capacity(): void
    {
        $assignment = $this->booking(User::factory()->create());
        $rider = $this->rider();
        $service = app(EcommerceDispatchService::class);
        $this->assertTrue($service->acceptJob($rider, $assignment->id)['success']);
        $this->assertTrue($service->acceptJob($rider, $assignment->id)['success']);
        $this->assertSame(1, $rider->fresh()->current_active_packages);
        $this->assertFalse($service->completeDelivery($assignment, $assignment->delivery_otp)['success']);
        $this->assertTrue($service->verifyPickupOtp($assignment, $assignment->pickup_otp)['success']);
        $this->assertTrue($service->completeDelivery($assignment, $assignment->delivery_otp)['success']);
        $this->assertFalse($service->completeDelivery($assignment, $assignment->delivery_otp)['success']);
        $this->assertFalse($service->recordFailure($assignment, 'customer_refused'));
        $this->assertFalse($service->verifyPickupOtp($assignment, $assignment->pickup_otp)['success']);
        $this->assertSame(1, $rider->codLedgers()->count());
        $this->assertSame(1, $rider->earningsLedgers()->count());
        $this->assertSame(0, $rider->fresh()->current_active_packages);
        $this->assertEquals(1000, $rider->fresh()->current_outstanding_cod);
        $this->assertSame('completed', $assignment->fresh()->status);
    }

    public function test_assignment_rechecks_verification_capacity_and_reserved_cod(): void
    {
        $seller = User::factory()->create();
        $first = $this->booking($seller);
        $second = $this->booking($seller);
        $rider = $this->rider();
        $service = app(EcommerceDispatchService::class);
        $this->assertTrue($service->acceptJob($rider, $first->id)['success']);
        $this->assertFalse($service->acceptJob($rider, $second->id)['success']);
        $this->assertFalse($service->acceptJob($this->rider(['verification_status' => 'suspended']), $second->id)['success']);
        $this->assertFalse($service->acceptJob($this->rider(['current_active_packages' => 5]), $second->id)['success']);
        $this->assertNull($second->fresh()->rider_profile_id);
    }

    public function test_private_kyc_is_available_only_to_owner_and_authorized_admin(): void
    {
        Storage::fake('private');
        $rider = $this->rider();
        $rider->update(['citizenship_front_path' => 'kyc/citizenship/test.pdf']);
        Storage::disk('private')->put($rider->citizenship_front_path, 'private identity');
        $url = route('rider.documents.show', [$rider->id, 'citizenship_front_path']);
        $this->getJson($url)->assertUnauthorized();
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $this->actingAs($rider->user)->get($url)->assertOk()->assertHeader('Cache-Control', 'max-age=0, no-store, private');
        $this->get(route('rider.documents.show', [$rider->id, 'bank_account_number']))->assertNotFound();
    }

    public function test_unsigned_stale_and_tampered_webhooks_are_rejected(): void
    {
        $payload = ['tracking_number' => '1234567890', 'status' => 'DELIVERED', 'location' => 'London', 'timestamp' => now()->toIso8601String()];
        $headers = $this->carrierWebhookHeaders($payload, 'dhl');
        $this->postJson('/api/webhooks/carrier-tracking/dhl', $payload)->assertUnauthorized();
        $this->postJson('/api/webhooks/carrier-tracking/dhl', array_merge($payload, ['location' => 'Changed']), $headers)->assertUnauthorized();
        $headers['X-Netpack-Timestamp'] = (string) (time() - 1000);
        $this->postJson('/api/webhooks/carrier-tracking/dhl', $payload, $headers)->assertUnauthorized();
    }

    public function test_public_and_seller_cannot_execute_ai_administration(): void
    {
        $this->getJson('/admin/ai/operational-intelligence')->assertUnauthorized();
        $seller = User::factory()->create(['role' => 'seller', 'user_type' => 'seller']);
        $this->actingAs($seller)->postJson('/admin/ai/resolve-issue-action', [
            'issue_id' => 'issue-1', 'action_type' => 'notify_and_reroute',
        ])->assertForbidden();
    }

    public function test_multileg_booking_rolls_back_when_no_linehaul_partner_exists(): void
    {
        try {
            app(EcommerceDispatchService::class)->createMultiLegDelivery(User::factory()->create(), [
                'delivery_name' => 'Recipient', 'delivery_phone' => '9841000001',
                'delivery_address' => 'Pokhara', 'destination_city' => 'Pokhara',
            ]);
            $this->fail('Expected missing partner validation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('partner_id', $exception->errors());
        }
        $this->assertDatabaseCount('shipment_assignments', 0);
        $this->assertDatabaseCount('rider_job_offers', 0);
    }

    public function test_cod_deposit_replay_and_overpayment_are_controlled(): void
    {
        $rider = $this->rider(['current_outstanding_cod' => 1000]);
        $first = $rider->recordCodDeposit(750, 'bank_transfer', 'BANK-01', null, '', false);
        $again = $rider->recordCodDeposit(750, 'bank_transfer', 'BANK-01', null, '', false);
        $this->assertSame($first->id, $again->id);
        $this->assertEquals(1000, $rider->fresh()->current_outstanding_cod);
        $this->expectException(ValidationException::class);
        $rider->recordCodDeposit(500, 'bank_transfer', 'BANK-02', null, '', false);
    }

    public function test_legacy_kyc_mover_verifies_private_copy_before_removing_public_file(): void
    {
        Storage::fake('public');
        Storage::fake('private');
        Storage::disk('public')->put('kyc/citizenship/legacy.pdf', 'legacy-private-document');
        $this->artisan('storage:privatize-kyc')->assertSuccessful();
        Storage::disk('public')->assertExists('kyc/citizenship/legacy.pdf');
        $this->artisan('storage:privatize-kyc --apply')->assertSuccessful();
        Storage::disk('private')->assertExists('kyc/citizenship/legacy.pdf');
        Storage::disk('public')->assertMissing('kyc/citizenship/legacy.pdf');
    }

    public function test_recipient_code_requires_a_valid_expiring_link_and_confirmed_pickup(): void
    {
        $assignment = $this->booking(User::factory()->create());
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute('recipient.handover', now()->addHour(), ['assignment' => $assignment->id]);
        $this->get(route('recipient.handover', $assignment->id))->assertForbidden();
        $this->get($url)->assertOk()->assertDontSee($assignment->delivery_otp);
        $service = app(EcommerceDispatchService::class);
        $service->acceptJob($this->rider(), $assignment->id);
        $service->verifyPickupOtp($assignment, $assignment->pickup_otp);
        $this->get($url)->assertOk()->assertSee($assignment->delivery_otp)->assertHeader('Referrer-Policy', 'no-referrer');
        $this->travel(2)->hours();
        $this->get($url)->assertForbidden();
    }

    public function test_three_leg_handover_sequence_finishes_with_one_cod_collection(): void
    {
        $partner = \App\Models\DomesticPartner::create(['company_name' => 'Test Linehaul', 'code' => 'TLH', 'phone' => '9841000002', 'is_active' => true]);
        $seller = User::factory()->create(['user_type' => 'seller', 'role' => 'seller']);
        $admin = User::factory()->create(['user_type' => 'domestic_admin', 'role' => 'domestic_admin']);
        $service = app(EcommerceDispatchService::class);
        $booking = $service->createMultiLegDelivery($seller, [
            'delivery_name' => 'Recipient', 'delivery_phone' => '9841000001', 'delivery_address' => 'Pokhara',
            'destination_city' => 'Pokhara', 'cod_amount' => 1000,
        ], $partner);
        $first = $booking['leg_1']; $middle = $booking['leg_2']; $last = $booking['leg_3'];
        $pickupRider = $this->rider(); $deliveryRider = $this->rider();
        $this->assertFalse($service->acceptJob($deliveryRider, $last->id)['success']);
        $this->assertTrue($service->acceptJob($pickupRider, $first->id)['success']);
        $this->assertTrue($service->verifyPickupOtp($first, $first->pickup_otp)['success']);
        $this->assertTrue($service->completeDelivery($first, $first->delivery_otp)['success']);
        $this->actingAs($admin)->get(route('domestic.handovers.index'))->assertOk()->assertSee($middle->master_awb);
        $this->actingAs($seller)->post(route('domestic.handovers.store', $middle->id), [
            'event' => 'received', 'location' => 'Pokhara Hub', 'notes' => 'Unauthorized handover',
        ])->assertForbidden();
        $service->recordLinehaulHandover($middle->id, 'dispatched', $admin, 'Kathmandu Hub', 'Manifest TEST-001');
        $this->assertFalse($service->acceptJob($deliveryRider, $last->id)['success']);
        $service->recordLinehaulHandover($middle->id, 'received', $admin, 'Pokhara Hub', 'Received seal TEST-001');
        $this->assertTrue($service->acceptJob($deliveryRider, $last->id)['success']);
        $this->assertTrue($service->verifyPickupOtp($last, $last->pickup_otp)['success']);
        $this->assertTrue($service->completeDelivery($last, $last->delivery_otp)['success']);
        $this->assertSame(['completed', 'completed', 'completed'], ShipmentAssignment::where('master_awb', $middle->master_awb)->orderBy('sequence')->pluck('status')->all());
        $this->assertDatabaseCount('assignment_handover_events', 2);
        $this->assertDatabaseCount('rider_cod_ledgers', 1);
        $this->assertEquals(1000, $deliveryRider->fresh()->current_outstanding_cod);
    }
}
