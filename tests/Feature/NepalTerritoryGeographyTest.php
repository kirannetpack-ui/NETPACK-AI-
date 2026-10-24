<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\DomesticPartner;
use App\Models\PickupRequest;
use App\Models\User;
use App\Services\NepalGeographicalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NepalTerritoryGeographyTest extends TestCase
{
    use RefreshDatabase;

    public function test_nepal_geographical_service_contains_7_provinces_and_77_districts(): void
    {
        $provinces = NepalGeographicalService::getProvinces();
        $this->assertCount(7, $provinces);

        $districts = NepalGeographicalService::getAllDistricts();
        $this->assertCount(77, $districts);

        // Spot-check each province
        $koshiDistricts = NepalGeographicalService::getDistrictsByProvince('Koshi Province');
        $this->assertCount(14, $koshiDistricts);
        $this->assertContains('Jhapa', $koshiDistricts);
        $this->assertContains('Morang', $koshiDistricts);

        $madheshDistricts = NepalGeographicalService::getDistrictsByProvince('Madhesh Province');
        $this->assertCount(8, $madheshDistricts);
        $this->assertContains('Parsa', $madheshDistricts);

        $bagmatiDistricts = NepalGeographicalService::getDistrictsByProvince('Bagmati Province');
        $this->assertCount(13, $bagmatiDistricts);
        $this->assertContains('Kathmandu', $bagmatiDistricts);
        $this->assertContains('Lalitpur', $bagmatiDistricts);
        $this->assertContains('Bhaktapur', $bagmatiDistricts);
        $this->assertContains('Chitwan', $bagmatiDistricts);

        $gandakiDistricts = NepalGeographicalService::getDistrictsByProvince('Gandaki Province');
        $this->assertCount(11, $gandakiDistricts);
        $this->assertContains('Kaski', $gandakiDistricts);

        $lumbiniDistricts = NepalGeographicalService::getDistrictsByProvince('Lumbini Province');
        $this->assertCount(12, $lumbiniDistricts);
        $this->assertContains('Rupandehi', $lumbiniDistricts);

        $karnaliDistricts = NepalGeographicalService::getDistrictsByProvince('Karnali Province');
        $this->assertCount(10, $karnaliDistricts);
        $this->assertContains('Surkhet', $karnaliDistricts);

        $sudurpashchimDistricts = NepalGeographicalService::getDistrictsByProvince('Sudurpashchim Province');
        $this->assertCount(9, $sudurpashchimDistricts);
        $this->assertContains('Kailali', $sudurpashchimDistricts);

        // Reverse lookup
        $this->assertEquals('Bagmati Province', NepalGeographicalService::getProvinceForDistrict('Kathmandu'));
        $this->assertEquals('Gandaki Province', NepalGeographicalService::getProvinceForDistrict('Kaski'));
        $this->assertEquals('Koshi Province', NepalGeographicalService::getProvinceForDistrict('Morang'));
    }

    public function test_client_domestic_pickup_view_renders_territory_pickers(): void
    {
        $client = User::factory()->create([
            'email' => 'pickup.client@netpack.test',
            'user_type' => 'client',
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($client)->get(route('domestic.pickup.create'));
        $response->assertOk();
        $response->assertSee('Pickup Province / Sector', false);
        $response->assertSee('Pickup District (Under Province)', false);
        $response->assertSee('Delivery Province / Sector', false);
        $response->assertSee('Delivery District (Under Province)', false);
        $response->assertSee('Bagmati Province', false);
        $response->assertSee('Koshi Province', false);
        $response->assertSee('Type to search district', false);
    }

    public function test_client_can_submit_domestic_pickup_with_provinces_and_districts(): void
    {
        $client = User::factory()->create([
            'email' => 'client.pickup.submit@netpack.test',
            'user_type' => 'client',
            'verification_status' => 'approved',
        ]);

        $payload = [
            'service_tier' => 'standard',
            'pickup_address' => 'New Road Kathmandu Central Depot',
            'pickup_province' => 'Bagmati Province',
            'pickup_district' => 'Kathmandu',
            'pickup_municipality' => 'Kathmandu Metropolitan City',
            'pickup_ward_no' => '22',
            'delivery_address' => 'Lakeside Pokhara Courier Hub',
            'delivery_province' => 'Gandaki Province',
            'delivery_district' => 'Kaski',
            'delivery_municipality' => 'Pokhara Metropolitan City',
            'delivery_ward_no' => '6',
            'scheduled_pickup_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'items_description' => 'Important business consignments',
            'estimated_weight_kg' => 2.5,
        ];

        $response = $this->actingAs($client)->post(route('domestic.pickup.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('pickup_requests', [
            'pickup_province' => 'Bagmati Province',
            'pickup_district' => 'Kathmandu',
            'delivery_province' => 'Gandaki Province',
            'delivery_district' => 'Kaski',
            'items_description' => 'Important business consignments',
        ]);
    }

    public function test_partner_without_district_can_set_operating_territory(): void
    {
        $partnerUser = User::factory()->create([
            'email' => 'partner.nodistrict@netpack.test',
            'user_type' => 'partner',
            'role' => 'partner',
            'district' => null,
            'province' => null,
            'verification_status' => 'approved',
        ]);

        DomesticPartner::firstOrCreate(
            ['id' => $partnerUser->id],
            [
                'code' => 'KTM-TEST',
                'name' => 'KTM Partner',
                'company_name' => 'KTM Logistics',
                'email' => $partnerUser->email,
                'password' => bcrypt('password123'),
                'phone' => '9800000000',
            ]
        );

        // 1. Visit index - renders territory setup form instead of dead end
        $response = $this->actingAs($partnerUser)->get(route('partner.zones.index'));
        $response->assertOk();
        $response->assertSee('Set Your Operating Territory', false);
        $response->assertSee('Set Operating District', false);

        // 2. Set operating district
        $setResponse = $this->actingAs($partnerUser)->post(route('partner.zones.set-operating-district'), [
            'province' => 'Gandaki Province',
            'district' => 'Kaski',
        ]);

        $setResponse->assertRedirect(route('partner.zones.index'));
        $partnerUser->refresh();
        $this->assertEquals('Kaski', $partnerUser->district);
        $this->assertEquals('Gandaki Province', $partnerUser->province);
    }

    public function test_partner_can_create_zone_with_territory_selection(): void
    {
        $partnerUser = User::factory()->create([
            'email' => 'partner.zonecreate@netpack.test',
            'user_type' => 'partner',
            'role' => 'partner',
            'district' => 'Kathmandu',
            'province' => 'Bagmati Province',
            'verification_status' => 'approved',
        ]);

        DomesticPartner::firstOrCreate(
            ['id' => $partnerUser->id],
            [
                'code' => 'KTM-ZN',
                'name' => 'KTM Metro Partner',
                'company_name' => 'KTM Hub',
                'email' => $partnerUser->email,
                'password' => bcrypt('password123'),
                'phone' => '9800000000',
            ]
        );

        // 1. Visit create view
        $viewResponse = $this->actingAs($partnerUser)->get(route('partner.zones.create'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('Coverage Territory & District', false);
        $viewResponse->assertSee('Operating Province / Sector', false);

        // 2. Submit zone
        $postResponse = $this->actingAs($partnerUser)->post(route('partner.zones.store'), [
            'zone_name' => 'Kathmandu Core Ring Road Zone',
            'province' => 'Bagmati Province',
            'district' => 'Kathmandu',
            'districts' => ['Kathmandu', 'Lalitpur'],
            'municipalities' => 'Kathmandu Metro, Lalitpur Metro',
            'wards' => '1,2,3,4,5',
            'description' => 'Central ring road express corridor',
            'standard_base_rate' => 90.00,
            'standard_per_kg_rate' => 20.00,
            'standard_estimated_hours' => 24,
        ]);

        $postResponse->assertRedirect(route('partner.zones.index'));
        $this->assertDatabaseHas('delivery_zones', [
            'partner_user_id' => $partnerUser->id,
            'zone_name' => 'Kathmandu Core Ring Road Zone',
        ]);
    }

    public function test_admin_can_set_user_province_and_district(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.territory@netpack.test',
            'user_type' => 'admin',
            'verification_status' => 'approved',
        ]);

        $targetUser = User::factory()->create([
            'email' => 'target.partner@netpack.test',
            'user_type' => 'partner',
            'district' => null,
            'province' => null,
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser->id), [
            'name' => 'Updated Pokhara Express Partner',
            'email' => $targetUser->email,
            'user_type' => 'partner',
            'verification_status' => 'approved',
            'province' => 'Gandaki Province',
            'district' => 'Kaski',
            'permanent_address' => 'Pokhara New Road Central',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $targetUser->refresh();
        $this->assertEquals('Gandaki Province', $targetUser->province);
        $this->assertEquals('Kaski', $targetUser->district);
    }

    public function test_user_registration_saves_province_and_district(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Biratnagar Merchant',
            'email' => 'biratnagar.merchant@netpack.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'phone' => '9841000000',
            'dob' => '1995-05-15',
            'gender' => 'male',
            'nationality' => 'Nepali',
            'user_type' => 'customer',
            'province' => 'Koshi Province',
            'district' => 'Morang',
            'city' => 'Biratnagar',
            'address' => 'Main Road, Ward 2',
            'terms' => 'on',
        ]);

        $response->assertRedirect(route('registration.pending'));
        $this->assertDatabaseHas('users', [
            'email' => 'biratnagar.merchant@netpack.test',
            'province' => 'Koshi Province',
            'district' => 'Morang',
            'city' => 'Biratnagar',
        ]);
    }

    public function test_domestic_admin_can_create_partner_with_territory(): void
    {
        $admin = User::factory()->create([
            'email' => 'dom.admin@netpack.test',
            'user_type' => 'domestic_admin',
            'role' => 'domestic_admin',
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('domestic.partners.store'), [
            'name' => 'Chitwan Express Hub',
            'company_name' => 'Chitwan Cargo Pvt Ltd',
            'email' => 'chitwan.hub@netpack.test',
            'phone' => '9855011111',
            'password' => 'Chitwan1234!',
            'password_confirmation' => 'Chitwan1234!',
            'province' => 'Bagmati Province',
            'district' => 'Chitwan',
            'city' => 'Bharatpur',
            'verification_status' => 'approved',
        ]);

        $response->assertRedirect(route('domestic.partners'));
        $this->assertDatabaseHas('users', [
            'email' => 'chitwan.hub@netpack.test',
            'user_type' => 'partner',
            'province' => 'Bagmati Province',
            'district' => 'Chitwan',
        ]);
    }

    public function test_domestic_admin_can_create_zone_with_districts(): void
    {
        $admin = User::factory()->create([
            'email' => 'zone.admin@netpack.test',
            'user_type' => 'domestic_admin',
            'role' => 'domestic_admin',
            'verification_status' => 'approved',
        ]);

        $partner = User::factory()->create([
            'email' => 'madhesh.partner@netpack.test',
            'user_type' => 'partner',
            'role' => 'partner',
            'province' => 'Madhesh Province',
            'district' => 'Parsa',
        ]);

        $domPartner = DomesticPartner::firstOrCreate(
            ['email' => $partner->email],
            [
                'code' => 'MDH-01',
                'name' => 'Madhesh Partner',
                'company_name' => 'Birgunj Hub',
                'email' => $partner->email,
                'password' => bcrypt('password123'),
                'phone' => '9855000000',
                'district' => 'Parsa',
                'province' => 'Madhesh Province',
                'address' => 'Parsa, Birgunj',
                'city' => 'Birgunj',
            ]
        );

        $response = $this->actingAs($admin)->post(route('domestic.zones.store'), [
            'partner_id' => $partner->id,
            'zone_name' => 'Birgunj Border Corridors',
            'zone_type' => 'urban',
            'province' => 'Madhesh Province',
            'districts' => ['Parsa', 'Bara'],
            'municipalities' => 'Birgunj Metropolitan City, Kalaiya Sub-Metro',
            'wards' => '1,2,3',
            'description' => 'Industrial cross-border hub coverage',
        ]);

        $response->assertRedirect(route('domestic.zones'));
        $this->assertDatabaseHas('delivery_zones', [
            'zone_name' => 'Birgunj Border Corridors',
            'partner_id' => $domPartner->id,
        ]);

        $zone = DeliveryZone::where('zone_name', 'Birgunj Border Corridors')->first();
        $this->assertEquals(['Parsa', 'Bara'], $zone->districts);
    }

    public function test_rider_can_update_operating_territory(): void
    {
        $rider = User::factory()->create([
            'email' => 'rider.territory@netpack.test',
            'user_type' => 'rider',
            'role' => 'rider',
            'province' => null,
            'district' => null,
            'vehicle_type' => 'bike',
            'license_number' => 'BA-2-PA-1234',
        ]);

        $response = $this->actingAs($rider)->put(route('rider.update-profile'), [
            'name' => 'Lumbini Speed Rider',
            'phone' => '9811122233',
            'address' => 'Traffic Chowk, Butwal',
            'city' => 'Butwal',
            'province' => 'Lumbini Province',
            'district' => 'Rupandehi',
            'vehicle_type' => 'bike',
            'license_number' => 'LU-1-PA-9988',
        ]);

        $response->assertRedirect();
        $rider->refresh();
        $this->assertEquals('Lumbini Province', $rider->province);
        $this->assertEquals('Rupandehi', $rider->district);
    }

    public function test_nepal_districts_have_constitutional_capitals_and_depots(): void
    {
        $allDistricts = NepalGeographicalService::getAllDistricts();
        $this->assertCount(77, $allDistricts);

        foreach ($allDistricts as $district) {
            $capital = NepalGeographicalService::getDistrictCapital($district);
            $this->assertNotEmpty($capital, "District {$district} must have a designated capital/headquarters.");

            $depotName = NepalGeographicalService::getDepotNameForDistrict($district);
            $this->assertStringContainsString($district, $depotName);
            $this->assertStringContainsString($capital, $depotName);
        }

        // Specific checks for primary hubs
        $this->assertEquals('Pokhara', NepalGeographicalService::getDistrictCapital('Kaski'));
        $this->assertEquals('Biratnagar', NepalGeographicalService::getDistrictCapital('Morang'));
        $this->assertEquals('Birgunj', NepalGeographicalService::getDistrictCapital('Parsa'));
        $this->assertEquals('Bharatpur', NepalGeographicalService::getDistrictCapital('Chitwan'));
        $this->assertEquals('Janakpur', NepalGeographicalService::getDistrictCapital('Dhanusha'));
        $this->assertEquals('Kathmandu', NepalGeographicalService::getDistrictCapital('Kathmandu'));
        $this->assertEquals('Patan (Lalitpur)', NepalGeographicalService::getDistrictCapital('Lalitpur'));
        $this->assertEquals('Nepalgunj', NepalGeographicalService::getDistrictCapital('Banke'));
        $this->assertEquals('Birendranagar', NepalGeographicalService::getDistrictCapital('Surkhet'));
        $this->assertEquals('Dhangadhi', NepalGeographicalService::getDistrictCapital('Kailali'));
    }

    public function test_partner_can_set_multi_province_and_multi_district_coverage(): void
    {
        $partnerUser = User::factory()->create([
            'email' => 'multi.partner@netpack.test',
            'user_type' => 'partner',
            'role' => 'partner',
            'district' => null,
            'province' => null,
            'verification_status' => 'approved',
        ]);

        $domPartner = DomesticPartner::firstOrCreate(
            ['email' => $partnerUser->email],
            [
                'id' => $partnerUser->id,
                'code' => 'MULTI-PTN',
                'name' => 'Multi-Region Logistics',
                'company_name' => 'Multi-Region Express Pvt Ltd',
                'password' => bcrypt('Password123!'),
                'phone' => '9801122334',
                'district' => 'Kathmandu',
                'province' => 'Bagmati Province',
                'address' => 'Central Hub',
                'city' => 'Kathmandu',
            ]
        );

        $selectedProvinces = ['Bagmati Province', 'Gandaki Province'];
        $selectedDistricts = ['Kathmandu', 'Lalitpur', 'Bhaktapur', 'Kaski', 'Tanahun'];

        $response = $this->actingAs($partnerUser)->post(route('partner.zones.set-operating-district'), [
            'provinces' => $selectedProvinces,
            'districts' => $selectedDistricts,
            'auto_create_depots' => 1,
        ]);

        $response->assertRedirect(route('partner.zones.index'));

        $partnerUser->refresh();
        $this->assertEquals($selectedProvinces, $partnerUser->operating_provinces);
        $this->assertEquals($selectedDistricts, $partnerUser->operating_districts);
        $this->assertEquals('Kathmandu', $partnerUser->district);
        $this->assertEquals('Bagmati Province', $partnerUser->province);
        $this->assertTrue($partnerUser->hasOperatingTerritory());

        $domPartner->refresh();
        $this->assertEquals($selectedProvinces, $domPartner->operating_provinces);
        $this->assertEquals($selectedDistricts, $domPartner->operating_districts);

        // Verify each operating district has an auto-provisioned depot in its capital
        $this->assertDatabaseHas('delivery_zones', [
            'partner_user_id' => $partnerUser->id,
            'zone_name' => 'Kathmandu District Depot (Kathmandu)',
        ]);

        $this->assertDatabaseHas('delivery_zones', [
            'partner_user_id' => $partnerUser->id,
            'zone_name' => 'Kaski District Depot (Pokhara)',
        ]);

        $this->assertDatabaseHas('delivery_zones', [
            'partner_user_id' => $partnerUser->id,
            'zone_name' => 'Tanahun District Depot (Damauli (Vyas))',
        ]);
    }

    public function test_partner_can_select_all_districts_in_a_province(): void
    {
        $partnerUser = User::factory()->create([
            'email' => 'madhesh.full@netpack.test',
            'user_type' => 'partner',
            'role' => 'partner',
            'verification_status' => 'approved',
        ]);

        DomesticPartner::firstOrCreate(
            ['email' => $partnerUser->email],
            [
                'id' => $partnerUser->id,
                'code' => 'MDH-FULL',
                'name' => 'Madhesh Full Network',
                'company_name' => 'Madhesh Logistics',
                'password' => bcrypt('Password123!'),
                'phone' => '9809988776',
                'district' => 'Parsa',
                'province' => 'Madhesh Province',
                'address' => 'Birgunj Hub',
                'city' => 'Birgunj',
            ]
        );

        // Madhesh Province has 8 districts
        $madheshDistricts = NepalGeographicalService::getDistrictsByProvince('Madhesh Province');
        $this->assertCount(8, $madheshDistricts);

        $response = $this->actingAs($partnerUser)->post(route('partner.zones.set-operating-district'), [
            'provinces' => ['Madhesh Province'],
            'districts' => $madheshDistricts,
            'auto_create_depots' => 1,
        ]);

        $response->assertRedirect(route('partner.zones.index'));

        $partnerUser->refresh();
        $this->assertCount(8, $partnerUser->getOperatingDistricts());
        $this->assertContains('Parsa', $partnerUser->getOperatingDistricts());
        $this->assertContains('Dhanusha', $partnerUser->getOperatingDistricts());

        // Assert capital depot created for Janakpur and Birgunj
        $this->assertDatabaseHas('delivery_zones', [
            'partner_user_id' => $partnerUser->id,
            'zone_name' => 'Parsa District Depot (Birgunj)',
        ]);
        $this->assertDatabaseHas('delivery_zones', [
            'partner_user_id' => $partnerUser->id,
            'zone_name' => 'Dhanusha District Depot (Janakpur)',
        ]);
    }
}
