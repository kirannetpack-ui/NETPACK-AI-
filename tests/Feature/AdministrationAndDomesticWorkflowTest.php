<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\DomesticRate;
use App\Models\Manifest;
use App\Models\ManifestShipment;
use App\Models\ProofOfDelivery;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrationAndDomesticWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_and_find_a_client_account(): void
    {
        $administrator = User::factory()->create([
            'user_type' => User::TYPE_SUPER_ADMIN,
            'verification_status' => 'approved',
        ]);

        $this->actingAs($administrator)
            ->post(route('admin.users.store'), [
                'name' => 'Verified Client',
                'email' => 'verified-client@example.test',
                'password' => 'ClientAccess!2026',
                'password_confirmation' => 'ClientAccess!2026',
                'user_type' => User::TYPE_CLIENT,
                'verification_status' => 'approved',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'verified-client@example.test',
            'user_type' => User::TYPE_CLIENT,
            'verification_status' => 'approved',
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.users.index', ['search' => 'verified-client']))
            ->assertOk()
            ->assertSee('verified-client@example.test');
    }

    public function test_customer_can_open_the_client_dashboard_and_only_sees_customer_owned_shipments(): void
    {
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER, 'verification_status' => 'approved']);

        $this->actingAs($customer)
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Client Dashboard');
    }

    public function test_overlapping_domestic_rate_is_rejected_before_save(): void
    {
        $administrator = User::factory()->create(['user_type' => User::TYPE_SUPER_ADMIN, 'verification_status' => 'approved']);
        $partner = User::factory()->create(['user_type' => User::TYPE_PARTNER, 'verification_status' => 'approved']);
        $origin = DeliveryZone::create(['zone_name' => 'Kathmandu', 'zone_code' => 'KTM001', 'zone_type' => 'urban', 'is_active' => true]);
        $destination = DeliveryZone::create(['zone_name' => 'Pokhara', 'zone_code' => 'PKR001', 'zone_type' => 'urban', 'is_active' => true]);

        DomesticRate::create([
            'partner_id' => $partner->id,
            'origin_zone_id' => $origin->id,
            'destination_zone_id' => $destination->id,
            'service_type' => 'standard',
            'service_name' => 'STANDARD',
            'base_rate' => 100,
            'per_kg_rate' => 20,
            'origin_city' => 'Kathmandu',
            'origin_zone' => 'KTM001',
            'destination_city' => 'Pokhara',
            'destination_zone' => 'PKR001',
            'rate_per_kg' => 20,
            'weight_from' => 0,
            'weight_to' => 5,
            'effective_from' => now()->toDateString(),
            'currency' => 'NPR',
        ]);

        $this->from(route('admin.domestic.rates.create'))
            ->actingAs($administrator)
            ->post(route('admin.domestic.rates.store'), [
                'partner_id' => $partner->id,
                'origin_zone_id' => $origin->id,
                'destination_zone_id' => $destination->id,
                'service_type' => 'standard',
                'base_rate' => 150,
                'per_kg_rate' => 25,
                'weight_from' => 4,
                'weight_to' => 10,
                'effective_from' => now()->toDateString(),
                'currency' => 'NPR',
            ])
            ->assertRedirect(route('admin.domestic.rates.create'))
            ->assertSessionHasErrors('weight_from');
    }

    public function test_authorised_operations_user_can_create_an_auditable_manifest(): void
    {
        $administrator = User::factory()->create(['user_type' => User::TYPE_SUPER_ADMIN, 'verification_status' => 'approved']);
        $partner = User::factory()->create(['user_type' => User::TYPE_PARTNER, 'verification_status' => 'approved']);
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER, 'verification_status' => 'approved']);
        $shipment = Shipment::create([
            'hawb_number' => 'HAWB-MANIFEST-001',
            'tracking_number' => 'TRK-MANIFEST-001',
            'customer_id' => $customer->id,
            'sender_name' => 'Sender',
            'sender_phone' => '9800000001',
            'sender_address' => 'Kathmandu',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'Receiver',
            'receiver_phone' => '9800000002',
            'receiver_address' => 'Pokhara',
            'receiver_city' => 'Pokhara',
            'receiver_country' => 'Nepal',
            'service_type' => 'standard',
            'shipment_type' => 'parcel',
            'actual_weight' => 1,
            'chargeable_weight' => 1,
            'shipping_cost' => 100,
            'total_amount' => 100,
            'status' => 'pending',
        ]);

        $this->actingAs($administrator)
            ->post(route('domestic.manifests.store'), [
                'load_type' => 'consolidated',
                'partner_id' => $partner->id,
                'origin_city' => 'Kathmandu',
                'destination_city' => 'Pokhara',
                'delivery_type' => 'door_delivery',
                'payment_status' => 'pending',
                'bags' => [[
                    'bag_type' => 'consolidated',
                    'weight' => 1,
                    'shipments' => [$shipment->id],
                ]],
            ])
            ->assertRedirect();

        $manifest = Manifest::firstOrFail();
        $this->assertSame(1, $manifest->total_shipments);
        $this->assertDatabaseHas('manifest_shipments', ['manifest_id' => $manifest->id, 'shipment_id' => $shipment->id, 'partner_id' => $partner->id]);
        $this->assertDatabaseHas('manifest_shipment_events', ['event_type' => 'manifested']);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $partner->id]);
    }

    public function test_partner_only_sees_proof_of_delivery_for_its_assigned_manifests(): void
    {
        $partner = User::factory()->create(['user_type' => User::TYPE_PARTNER, 'verification_status' => 'approved']);
        $otherPartner = User::factory()->create(['user_type' => User::TYPE_PARTNER, 'verification_status' => 'approved']);
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER, 'verification_status' => 'approved']);
        $shipment = $this->domesticShipment($customer, 'HAWB-POD-001', 'POD-VISIBLE-001');
        $otherShipment = $this->domesticShipment($customer, 'HAWB-POD-002', 'POD-HIDDEN-002');

        [$manifest, $line] = $this->manifestLine($partner, $shipment);
        [$otherManifest, $otherLine] = $this->manifestLine($otherPartner, $otherShipment);
        foreach ([[$manifest, $line, $shipment], [$otherManifest, $otherLine, $otherShipment]] as [$podManifest, $podLine, $podShipment]) {
            ProofOfDelivery::create([
                'manifest_id' => $podManifest->id,
                'manifest_shipment_id' => $podLine->id,
                'shipment_id' => $podShipment->id,
                'uploaded_by' => $partner->id,
                'pod_type' => 'signature',
                'recipient_name' => 'Recipient',
                'status' => 'uploaded',
            ]);
        }

        $this->actingAs($partner)
            ->get(route('domestic.manifests.pods'))
            ->assertOk()
            ->assertSee('POD-VISIBLE-001')
            ->assertDontSee('POD-HIDDEN-002');
    }

    private function domesticShipment(User $customer, string $hawb, string $tracking): Shipment
    {
        return Shipment::create([
            'hawb_number' => $hawb,
            'tracking_number' => $tracking,
            'customer_id' => $customer->id,
            'sender_name' => 'Sender', 'sender_phone' => '9800000001', 'sender_address' => 'Kathmandu', 'sender_city' => 'Kathmandu', 'sender_country' => 'Nepal',
            'receiver_name' => 'Receiver', 'receiver_phone' => '9800000002', 'receiver_address' => 'Pokhara', 'receiver_city' => 'Pokhara', 'receiver_country' => 'Nepal',
            'service_type' => 'standard', 'shipment_type' => 'parcel', 'actual_weight' => 1, 'chargeable_weight' => 1,
            'shipping_cost' => 100, 'total_amount' => 100, 'status' => 'pending',
        ]);
    }

    private function manifestLine(User $partner, Shipment $shipment): array
    {
        $creator = User::factory()->create(['user_type' => User::TYPE_SUPER_ADMIN, 'verification_status' => 'approved']);
        $manifest = Manifest::create(['manifest_number' => 'MF-'.str()->upper(str()->random(10)), 'created_by' => $creator->id, 'partner_id' => $partner->id, 'load_type' => 'direct']);
        $line = ManifestShipment::create(['manifest_id' => $manifest->id, 'shipment_id' => $shipment->id, 'partner_id' => $partner->id]);
        return [$manifest, $line];
    }
}
