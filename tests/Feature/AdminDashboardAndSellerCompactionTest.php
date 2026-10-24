<?php

namespace Tests\Feature;

use App\Models\Manifest;
use App\Models\Order;
use App\Models\SellerPaymentMethod;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardAndSellerCompactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_international_admin_dashboard_loads_without_sql_column_error(): void
    {
        $intlAdmin = User::factory()->create([
            'user_type' => User::TYPE_INTERNATIONAL_ADMIN,
            'role' => 'international_admin',
            'email' => 'intl_admin@netpack.test',
        ]);

        $response = $this->actingAs($intlAdmin)->get(route('international.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Flight Manifests');
        $response->assertSee('International Hubs');
        $response->assertDontSee('Column not found');
    }

    public function test_domestic_admin_dashboard_loads_without_sql_column_error(): void
    {
        $domesticAdmin = User::factory()->create([
            'user_type' => User::TYPE_DOMESTIC_ADMIN,
            'role' => 'domestic_admin',
            'email' => 'dom_admin@netpack.test',
        ]);

        $response = $this->actingAs($domesticAdmin)->get(route('domestic.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Sortation Manifests');
        $response->assertSee('Domestic Shipments');
        $response->assertDontSee('Column not found');
    }

    public function test_manifest_defensive_count_methods(): void
    {
        $this->assertIsInt(Manifest::countInternational());
        $this->assertIsInt(Manifest::countDomestic());
    }

    public function test_seller_dashboard_loads_compactly_without_product_catalog(): void
    {
        $seller = User::factory()->create([
            'user_type' => User::TYPE_SELLER,
            'role' => 'seller',
            'business_name' => 'Himalayan Organics',
            'email' => 'seller@netpack.test',
        ]);

        $response = $this->actingAs($seller)->get(route('seller.dashboard'));
        $response->assertStatus(200);

        // Verify 3-tier booking channels are visible
        $response->assertSee('Rider Delivery');
        $response->assertSee('Domestic Courier');
        $response->assertSee('International Air Cargo');
        $response->assertSee('Rate Calculator');

        // Verify product catalog bloat is gone
        $response->assertDontSee('Product Catalog');
        $response->assertDontSee('Top Products');
        $response->assertDontSee('Add Product');

        // Verify sidebar has only compact core links
        $response->assertSee('Book Shipment');
        $response->assertSee('Orders & History', false);
        $response->assertSee('Wallet & COD', false);
        $response->assertSee('Store Settings & Payout', false);
        $response->assertDontSee('Catalog Management');
        $response->assertDontSee('My Products');
    }

    public function test_seller_products_routes_redirect_to_dashboard(): void
    {
        $seller = User::factory()->create([
            'user_type' => User::TYPE_SELLER,
            'role' => 'seller',
        ]);

        $response = $this->actingAs($seller)->get(route('seller.products.index'));
        $response->assertRedirect(route('seller.dashboard'));

        $createResponse = $this->actingAs($seller)->get(route('seller.products.create'));
        $createResponse->assertRedirect(route('seller.dashboard'));
    }

    public function test_seller_can_save_cod_payout_bank_and_digital_wallet_details(): void
    {
        $seller = User::factory()->create([
            'user_type' => User::TYPE_SELLER,
            'role' => 'seller',
        ]);

        $response = $this->actingAs($seller)->put(route('seller.settings.update-bank'), [
            'bank_name' => 'Nabil Bank Ltd',
            'account_holder_name' => 'Himalayan Traders',
            'account_number' => '0101017500123',
            'account_type' => 'current',
            'ifsc_code' => 'NABILNPA',
            'esewa_id' => '9841234567',
            'khalti_id' => '9801234567',
        ]);

        $response->assertRedirect(route('seller.settings'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $seller->id,
            'bank_name' => 'Nabil Bank Ltd',
            'account_number' => '0101017500123',
            'account_type' => 'current',
        ]);

        $this->assertDatabaseHas('seller_payment_methods', [
            'user_id' => $seller->id,
            'method_type' => 'bank',
            'bank_name' => 'Nabil Bank Ltd',
            'account_number' => '0101017500123',
        ]);

        $this->assertDatabaseHas('seller_payment_methods', [
            'user_id' => $seller->id,
            'method_type' => 'esewa',
            'esewa_id' => '9841234567',
        ]);

        $this->assertDatabaseHas('seller_payment_methods', [
            'user_id' => $seller->id,
            'method_type' => 'khalti',
            'khalti_id' => '9801234567',
        ]);
    }

    public function test_rider_dashboard_and_compact_sidebar_render_cleanly(): void
    {
        $rider = User::factory()->create([
            'user_type' => User::TYPE_RIDER,
            'role' => 'rider',
            'name' => 'Rider Kancha',
            'email' => 'rider@netpack.test',
        ]);

        $response = $this->actingAs($rider)->get(route('rider.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Delivery workspace');
        $response->assertSee('Available Jobs');
        $response->assertSee('My Deliveries');
        $response->assertSee('COD & Earnings', false);
        $response->assertSee('Settings & Vehicle', false);
        $response->assertSee('Live Radar Map');

        // Verify redundant/scattered duplicate links are removed
        $response->assertDontSee('Active E-Commerce');
        $response->assertDontSee('Delivery History');
        $response->assertDontSee('Payout Bank Accounts');
    }
}
