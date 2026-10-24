<?php

namespace Tests\Feature;

use App\Models\DomesticPartner;
use App\Models\RiderCodLedger;
use App\Models\RiderProfile;
use App\Models\ShipmentAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EcommerceRiderDeliveryNetworkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('private');
    }

    public function test_rider_can_register_with_kyc_and_informational_platform_affiliation(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Ram Shrestha',
            'email' => 'ram.rider@example.com',
            'phone' => '9841000001',
            'dob' => '1995-05-15',
            'gender' => 'male',
            'nationality' => 'Nepali',
            'user_type' => 'rider',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'vehicle_type' => 'motorcycle',
            'vehicle_registration_number' => 'BA 99 PA 1234',
            'license_number' => 'DL-998877',
            'citizenship_number' => 'CT-112233',
            'affiliation' => 'pathao',
            'terms' => '1',
            'driving_license_doc' => UploadedFile::fake()->create('license.pdf', 500, 'application/pdf'),
            'vehicle_registration_doc' => UploadedFile::fake()->create('bluebook.pdf', 500, 'application/pdf'),
            'citizenship_front' => UploadedFile::fake()->create('citizenship.pdf', 500, 'application/pdf'),
        ]);

        $response->assertRedirect(route('registration.pending'));

        $this->assertDatabaseHas('users', [
            'email' => 'ram.rider@example.com',
            'role' => 'rider',
            'user_type' => 'rider',
        ]);

        $user = User::where('email', 'ram.rider@example.com')->first();
        $this->assertNotNull($user);

        $this->assertDatabaseHas('rider_profiles', [
            'user_id' => $user->id,
            'vehicle_type' => 'motorcycle',
            'vehicle_number' => 'BA 99 PA 1234',
            'affiliation' => 'pathao',
            'verification_status' => 'pending',
            'cod_limit' => 0.00,
        ]);
    }

    public function test_domestic_admin_can_verify_rider_and_assign_cod_limit(): void
    {
        $admin = User::factory()->create([
            'role' => 'domestic_admin',
            'user_type' => 'domestic_admin',
        ]);

        $riderUser = User::factory()->create([
            'role' => 'rider',
            'user_type' => 'rider',
        ]);

        $riderProfile = RiderProfile::create([
            'user_id' => $riderUser->id,
            'vehicle_type' => 'motorcycle',
            'vehicle_number' => 'BA 10 PA 5678',
            'affiliation' => 'indrive',
            'operating_zones' => 'Kathmandu',
            'verification_status' => 'pending',
            'cod_limit' => 0,
        ]);

        // Domestic admin views rider dossier
        $showResponse = $this->actingAs($admin)->get(route('domestic.ecommerce.riders.show', $riderProfile->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('BA 10 PA 5678');
        $showResponse->assertSee('indrive');

        // Admin verifies rider with Level 2 (Rs. 20,000 limit)
        $verifyResponse = $this->actingAs($admin)->post(route('domestic.ecommerce.riders.verify', $riderProfile->id), [
            'cod_level' => 'level_2',
        ]);

        $verifyResponse->assertRedirect();
        $verifyResponse->assertSessionHas('success');

        $riderProfile->refresh();
        $this->assertTrue((bool) $riderProfile->is_verified);
        $this->assertEquals(20000.00, (float) $riderProfile->cod_limit);
        $this->assertEquals('level_2', $riderProfile->cod_level);
    }

    public function test_seller_can_book_direct_rider_delivery_with_instant_pickup_otp(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'user_type' => 'seller',
            'business_name' => 'Tech Nepal Store',
            'phone' => '9801000001',
            'address' => 'New Road, Kathmandu',
        ]);

        $response = $this->actingAs($seller)->post(route('seller.ecommerce.direct.store'), [
            'delivery_name' => 'Bikash Thapa',
            'delivery_phone' => '9802000002',
            'delivery_address' => 'Patan Dhoka, Lalitpur',
            'distance_km' => 5.0,
            'parcel_weight' => 1.5,
            'cod_amount' => 2500,
            'vehicle_type' => 'motorcycle',
            'special_instructions' => 'Call recipient upon arriving at gate',
        ]);

        $assignment = ShipmentAssignment::latest()->first();
        $this->assertNotNull($assignment);

        $response->assertRedirect(route('seller.ecommerce.show', $assignment->id));
        $response->assertSessionHas('success');

        $this->assertEquals('local_direct', $assignment->assignment_type);
        $this->assertEquals('assigned', $assignment->status);
        $this->assertEquals(2500.00, (float) $assignment->cod_amount);
        $this->assertNotNull($assignment->pickup_otp);
        $this->assertEquals(6, strlen($assignment->pickup_otp));
        $this->assertNotNull($assignment->delivery_otp);
        $this->assertEquals(6, strlen($assignment->delivery_otp));
    }

    public function test_direct_delivery_complete_lifecycle_with_otp_and_segregated_ledgers(): void
    {
        // 1. Setup Seller
        $seller = User::factory()->create([
            'role' => 'seller',
            'user_type' => 'seller',
            'business_name' => 'Fashion Hub',
            'phone' => '9841112233',
            'address' => 'Baneshwor, Kathmandu',
        ]);

        // 2. Setup Verified Rider with COD limit Rs. 10,000
        $riderUser = User::factory()->create([
            'role' => 'rider',
            'user_type' => 'rider',
            'name' => 'Bikash Rider',
        ]);
        $riderProfile = RiderProfile::create([
            'user_id' => $riderUser->id,
            'full_name' => 'Bikash Rider',
            'vehicle_type' => 'motorcycle',
            'vehicle_number' => 'BA 44 PA 1111',
            'affiliation' => 'pathao',
            'verification_status' => 'verified',
            'availability_status' => 'online',
            'cod_limit' => 10000,
            'current_outstanding_cod' => 0,
        ]);

        // 3. Seller creates direct delivery
        $this->actingAs($seller)->post(route('seller.ecommerce.direct.store'), [
            'delivery_name' => 'Anita Gurung',
            'delivery_phone' => '9845556677',
            'delivery_address' => 'Jhamsikhel, Lalitpur',
            'distance_km' => 4.5,
            'parcel_weight' => 1.0,
            'cod_amount' => 3200,
            'vehicle_type' => 'motorcycle',
        ]);

        $assignment = ShipmentAssignment::latest()->first();
        $this->assertNotNull($assignment);

        // 4. Rider views available jobs
        $availResponse = $this->actingAs($riderUser)->get(route('rider.delivery.available'));
        $availResponse->assertStatus(200);
        $availResponse->assertSee($assignment->master_awb);

        // 5. Rider accepts job
        $acceptResponse = $this->actingAs($riderUser)->post(route('rider.delivery.accept', $assignment->id));
        $acceptResponse->assertRedirect(route('rider.delivery.show', $assignment->id));

        $assignment->refresh();
        $this->assertEquals('accepted', $assignment->status);
        $this->assertEquals($riderProfile->id, $assignment->rider_profile_id);

        // 6. Rider arrives at pickup location
        $arriveResponse = $this->actingAs($riderUser)->post(route('rider.delivery.arrive', $assignment->id));
        $arriveResponse->assertRedirect();
        $assignment->refresh();
        $this->assertEquals('arrived_pickup', $assignment->status);

        // 7. Rider verifies Pickup OTP provided by Seller
        $wrongOtpResponse = $this->actingAs($riderUser)->post(route('rider.delivery.verify-pickup', $assignment->id), [
            'pickup_otp' => '000000',
        ]);
        $wrongOtpResponse->assertSessionHas('error');

        $correctOtpResponse = $this->actingAs($riderUser)->post(route('rider.delivery.verify-pickup', $assignment->id), [
            'pickup_otp' => $assignment->pickup_otp,
        ]);
        $correctOtpResponse->assertSessionHas('success');

        $assignment->refresh();
        $this->assertEquals('picked_up', $assignment->status);

        // 8. Rider arrives at Customer, collects COD Rs. 3200 and enters customer delivery OTP
        $deliveryResponse = $this->actingAs($riderUser)->post(route('rider.delivery.complete', $assignment->id), [
            'delivery_otp' => $assignment->delivery_otp,
            'cod_collected' => 3200,
            'recipient_name' => 'Anita Gurung',
            'recipient_notes' => 'Delivered to recipient in person at doorstep',
        ]);
        $deliveryResponse->assertSessionHas('success');

        $assignment->refresh();
        $riderProfile->refresh();

        $this->assertEquals('completed', $assignment->status);

        // 9. Segregation verification:
        // Cash in hand must be in rider_cod_ledgers (COD Credit)
        $this->assertDatabaseHas('rider_cod_ledgers', [
            'rider_profile_id' => $riderProfile->id,
            'assignment_id' => $assignment->id,
            'transaction_type' => 'collected',
            'amount' => 3200.00,
        ]);
        $this->assertEquals(3200.00, (float) $riderProfile->current_outstanding_cod);

        // Rider earnings must be separate in rider_earnings_ledgers
        $this->assertDatabaseHas('rider_earnings_ledgers', [
            'rider_profile_id' => $riderProfile->id,
            'assignment_id' => $assignment->id,
            'type' => 'delivery_fee',
            'amount' => $assignment->provider_fee,
        ]);
        $this->assertGreaterThan(0, (float) $riderProfile->total_earnings);
    }

    public function test_rider_cod_deposit_workflow_with_domestic_admin_approval(): void
    {
        $admin = User::factory()->create(['role' => 'domestic_admin', 'user_type' => 'domestic_admin']);
        $riderUser = User::factory()->create(['role' => 'rider', 'user_type' => 'rider']);
        $riderProfile = RiderProfile::create([
            'user_id' => $riderUser->id,
            'vehicle_type' => 'motorcycle',
            'vehicle_number' => 'BA 22 PA 9999',
            'verification_status' => 'verified',
            'availability_status' => 'online',
            'cod_limit' => 15000,
            'current_outstanding_cod' => 4500,
        ]);

        // Rider submits COD bank transfer deposit
        $depositResponse = $this->actingAs($riderUser)->post(route('rider.delivery.deposit'), [
            'amount' => 4500,
            'deposit_method' => 'bank_transfer',
            'deposit_reference' => 'TXN-987654321',
            'deposit_receipt' => UploadedFile::fake()->create('slip.jpg', 300, 'image/jpeg'),
            'notes' => 'Deposited via Nabil Bank counter',
        ]);

        $depositResponse->assertRedirect();
        $depositResponse->assertSessionHas('success');

        $codLedger = RiderCodLedger::where('rider_profile_id', $riderProfile->id)
            ->where('transaction_type', 'deposited')
            ->first();

        $this->assertNotNull($codLedger);
        $this->assertEquals('pending', $codLedger->approval_status);
        $this->assertEquals(-4500.00, (float) $codLedger->amount);

        // Outstanding COD does not reduce until domestic admin approves
        $riderProfile->refresh();
        $this->assertEquals(4500.00, (float) $riderProfile->current_outstanding_cod);

        // Domestic admin inspects COD ledger console and approves deposit
        $approveResponse = $this->actingAs($admin)->post(route('domestic.ecommerce.riders.cod.approve', $codLedger->id));
        $approveResponse->assertRedirect();
        $approveResponse->assertSessionHas('success');

        $codLedger->refresh();
        $riderProfile->refresh();

        $this->assertEquals('approved', $codLedger->approval_status);
        $this->assertEquals(0.00, (float) $riderProfile->current_outstanding_cod);
    }

    public function test_seller_can_book_multi_leg_delivery_with_domestic_partner_linehaul(): void
    {
        $seller = User::factory()->create(['role' => 'seller', 'user_type' => 'seller']);

        // Create partner
        $partner = DomesticPartner::create([
            'name' => 'Sundar Courier Express',
            'company_name' => 'Sundar Courier Express',
            'email' => 'partner@example.com',
            'code' => 'SCE-NP',
            'phone' => '01-4433221',
            'is_active' => true,
        ]);

        $response = $this->actingAs($seller)->post(route('seller.ecommerce.multileg.store'), [
            'delivery_name' => 'Suman Adhikari',
            'delivery_phone' => '9803112233',
            'delivery_address' => 'Lakeside, Pokhara',
            'destination_city' => 'Pokhara',
            'partner_id' => $partner->id,
            'package_contents' => 'Handmade Pashmina Shawls',
            'parcel_weight' => 2.5,
            'cod_amount' => 8500,
        ]);

        $response->assertRedirect(route('seller.ecommerce.index'));
        $response->assertSessionHas('success');

        // 3 legs created with the same master AWB:
        $leg1 = ShipmentAssignment::where('assignment_type', 'pickup_first_mile')->latest()->first();
        $this->assertNotNull($leg1);

        $assignments = ShipmentAssignment::where('master_awb', $leg1->master_awb)->orderBy('sequence')->get();
        $this->assertCount(3, $assignments);

        $this->assertEquals('pickup_first_mile', $assignments[0]->assignment_type);
        $this->assertEquals(1, $assignments[0]->sequence);

        $this->assertEquals('line_haul', $assignments[1]->assignment_type);
        $this->assertEquals(2, $assignments[1]->sequence);
        $this->assertEquals($partner->id, $assignments[1]->partner_id);

        $this->assertEquals('last_mile', $assignments[2]->assignment_type);
        $this->assertEquals(3, $assignments[2]->sequence);
        $this->assertEquals(8500.00, (float) $assignments[2]->cod_amount);
    }

    public function test_rider_can_manage_service_areas_and_operating_radius(): void
    {
        $riderUser = User::factory()->create(['role' => 'rider', 'user_type' => 'rider']);
        $profile = $riderUser->ensureRiderProfile();

        // 1. Rider accesses service areas view
        $viewResponse = $this->actingAs($riderUser)->get(route('rider.service-areas.index'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Operating Service Areas');

        // 2. Rider adds service area for Lalitpur - Jawalakhel
        $addResponse = $this->actingAs($riderUser)->post(route('rider.service-areas.store'), [
            'province' => 'Bagmati',
            'district' => 'Lalitpur',
            'municipality' => 'Lalitpur Metropolitan City',
            'ward' => '4',
            'area_name' => 'Jawalakhel',
            'service_radius_km' => 12.0,
        ]);

        $addResponse->assertRedirect(route('rider.service-areas.index'));
        $addResponse->assertSessionHas('success');

        $this->assertDatabaseHas('rider_service_areas', [
            'rider_profile_id' => $profile->id,
            'district' => 'Lalitpur',
            'area_name' => 'Jawalakhel',
            'is_active' => true,
        ]);
    }

    public function test_dispatcher_can_direct_assign_qualified_rider_to_shipment(): void
    {
        $admin = User::factory()->create(['role' => 'domestic_admin', 'user_type' => 'domestic_admin']);
        $seller = User::factory()->create(['role' => 'seller', 'user_type' => 'seller']);

        $riderUser = User::factory()->create(['role' => 'rider', 'user_type' => 'rider', 'name' => 'Suman Direct Rider']);
        $profile = RiderProfile::create([
            'user_id' => $riderUser->id,
            'full_name' => 'Suman Direct Rider',
            'vehicle_type' => 'motorcycle',
            'verification_status' => 'verified',
            'availability_status' => 'online',
            'cod_limit' => 20000,
            'current_outstanding_cod' => 0,
        ]);

        // Seller books delivery
        $this->actingAs($seller)->post(route('seller.ecommerce.direct.store'), [
            'delivery_name' => 'Kiran Thapa',
            'delivery_phone' => '9841999999',
            'delivery_address' => 'Balaju, Kathmandu',
            'distance_km' => 6.0,
            'parcel_weight' => 2.0,
            'cod_amount' => 4500,
            'vehicle_type' => 'motorcycle',
        ]);

        $assignment = ShipmentAssignment::latest()->first();
        $this->assertNotNull($assignment);

        // Dispatcher assigns rider directly (Method A)
        $assignResponse = $this->actingAs($admin)->post(route('domestic.ecommerce.riders.assign'), [
            'assignment_id' => $assignment->id,
            'rider_profile_id' => $profile->id,
        ]);

        $assignResponse->assertRedirect();
        $assignResponse->assertSessionHas('success');

        $assignment->refresh();
        $this->assertEquals($profile->id, $assignment->rider_profile_id);
        $this->assertStringContainsString('Directly Assigned', $assignment->current_custody);
    }

    public function test_master_awb_universal_tracking_renders_milestones_and_masks_customer_phone(): void
    {
        $seller = User::factory()->create(['role' => 'seller', 'user_type' => 'seller', 'business_name' => 'Alpha Goods']);

        // Seller books delivery
        $this->actingAs($seller)->post(route('seller.ecommerce.direct.store'), [
            'delivery_name' => 'Sarita Sharma',
            'delivery_phone' => '9841234567',
            'delivery_address' => 'Baluwatar, Kathmandu',
            'distance_km' => 3.5,
            'parcel_weight' => 1.2,
            'cod_amount' => 1800,
            'vehicle_type' => 'motorcycle',
        ]);

        $assignment = ShipmentAssignment::latest()->first();
        $this->assertNotNull($assignment);

        // Customer tracks by Master AWB
        $trackResponse = $this->get(route('tracking.show', $assignment->master_awb));
        $trackResponse->assertStatus(200);
        $trackResponse->assertSee($assignment->master_awb);
        $trackResponse->assertSee('984****567'); // Masked customer phone
        $trackResponse->assertDontSee('9841234567'); // Exact phone hidden
        $trackResponse->assertSee('Master Consignment Progression');
    }

    public function test_delivery_failure_generates_automatic_rto_return_assignment(): void
    {
        $seller = User::factory()->create(['role' => 'seller', 'user_type' => 'seller', 'business_name' => 'Nepal Shop']);
        $riderUser = User::factory()->create(['role' => 'rider', 'user_type' => 'rider']);
        $profile = RiderProfile::create([
            'user_id' => $riderUser->id,
            'full_name' => 'Hari Rider',
            'vehicle_type' => 'motorcycle',
            'verification_status' => 'verified',
            'availability_status' => 'online',
            'cod_limit' => 10000,
        ]);

        $this->actingAs($seller)->post(route('seller.ecommerce.direct.store'), [
            'delivery_name' => 'Pradeep Karki',
            'delivery_phone' => '9801239999',
            'delivery_address' => 'Kapan, Kathmandu',
            'distance_km' => 7.0,
            'parcel_weight' => 1.5,
            'cod_amount' => 2000,
        ]);

        $assignment = ShipmentAssignment::latest()->first();
        $this->actingAs($riderUser)->post(route('rider.delivery.accept', $assignment->id));

        // Rider reports failed delivery (Customer refused)
        $this->actingAs($riderUser)->post(route('rider.delivery.verify-pickup', $assignment->id), ['pickup_otp' => $assignment->pickup_otp]);
        $failResponse = $this->actingAs($riderUser)->post(route('rider.delivery.fail', $assignment->id), [
            'failure_reason' => 'customer_refused',
            'failure_notes' => 'Customer refused parcel at doorstep',
        ]);

        $failResponse->assertRedirect(route('rider.delivery.my'));

        $assignment->refresh();
        $this->assertEquals('failed', $assignment->status);

        // Automatic RTO Return assignment generated
        $rtoAssignment = ShipmentAssignment::where('master_awb', $assignment->master_awb.'-RTO')->first();
        $this->assertNotNull($rtoAssignment);
        $this->assertEquals('return_rto', $rtoAssignment->assignment_type);
        $this->assertEquals(60.00, (float) $rtoAssignment->provider_fee);
    }

    public function test_seller_can_rate_rider_and_recalculate_trust_score(): void
    {
        $seller = User::factory()->create(['role' => 'seller', 'user_type' => 'seller']);
        $riderUser = User::factory()->create(['role' => 'rider', 'user_type' => 'rider']);
        $profile = RiderProfile::create([
            'user_id' => $riderUser->id,
            'full_name' => 'Ramesh Top Rider',
            'vehicle_type' => 'motorcycle',
            'verification_status' => 'verified',
            'availability_status' => 'online',
            'cod_limit' => 10000,
            'total_completed_deliveries' => 50,
        ]);

        $this->actingAs($seller)->post(route('seller.ecommerce.direct.store'), [
            'delivery_name' => 'Manoj Shrestha',
            'delivery_phone' => '9800000000',
            'delivery_address' => 'Patan, Lalitpur',
            'distance_km' => 4.0,
            'parcel_weight' => 1.0,
            'cod_amount' => 0,
        ]);

        $assignment = ShipmentAssignment::latest()->first();
        $this->actingAs($riderUser)->post(route('rider.delivery.accept', $assignment->id));
        $this->actingAs($riderUser)->post(route('rider.delivery.verify-pickup', $assignment->id), ['pickup_otp' => $assignment->pickup_otp]);
        $this->actingAs($riderUser)->post(route('rider.delivery.complete', $assignment->id), ['delivery_otp' => $assignment->delivery_otp]);

        // Seller rates rider 5 stars
        $rateResponse = $this->actingAs($seller)->post(route('seller.ecommerce.rate', $assignment->id), [
            'overall_rating' => 5,
            'professionalism' => 5,
            'timeliness' => 5,
            'parcel_handling' => 5,
            'communication' => 5,
            'feedback' => 'Exceptional and punctual service!',
        ]);

        $rateResponse->assertRedirect();
        $rateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('rider_ratings', [
            'rider_profile_id' => $profile->id,
            'assignment_id' => $assignment->id,
            'overall_rating' => 5,
        ]);

        $profile->refresh();
        $this->assertEquals(5.00, (float) $profile->rating);
        $this->assertGreaterThanOrEqual(85, $profile->trust_score);
        $this->assertEquals('trusted', $profile->badge_status);
    }
}
