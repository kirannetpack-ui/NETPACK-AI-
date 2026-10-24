<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\InternationalRate;
use App\Models\OverseasHub;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HubAgencyConsolidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure migrations have executed
    }

    public function test_many_to_many_relationship_between_hubs_and_agencies()
    {
        // 1. Create two Gateway Hubs (Country & Airport)
        $dubaiHub = OverseasHub::create([
            'hub_code' => 'DXB',
            'hub_name' => 'Dubai Gateway Cargo Hub',
            'country' => 'United Arab Emirates',
            'location' => 'Dubai',
            'airport_name' => 'Dubai International Airport (DXB)',
            'mode_type' => 'DDP',
            'coverage_countries' => ['United Arab Emirates', 'Saudi Arabia', 'Qatar'],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $londonHub = OverseasHub::create([
            'hub_code' => 'LHR',
            'hub_name' => 'London Heathrow Gateway Hub',
            'country' => 'United Kingdom',
            'location' => 'London',
            'airport_name' => 'London Heathrow Cargo Terminal (LHR)',
            'mode_type' => 'DDP',
            'coverage_countries' => ['United Kingdom', 'Germany', 'France'],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // 2. Create Agency 1 operating in BOTH Dubai and London hubs
        $agencyGlobal = Agency::create([
            'name' => 'Apex World Cargo Logistics LLC',
            'code' => 'APEX-GLOBAL',
            'country' => 'United Arab Emirates',
            'city' => 'Dubai Cargo Village',
            'address' => 'DAFZA Terminal 2',
            'phone' => '+971-4-111-2222',
            'primary_contact' => 'Tariq Al-Mansoor',
            'email' => 'ops@apex-cargo.com',
            'password' => bcrypt('Secret@123'),
            'is_active' => true,
        ]);
        $agencyGlobal->hubs()->sync([$dubaiHub->id, $londonHub->id]);

        // 3. Create Agency 2 operating ONLY in Dubai hub
        $agencyDubaiOnly = Agency::create([
            'name' => 'Gulf Express Clearance Co.',
            'code' => 'GULF-EXP',
            'country' => 'United Arab Emirates',
            'city' => 'Dubai',
            'address' => 'Cargo Mega Terminal 1',
            'phone' => '+971-4-333-4444',
            'primary_contact' => 'Rashid Khan',
            'email' => 'clearance@gulfexpress.ae',
            'password' => bcrypt('Secret@123'),
            'is_active' => true,
        ]);
        $agencyDubaiOnly->hubs()->sync([$dubaiHub->id]);

        // Verify Agency 1 belongs to multiple hubs
        $agencyGlobal->refresh();
        $this->assertCount(2, $agencyGlobal->hubs);
        $this->assertTrue($agencyGlobal->hubs->contains('id', $dubaiHub->id));
        $this->assertTrue($agencyGlobal->hubs->contains('id', $londonHub->id));

        // Verify Dubai Hub has multiple agencies
        $dubaiHub->refresh();
        $this->assertCount(2, $dubaiHub->agencies);
        $this->assertTrue($dubaiHub->agencies->contains('id', $agencyGlobal->id));
        $this->assertTrue($dubaiHub->agencies->contains('id', $agencyDubaiOnly->id));

        // Verify London Hub has only Agency 1
        $londonHub->refresh();
        $this->assertCount(1, $londonHub->agencies);
        $this->assertTrue($londonHub->agencies->contains('id', $agencyGlobal->id));
    }

    public function test_rates_can_be_entered_according_to_hub_agencies()
    {
        $admin = User::factory()->create([
            'user_type' => 'super_admin',
            'email' => 'superadmin@netpack.com',
        ]);

        $hub = OverseasHub::create([
            'hub_code' => 'SYD',
            'hub_name' => 'Sydney Kingsford Smith Hub',
            'country' => 'Australia',
            'airport_name' => 'Sydney Airport Cargo Terminal',
            'mode_type' => 'DDU',
            'coverage_countries' => ['Australia', 'New Zealand'],
            'is_active' => true,
        ]);

        $agency = Agency::create([
            'name' => 'Pacific Courier & Customs Pty',
            'code' => 'PACIFIC-AUS',
            'country' => 'Australia',
            'city' => 'Mascot, Sydney',
            'address' => 'Link Road Mascot',
            'phone' => '+61-2-9000-1111',
            'primary_contact' => 'Liam Smith',
            'email' => 'sydney@pacificcargo.au',
            'password' => bcrypt('Agency@123'),
            'is_active' => true,
        ]);
        $agency->hubs()->sync([$hub->id]);

        // Post rate matrix tied to Hub and Agency
        $response = $this->actingAs($admin)->post(route('admin.international-rates.store'), [
            'rate_type' => 'country',
            'country' => 'Australia',
            'country_code' => 'AU',
            'hub_id' => $hub->id,
            'agency_id' => $agency->id,
            'service_type' => 'economy',
            'weight_tiers' => [
                '0.5' => 2800,
                '1.0' => 3400,
                '10.0' => 12500,
            ],
            'per_kg_tiers' => [
                ['min_weight' => 10.1, 'max_weight' => 30.0, 'rate_per_kg' => 1100],
            ],
            'customs_clearance_charge' => 600.00,
            'godown_charge' => 400.00,
            'fuel_surcharge_percent' => 5.0,
            'transit_days_min' => 5,
            'transit_days_max' => 7,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.international-rates.index'));

        $rate = InternationalRate::where('country', 'Australia')->first();
        $this->assertNotNull($rate);
        $this->assertEquals($hub->id, $rate->hub_id);
        $this->assertEquals($agency->id, $rate->agency_id);
        $this->assertEquals('Pacific Courier & Customs Pty', $rate->agency->name);
        $this->assertEquals('Sydney Kingsford Smith Hub', $rate->hub->hub_name);
    }

    public function test_only_authorized_roles_can_access_hubs_and_agencies()
    {
        $superAdmin = User::factory()->create(['user_type' => 'super_admin']);
        $internationalAdmin = User::factory()->create(['user_type' => 'international_admin']);
        $internationalStaff = User::factory()->create([
            'user_type' => 'staff',
            'service_scope' => 'international',
        ]);
        $domesticStaff = User::factory()->create([
            'user_type' => 'staff',
            'service_scope' => 'domestic',
        ]);
        $client = User::factory()->create(['user_type' => 'client']);

        // Super Admin: ALLOWED
        $this->actingAs($superAdmin)->get(route('international.hubs.index'))->assertStatus(200);
        $this->actingAs($superAdmin)->get(route('international.agencies.index'))->assertStatus(200);

        // International Admin: ALLOWED
        $this->actingAs($internationalAdmin)->get(route('international.hubs.index'))->assertStatus(200);
        $this->actingAs($internationalAdmin)->get(route('international.agencies.index'))->assertStatus(200);

        // International Staff: ALLOWED
        $this->actingAs($internationalStaff)->get(route('international.hubs.index'))->assertStatus(200);
        $this->actingAs($internationalStaff)->get(route('international.agencies.index'))->assertStatus(200);

        // Domestic Staff: FORBIDDEN (403)
        $this->actingAs($domesticStaff)->get(route('international.hubs.index'))->assertStatus(403);
        $this->actingAs($domesticStaff)->get(route('international.agencies.index'))->assertStatus(403);

        // Client: FORBIDDEN (403)
        $this->actingAs($client)->get(route('international.hubs.index'))->assertStatus(403);
        $this->actingAs($client)->get(route('international.agencies.index'))->assertStatus(403);
    }

    public function test_clients_never_see_agency_names_in_rates_and_inquiries()
    {
        $hub = OverseasHub::create([
            'hub_code' => 'DXB',
            'hub_name' => 'Dubai Gateway Hub',
            'country' => 'United Arab Emirates',
            'mode_type' => 'DDP',
            'coverage_countries' => ['United Arab Emirates'],
            'is_active' => true,
        ]);

        $agency = Agency::create([
            'name' => 'Secret Overseas Agency LLC',
            'code' => 'SECRET-AGENCY',
            'country' => 'United Arab Emirates',
            'city' => 'Dubai',
            'address' => 'Terminal 3',
            'phone' => '+971-4-999-8888',
            'email' => 'ops@secretagency.ae',
            'password' => bcrypt('Password@123'),
            'is_active' => true,
        ]);
        $agency->hubs()->sync([$hub->id]);

        InternationalRate::create([
            'hub_id' => $hub->id,
            'agency_id' => $agency->id,
            'rate_type' => 'country',
            'country' => 'United Arab Emirates',
            'service_type' => 'economy',
            'weight_tiers' => ['0.5' => 2000, '1.0' => 2500],
            'customs_clearance_charge' => 500,
            'godown_charge' => 300,
            'transit_days_min' => 4,
            'transit_days_max' => 6,
            'is_active' => true,
        ]);

        $client = User::factory()->create(['user_type' => 'client']);

        // Request live calculate endpoint
        $response = $this->actingAs($client)->postJson(route('rates.calculate'), [
            'country' => 'United Arab Emirates',
            'weight' => 1.0,
            'packaging' => 'none',
        ]);

        $response->assertStatus(200);

        // Agency name or code should NOT appear in JSON response
        $response->assertDontSee('Secret Overseas Agency LLC');
        $response->assertDontSee('SECRET-AGENCY');

        // Hub name is visible
        $response->assertSee('Dubai Gateway Hub');
    }

    public function test_legacy_overseas_partner_and_transit_points_routes_redirect_to_international_hubs()
    {
        $admin = User::factory()->create(['user_type' => 'super_admin']);

        // /admin/overseas-partners redirects to /international/hubs
        $response = $this->actingAs($admin)->get(route('admin.overseas-partners.index'));
        $response->assertRedirect(route('international.hubs.index'));

        // /international/partners redirects to /international/hubs
        $response = $this->actingAs($admin)->get(route('international.partners'));
        $response->assertRedirect(route('international.hubs.index'));

        // /international/transit-points redirects to /international/hubs
        $response = $this->actingAs($admin)->get(route('international.transit-points.index'));
        $response->assertRedirect(route('international.hubs.index'));

        // /international/transit-points/create redirects to /international/hubs/create
        $response = $this->actingAs($admin)->get(route('international.transit-points.create'));
        $response->assertRedirect(route('international.hubs.create'));
    }

    public function test_international_hub_stores_merged_agency_and_transit_point_functions()
    {
        $admin = User::factory()->create(['user_type' => 'super_admin']);

        $hubData = [
            'code' => 'JFK',
            'name' => 'New York JFK International Hub',
            'country' => 'United States',
            'city' => 'New York',
            'airport_name' => 'John F. Kennedy Cargo Terminal 4',
            'hub_type' => 'transit_point',
            'mode_type' => 'DDU',
            'is_mandatory' => 1,
            'is_active' => 1,
            'coverage_countries' => 'US, CA, MX',
            'service_routes' => 'Transatlantic Corridor, US Domestic Feeder',
            'handling_agency_name' => 'Atlantic Hub Operations LLC',
            'agency_contact_person' => 'Michael Scott',
            'agency_phone' => '+1-212-555-0199',
            'agency_email' => 'ops.jfk@atlantic-cargo.com',
            'agency_address' => 'Building 14, JFK International Airport',
        ];

        $response = $this->actingAs($admin)->post(route('international.hubs.store'), $hubData);
        $response->assertRedirect(route('international.hubs.index'));

        $hub = OverseasHub::where('hub_code', 'JFK')->first();
        $this->assertNotNull($hub);
        $this->assertEquals('transit_point', $hub->hub_type);
        $this->assertTrue($hub->is_mandatory);
        $this->assertEquals('DDU', $hub->mode_type);

        // Verify Handling Agency was automatically created and linked
        $this->assertCount(1, $hub->agencies);
        $agency = $hub->agencies->first();
        $this->assertEquals('Atlantic Hub Operations LLC', $agency->name);
        $this->assertEquals('Michael Scott', $agency->primary_contact);
        $this->assertEquals('+1-212-555-0199', $agency->phone);
        $this->assertEquals('ops.jfk@atlantic-cargo.com', $agency->email);
    }
}
