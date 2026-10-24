<?php

namespace Tests\Feature;

use App\Models\PickupRequest;
use App\Models\ReminderLog;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAndCommunicationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_sees_namaste_greeting_and_typographic_brand()
    {
        $user = User::factory()->create([
            'name' => 'Kiran Thapa',
            'user_type' => 'super_admin',
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Namaste', false);
        $response->assertSee('Kiran Thapa');
        $response->assertSee('COURIER');
        $response->assertSee('NETPACK');
    }

    public function test_superadmin_sidebar_contains_monitoring_suite_and_international_rate_feeding()
    {
        $admin = User::factory()->create([
            'name' => 'Super Admin',
            'user_type' => 'super_admin',
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Monitoring suite
        $response->assertSee(route('tracking.page'));
        $response->assertSee(route('admin.shipments.index'));
        $response->assertSee(route('admin.riders.dashboard'));

        // International Rate Feeding
        $response->assertSee(route('admin.international-rates.index'));
        $response->assertSee(route('admin.international-rates.settings'));

        // Communications / Delay hub
        $response->assertSee(route('admin.communications'));

        // Sub-portals are removed for Super Admin
        $response->assertDontSee(route('seller.dashboard'));
        $response->assertDontSee(route('rider.dashboard'));
        $response->assertDontSee(route('overseas.dashboard'));
    }

    public function test_client_registration_accepts_client_entity()
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Bikash Shrestha',
            'email' => 'bikash@example.test',
            'phone' => '9841123456',
            'dob' => '1995-05-15',
            'gender' => 'male',
            'nationality' => 'Nepali',
            'password' => 'Secret123!Safe',
            'password_confirmation' => 'Secret123!Safe',
            'user_type' => 'client',
            'address' => 'Baneshwor, Kathmandu',
            'city' => 'Kathmandu',
            'district' => 'Kathmandu',
            'province' => 'Bagmati',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('registration.pending'));

        $this->assertDatabaseHas('users', [
            'email' => 'bikash@example.test',
            'user_type' => 'client',
        ]);
    }

    public function test_admin_can_broadcast_delay_and_records_reminder_log()
    {
        $admin = User::factory()->create([
            'name' => 'Operations Admin',
            'user_type' => 'super_admin',
            'verification_status' => 'approved',
        ]);

        $client = User::factory()->create([
            'name' => 'Test Client',
            'user_type' => 'client',
            'verification_status' => 'approved',
        ]);

        $shipment = Shipment::create([
            'customer_id' => $client->id,
            'tracking_number' => 'NP-DOM-DELAY-TEST',
            'sender_name' => 'Sender Co',
            'sender_phone' => '9841000000',
            'sender_address' => 'Kathmandu',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'Receiver Co',
            'receiver_phone' => '9800000001',
            'receiver_address' => 'Pokhara',
            'receiver_city' => 'Pokhara',
            'receiver_country' => 'Nepal',
            'service_type' => 'standard',
            'shipment_type' => 'parcel',
            'package_type' => 'parcel',
            'actual_weight' => 1.5,
            'chargeable_weight' => 2.0,
            'shipping_cost' => 180,
            'total_amount' => 200,
            'total_cost' => 200,
            'status' => 'in_transit',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.communications.send-delay'), [
            'tracking_number' => 'NP-DOM-DELAY-TEST',
            'reason_code' => 'highway_blocked',
            'custom_note' => 'Dry landslide at Mugling highway; vehicle halted safely.',
            'channel' => 'all',
        ]);

        $response->assertRedirect(route('admin.communications'));

        $this->assertDatabaseHas('reminder_logs', [
            'reminder_type' => 'delay_alert',
            'channel' => 'all',
            'status' => 'sent',
        ]);

        $shipment->refresh();
        $this->assertNotEmpty($shipment->tracking_history);
    }
}
