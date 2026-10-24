<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StaffAndSellerDirectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_seller_logs_in_and_opens_seller_dashboard_with_all_components(): void
    {
        $seller = User::factory()->create([
            'name' => 'Verified Merchant',
            'email' => 'merchant@test.com',
            'user_type' => 'seller',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        $response = $this->actingAs($seller)->get('/dashboard');
        $response->assertRedirect(route('seller.dashboard'));

        $dashboardResponse = $this->actingAs($seller)->get(route('seller.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Merchant workspace');
        $dashboardResponse->assertSee('Book Shipment');
        $dashboardResponse->assertSee('Rider Delivery');
        $dashboardResponse->assertSee('Domestic Courier');
    }

    public function test_international_admin_creates_staff_and_staff_only_opens_international_section(): void
    {
        $internationalAdmin = User::factory()->create([
            'name' => 'Intl Admin',
            'email' => 'intl.admin@test.com',
            'user_type' => 'international_admin',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        // International Admin creates a staff member
        $response = $this->actingAs($internationalAdmin)->post(route('international.staff.store'), [
            'name' => 'Intl Staff Lead',
            'email' => 'intl.staff@test.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'phone' => '9851000001',
            'role' => 'Air Cargo Officer',
        ]);

        $response->assertRedirect(route('international.staff.index'));

        $staff = User::where('email', 'intl.staff@test.com')->first();
        $this->assertNotNull($staff);
        $this->assertEquals('staff', $staff->user_type);
        $this->assertEquals('international', $staff->service_scope);
        $this->assertEquals($internationalAdmin->id, $staff->created_by);
        $this->assertEquals('international.dashboard', $staff->dashboardRoute());

        // When this staff logs in or visits /dashboard, they open international dashboard
        $loginResponse = $this->actingAs($staff)->get('/dashboard');
        $loginResponse->assertRedirect(route('international.dashboard'));

        // Can view international dashboard
        $viewResponse = $this->actingAs($staff)->get(route('international.dashboard'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Global Freight Command');
        $viewResponse->assertSee('4 Gateway Hubs Active');

        // Cannot view domestic section (scoped isolation)
        $domesticResponse = $this->actingAs($staff)->get(route('domestic.dashboard'));
        $domesticResponse->assertStatus(403);
    }

    public function test_domestic_admin_creates_staff_and_staff_only_opens_domestic_section(): void
    {
        $domesticAdmin = User::factory()->create([
            'name' => 'Domestic Admin',
            'email' => 'dom.admin@test.com',
            'user_type' => 'domestic_admin',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        // Domestic Admin creates a staff member
        $response = $this->actingAs($domesticAdmin)->post(route('domestic.staff.store'), [
            'name' => 'Domestic Sortation Staff',
            'email' => 'dom.staff@test.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'phone' => '9851000002',
            'role' => 'Depot Scanner',
            'service_scope' => 'domestic',
        ]);

        $response->assertRedirect(route('domestic.staff.index'));

        $staff = User::where('email', 'dom.staff@test.com')->first();
        $this->assertNotNull($staff);
        $this->assertEquals('staff', $staff->user_type);
        $this->assertEquals('domestic', $staff->service_scope);
        $this->assertEquals($domesticAdmin->id, $staff->created_by);
        $this->assertEquals('domestic.dashboard', $staff->dashboardRoute());

        // When this staff logs in or visits /dashboard, they open domestic dashboard
        $loginResponse = $this->actingAs($staff)->get('/dashboard');
        $loginResponse->assertRedirect(route('domestic.dashboard'));

        // Can view domestic dashboard
        $viewResponse = $this->actingAs($staff)->get(route('domestic.dashboard'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Nepal Provincial Network');
        $viewResponse->assertSee('7 Provinces Operational');

        // Cannot view international section (scoped isolation)
        $intlResponse = $this->actingAs($staff)->get(route('international.dashboard'));
        $intlResponse->assertStatus(403);
    }

    public function test_super_admin_creates_staff_with_scope_all_and_staff_views_all_services(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'master.super@test.com',
            'user_type' => 'super_admin',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        // Super Admin creates a staff member with scope all
        $staff = User::create([
            'name' => 'Super Operations Staff',
            'email' => 'super.staff@test.com',
            'password' => bcrypt('SecurePass123!'),
            'user_type' => 'staff',
            'service_scope' => 'all',
            'created_by' => $superAdmin->id,
            'verification_status' => 'approved',
            'registration_completed' => true,
            'password_changed' => true,
        ]);

        $this->assertEquals('all', $staff->effectiveServiceScope());
        $this->assertEquals('admin.dashboard', $staff->dashboardRoute());

        // When this staff visits /dashboard, they open admin.dashboard
        $loginResponse = $this->actingAs($staff)->get('/dashboard');
        $loginResponse->assertRedirect(route('admin.dashboard'));

        // Can view super admin dashboard with 3 core logistics services command center
        $adminView = $this->actingAs($staff)->get(route('admin.dashboard'));
        $adminView->assertStatus(200);
        $adminView->assertSee('International Tariffs');
        $adminView->assertSee('Domestic delivery');
        $adminView->assertSee('Fleet & collections');

        // Can also view international and domestic underlying dashboards
        $this->actingAs($staff)->get(route('international.dashboard'))->assertStatus(200);
        $this->actingAs($staff)->get(route('domestic.dashboard'))->assertStatus(200);
    }
}
