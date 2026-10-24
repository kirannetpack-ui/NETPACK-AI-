<?php

namespace Tests\Feature;

use App\Models\DomesticPartner;
use App\Models\RiderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementConsoleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'email' => 'superadmin@netpack.test',
            'user_type' => 'admin',
            'verification_status' => 'approved',
        ]);
    }

    public function test_admin_can_view_edit_page_with_dynamic_console_and_telemetry(): void
    {
        $rider = User::factory()->create([
            'name' => 'Bikram Thapa',
            'email' => 'bikram.rider@netpack.test',
            'user_type' => 'rider',
            'verification_status' => 'approved',
        ]);
        $profile = $rider->ensureRiderProfile();
        $profile->update([
            'trust_score' => 95,
            'cod_level' => 'level_3',
            'cod_limit' => 30000.00,
            'rating' => 4.9,
            'vehicle_type' => 'motorcycle',
            'vehicle_number' => 'BA-88-PA-9999',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $rider->id));

        $response->assertOk();
        $response->assertSee('Edit User Account', false);
        $response->assertSee('Functionality Console', false);
        $response->assertSee('Independent Delivery Rider Console', false);
        $response->assertSee('BA-88-PA-9999', false);
        $response->assertSee('30,000', false);
    }

    public function test_admin_can_update_rider_with_fleet_dossier_and_cod_risk_limits(): void
    {
        $rider = User::factory()->create([
            'name' => 'Suman Gurung',
            'email' => 'suman.gurung@netpack.test',
            'user_type' => 'rider',
            'verification_status' => 'pending',
        ]);
        $rider->ensureRiderProfile();

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $rider->id), [
            'name' => 'Suman Gurung Senior',
            'email' => 'suman.gurung@netpack.test',
            'user_type' => 'rider',
            'phone' => '9841999888',
            'verification_status' => 'approved',
            'vehicle_type' => 'electric_bike',
            'vehicle_number' => 'PRA-3-02-001-PA-5555',
            'driving_license_number' => '01-08-99887766',
            'license_expiry_date' => '2028-12-31',
            'cod_level' => 'level_4',
            'cod_limit' => 50000.00,
            'trust_score' => 92,
            'badge_status' => 'preferred',
            'rating' => 4.8,
            'availability_status' => 'online',
            'service_radius_km' => 20.0,
            'max_carrying_weight' => 35.0,
            'max_active_packages' => 15,
            'emergency_contact' => 'Hari Gurung (9800000000)',
            'affiliation' => 'pathao',
            'affiliation_reference_id' => 'PTH-443322',
            'payout_method' => 'esewa',
            'esewa_id' => '9841999888',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $rider->refresh();
        $this->assertEquals('Suman Gurung Senior', $rider->name);
        $this->assertEquals('approved', $rider->verification_status);

        $profile = RiderProfile::where('user_id', $rider->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('scooter', $profile->vehicle_type);
        $this->assertEquals('PRA-3-02-001-PA-5555', $profile->vehicle_number);
        $this->assertEquals('01-08-99887766', $profile->driving_license_number);
        $this->assertEquals('2028-12-31', $profile->license_expiry_date?->format('Y-m-d'));
        $this->assertEquals('level_4', $profile->cod_level);
        $this->assertEquals(50000.00, (float) $profile->cod_limit);
        $this->assertEquals(92, $profile->trust_score);
        $this->assertEquals('preferred', $profile->badge_status);
        $this->assertEquals('pathao', $profile->affiliation);
        $this->assertEquals('esewa', $profile->payout_method);
        $this->assertEquals('9841999888', $profile->esewa_id);
    }

    public function test_admin_can_update_seller_with_store_profile_and_settlement_cycle(): void
    {
        $seller = User::factory()->create([
            'name' => 'Everest Fashion',
            'email' => 'store@everestfashion.test',
            'user_type' => 'seller',
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $seller->id), [
            'name' => 'Everest Fashion House',
            'email' => 'store@everestfashion.test',
            'user_type' => 'seller',
            'phone' => '9851000111',
            'verification_status' => 'approved',
            'business_name' => 'Everest Fashion Online',
            'company_name' => 'Everest Apparel Pvt. Ltd.',
            'pan_number' => '601234999',
            'merchant_category' => 'fashion',
            'contact_person' => 'Pooja Shrestha',
            'pickup_address' => 'New Road Super Market, Stall 44, Kathmandu',
            'return_address' => 'New Road Central Warehouse, Kathmandu',
            'settlement_cycle' => 'daily',
            'commission_rate' => 2.5,
            'discount_tier' => 'gold',
            'bank_name' => 'Nabil Bank',
            'account_holder_name' => 'Everest Apparel Pvt. Ltd.',
            'account_number' => '0100100200300',
            'branch' => 'New Road Branch',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $seller->refresh();
        $this->assertEquals('Everest Fashion House', $seller->name);
        $this->assertEquals('Everest Fashion Online', $seller->business_name);
        $this->assertEquals('Everest Apparel Pvt. Ltd.', $seller->company_name);
        $this->assertEquals('Pooja Shrestha', $seller->contact_person);
        $this->assertEquals('Nabil Bank', $seller->bank_name);
        $this->assertEquals('0100100200300', $seller->account_number);
        $this->assertEquals('New Road Branch', $seller->ifsc_code);

        $metadata = $seller->metadata;
        $this->assertIsArray($metadata);
        $this->assertEquals('601234999', $metadata['pan_number']);
        $this->assertEquals('fashion', $metadata['merchant_category']);
        $this->assertEquals('daily', $metadata['settlement_cycle']);
        $this->assertEquals('gold', $metadata['discount_tier']);
        $this->assertEquals(2.5, (float) $metadata['commission_rate']);
    }

    public function test_admin_can_update_domestic_partner_carrier(): void
    {
        $partnerUser = User::factory()->create([
            'name' => 'Pokhara Cargo Lines',
            'email' => 'partner@pokharacargo.test',
            'user_type' => 'partner',
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $partnerUser->id), [
            'name' => 'Pokhara Express Cargo Hub',
            'email' => 'partner@pokharacargo.test',
            'user_type' => 'partner',
            'phone' => '9846001122',
            'verification_status' => 'approved',
            'partner_code' => 'PRT-PKR-01',
            'company_name' => 'Pokhara Express Logistics Pvt. Ltd.',
            'pan_number' => '600112233',
            'service_type' => 'hub_to_hub_linehaul',
            'margin_percentage' => 12.5,
            'base_hub' => 'Pokhara Transit Terminal',
            'api_status' => 'active',
            'kyc_verified' => 1,
            'province' => 'Gandaki',
            'district' => 'Kaski',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $partnerUser->refresh();
        $this->assertEquals('Pokhara Express Cargo Hub', $partnerUser->name);

        $partner = DomesticPartner::where('id', $partnerUser->id)->first();
        $this->assertNotNull($partner);
        $this->assertEquals('PRT-PKR-01', $partner->code);
        $this->assertEquals('Pokhara Express Logistics Pvt. Ltd.', $partner->company_name);
        $this->assertEquals('600112233', $partner->pan_number);
        $this->assertEquals('standard', $partner->service_type);
        $this->assertEquals(12.5, (float) $partner->margin_percentage);
        $this->assertTrue((bool) $partner->kyc_verified);
    }

    public function test_admin_can_update_corporate_client_account(): void
    {
        $client = User::factory()->create([
            'name' => 'Himalayan Enterprises',
            'email' => 'corporate@himalayan.test',
            'user_type' => 'client',
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $client->id), [
            'name' => 'Himalayan Enterprises Group',
            'email' => 'corporate@himalayan.test',
            'user_type' => 'client',
            'phone' => '9801002233',
            'verification_status' => 'approved',
            'company_name' => 'Himalayan Trade Syndicate Ltd.',
            'pan_number' => '609988776',
            'account_category' => 'enterprise_b2b',
            'billing_mode' => 'monthly_net_30',
            'credit_limit' => 150000.00,
            'account_manager' => 'Rajesh Sharma (Key Accounts)',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $client->refresh();
        $this->assertEquals('Himalayan Enterprises Group', $client->name);
        $this->assertEquals('Himalayan Trade Syndicate Ltd.', $client->company_name);

        $metadata = $client->metadata;
        $this->assertIsArray($metadata);
        $this->assertEquals('609988776', $metadata['pan_number']);
        $this->assertEquals('enterprise_b2b', $metadata['account_category']);
        $this->assertEquals('monthly_net_30', $metadata['billing_mode']);
        $this->assertEquals(150000.00, (float) $metadata['credit_limit']);
        $this->assertEquals('Rajesh Sharma (Key Accounts)', $metadata['account_manager']);
    }

    public function test_admin_can_update_operations_staff_account(): void
    {
        $staff = User::factory()->create([
            'name' => 'Anita Thapa',
            'email' => 'anita.staff@netpack.test',
            'user_type' => 'staff',
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $staff->id), [
            'name' => 'Anita Thapa Senior',
            'email' => 'anita.staff@netpack.test',
            'user_type' => 'staff',
            'phone' => '9812345678',
            'verification_status' => 'approved',
            'service_scope' => 'domestic',
            'department' => 'hub_dispatch',
            'hub_location' => 'Kathmandu Central Cargo Complex',
            'designation' => 'Senior Terminal Dispatch Controller',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $staff->refresh();
        $this->assertEquals('Anita Thapa Senior', $staff->name);
        $this->assertEquals('domestic', $staff->service_scope);

        $metadata = $staff->metadata;
        $this->assertIsArray($metadata);
        $this->assertEquals('hub_dispatch', $metadata['department']);
        $this->assertEquals('Kathmandu Central Cargo Complex', $metadata['hub_location']);
        $this->assertEquals('Senior Terminal Dispatch Controller', $metadata['designation']);
    }
}
