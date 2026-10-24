<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\DomesticShipment;
use App\Models\Manifest;
use App\Models\DeliveryZone;
use App\Models\DomesticRate;
use App\Models\Order;
use App\Models\Product;
use App\Models\PickupRequest;
use App\Models\ProofOfDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DomesticAdminRoutesDiagnosticTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_seller_wallet_route(): void
    {
        $seller = User::factory()->create([
            'name' => 'Seller Wallet User',
            'email' => 'seller_wallet@test.com',
            'user_type' => 'seller',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        $response = $this->actingAs($seller)->get('/seller/wallet');
        dump("GET /seller/wallet -> Status: " . $response->getStatusCode());
        if ($response->getStatusCode() >= 400) {
            dump($response->getContent());
        }
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }

    public function test_domestic_admin_routes(): void
    {
        $domesticAdmin = User::factory()->create([
            'name' => 'Domestic Admin Lead',
            'email' => 'dom.lead@test.com',
            'user_type' => 'domestic_admin',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        $domesticPartner = \App\Models\DomesticPartner::create([
            'name' => 'KTM Express Partner',
            'code' => 'KTM-EXP',
            'company_name' => 'KTM Express Ltd',
            'email' => 'partner_corp@test.com',
            'password' => bcrypt('secret123'),
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'city' => 'Kathmandu',
            'district' => 'Kathmandu',
            'province' => 'Bagmati',
        ]);

        $zone1 = DeliveryZone::create([
            'partner_id' => $domesticPartner->id,
            'zone_name' => 'Kathmandu Valley',
            'zone_code' => 'KTM-01',
            'zone_type' => 'urban',
            'is_active' => true,
        ]);
        $zone2 = DeliveryZone::create([
            'partner_id' => $domesticPartner->id,
            'zone_name' => 'Pokhara',
            'zone_code' => 'PKR-01',
            'zone_type' => 'urban',
            'is_active' => true,
        ]);

        $partner = User::factory()->create([
            'name' => 'Partner Depot User',
            'email' => 'partner.depot@test.com',
            'user_type' => 'partner',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        $rate = DomesticRate::create([
            'partner_id' => $partner->id,
            'origin_zone_id' => $zone1->id,
            'destination_zone_id' => $zone2->id,
            'origin_city' => 'Kathmandu',
            'destination_city' => 'Pokhara',
            'service_type' => 'standard',
            'service_name' => 'STANDARD',
            'weight_from' => 0,
            'weight_to' => 5,
            'base_rate' => 150,
            'per_kg_rate' => 30,
            'rate_per_kg' => 30,
            'effective_from' => now()->subDay(),
            'is_active' => true,
        ]);

        $shipment = DomesticShipment::create([
            'tracking_number' => 'DOM-TEST-12345',
            'client_id' => $domesticAdmin->id,
            'partner_id' => $domesticPartner->id,
            'domestic_rate_id' => $rate->id,
            'sender_name' => 'Sender Name',
            'sender_phone' => '9800000001',
            'sender_address' => 'Kathmandu 1',
            'sender_city' => 'Kathmandu',
            'receiver_name' => 'Receiver Name',
            'receiver_phone' => '9800000002',
            'receiver_address' => 'Pokhara 2',
            'receiver_city' => 'Pokhara',
            'package_type' => 'document',
            'service_type' => 'standard',
            'service_name' => 'STANDARD',
            'status' => 'pending',
            'weight' => 2.5,
            'base_rate' => 150,
            'per_kg_rate' => 30,
            'total_amount' => 200,
        ]);

        $manifest = Manifest::create([
            'manifest_number' => 'MNF-DOM-001',
            'manifest_type' => 'domestic',
            'service_type' => 'domestic',
            'load_type' => 'parcel',
            'origin_hub' => 'Kathmandu Hub',
            'destination_hub' => 'Pokhara Depot',
            'status' => 'created',
            'created_by' => $domesticAdmin->id,
        ]);

        $seller = User::factory()->create([
            'name' => 'Seller Store',
            'email' => 'seller.store@test.com',
            'user_type' => 'seller',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        $pickup = PickupRequest::create([
            'seller_id' => $seller->id,
            'pickup_address' => 'Kathmandu Ward 3',
            'pickup_contact_name' => 'Seller Store',
            'pickup_contact_phone' => '9800000000',
            'delivery_address' => 'Kathmandu Ward 5',
            'delivery_contact_name' => 'Recipient Name',
            'delivery_contact_phone' => '9800000003',
            'package_type' => 'parcel',
            'status' => 'pending',
            'scheduled_pickup_time' => now()->addDay(),
            'preferred_pickup_date' => now()->addDay(),
        ]);

        $routesToTest = [
            'domestic.dashboard' => [],
            'domestic.manifests.index' => [],
            'domestic.manifests.create' => [],
            'domestic.manifests.scan' => [],
            'domestic.manifests.pods' => [],
            'domestic.shipments' => [],
            'domestic.pickups' => [],
            'domestic.partners' => [],
            'domestic.zones' => [],
            'domestic.rates' => [],
            'domestic.sellers' => [],
            'domestic.orders' => [],
            'domestic.products' => [],
            'domestic.reports' => [],
            'domestic.staff.index' => [],
            'domestic.partners.create' => [],
            'domestic.rates.create' => [],
            'domestic.zones.create' => [],
            'domestic.staff.create' => [],
            'domestic.pickup.create' => [],
            'domestic.shipments.show' => ['id' => $shipment->id],
            'domestic.manifests.show' => ['id' => $manifest->id],
            'domestic.partners.show' => ['id' => $partner->id],
            'domestic.zones.edit' => ['id' => $zone1->id],
            'domestic.rates.edit' => ['id' => $rate->id],
            'domestic.sellers.show' => ['id' => $seller->id],
        ];

        $failed = [];
        foreach ($routesToTest as $routeName => $params) {
            try {
                $url = route($routeName, $params);
                $response = $this->actingAs($domesticAdmin)->get($url);
                $status = $response->getStatusCode();
                if ($status >= 400) {
                    $failed[$routeName] = [
                        'status' => $status,
                        'url' => $url,
                        'error' => substr(strip_tags($response->getContent()), 0, 300)
                    ];
                }
            } catch (\Throwable $e) {
                $failed[$routeName] = [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ];
            }
        }

        if (!empty($failed)) {
            dump("FAILED ROUTES:", $failed);
        }

        $this->assertEmpty($failed, "Some domestic routes failed!");
    }
}
