<?php

namespace Tests\Feature;

use App\Models\DeliveryReminder;
use App\Models\DomesticPartner;
use App\Models\LogisticsService;
use App\Models\PickupRequest;
use App\Models\ReminderLog;
use App\Models\User;
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicServicesAndRemindersTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $partnerUser;
    protected $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = User::factory()->create([
            'user_type' => 'super_admin',
            'role' => 'admin',
        ]);

        $this->partnerUser = User::factory()->create([
            'user_type' => User::TYPE_PARTNER,
            'role' => 'admin',
            'email' => 'partner@test.local',
            'verification_status' => 'approved',
        ]);

        $this->partner = DomesticPartner::create([
            'name' => 'Kathmandu Express Hub',
            'code' => 'KTM-EXP-01',
            'company_name' => 'Kathmandu Express Pvt Ltd',
            'email' => 'partner@test.local',
            'password' => bcrypt('partner123'),
            'phone' => '9800000000',
            'address' => 'Thamel, Kathmandu',
            'city' => 'Kathmandu',
            'district' => 'Kathmandu',
            'province' => 'Bagmati',
            'pan_number' => '123456789',
            'is_active' => true,
            'kyc_verified' => true,
        ]);
    }

    public function test_admin_can_view_services_index_with_category_counts()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.services.index'));

        $response->assertStatus(200);
        $response->assertSee('Services & Transit Time SLA Configurator', false);
        $response->assertSee('Flash Express');
        $response->assertSee('Domestic Standard');
        $response->assertSee('International Air Priority');
    }

    public function test_admin_can_create_new_custom_service_with_dynamic_transit_sla()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.services.store'), [
            'name' => 'Valley Super-Express',
            'code' => 'valley_super_express',
            'category' => 'domestic',
            'transit_time_hours' => 2.5,
            'transit_time_days' => 0.1,
            'reminder_intervals' => '50, 80',
            'base_rate' => 250.00,
            'per_kg_rate' => 90.00,
            'description' => 'Fastest courier delivery within ring road',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.services.index', ['category' => 'domestic']));

        $this->assertDatabaseHas('logistics_services', [
            'code' => 'valley_super_express',
            'name' => 'Valley Super-Express',
            'transit_time_hours' => 2.5,
            'category' => 'domestic',
        ]);

        $service = LogisticsService::where('code', 'valley_super_express')->first();
        $this->assertEquals([50, 80], $service->reminder_intervals);
    }

    public function test_admin_can_update_service_transit_time_and_rates()
    {
        $service = LogisticsService::where('code', 'flash')->first();
        $this->assertNotNull($service);

        $response = $this->actingAs($this->admin)->put(route('admin.services.update', $service->id), [
            'name' => 'Flash Express Updated (3h)',
            'code' => 'flash',
            'category' => 'domestic',
            'transit_time_hours' => 3.0,
            'transit_time_days' => 0.125,
            'reminder_intervals' => '40, 70, 90',
            'base_rate' => 220.00,
            'per_kg_rate' => 85.00,
            'description' => 'Updated 3 hour delivery SLA',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.services.index', ['category' => 'domestic']));

        $service->refresh();
        $this->assertEquals(3.0, $service->transit_time_hours);
        $this->assertEquals('Flash Express Updated (3h)', $service->name);
        $this->assertEquals([40, 70, 90], $service->reminder_intervals);
    }

    public function test_admin_can_toggle_and_delete_service()
    {
        $service = LogisticsService::create([
            'name' => 'Temporary Promo Courier',
            'code' => 'temp_promo',
            'category' => 'ecommerce',
            'transit_time_hours' => 5.0,
            'is_active' => true,
        ]);

        // Toggle status
        $response = $this->actingAs($this->admin)->post(route('admin.services.toggle', $service->id));
        $response->assertRedirect();
        $service->refresh();
        $this->assertFalse($service->is_active);

        // Delete service
        $response = $this->actingAs($this->admin)->delete(route('admin.services.destroy', $service->id));
        $response->assertRedirect(route('admin.services.index', ['category' => 'ecommerce']));
        $this->assertDatabaseMissing('logistics_services', ['id' => $service->id]);
    }

    public function test_pickup_request_dynamically_calculates_deadline_from_logistics_service()
    {
        $bookingTime = Carbon::now()->startOfHour();

        // Create a pickup with 'flash' tier
        $pickup = PickupRequest::create([
            'seller_id' => $this->admin->id,
            'partner_id' => $this->partner->id,
            'service_tier' => 'flash',
            'customer_name' => 'Sita Sharma',
            'customer_phone' => '9841000000',
            'pickup_address' => 'Lazimpat, Kathmandu',
            'delivery_address' => 'Baneshwor, Kathmandu',
            'status' => 'pending',
            'scheduled_pickup_time' => $bookingTime,
        ]);

        $flashService = LogisticsService::where('code', 'flash')->first();
        $expectedHours = (float) $flashService->transit_time_hours;

        $timeframe = $pickup->service_timeframe;
        $this->assertEquals($expectedHours, $timeframe['hours']);

        $expectedDeadline = $bookingTime->copy()->addMinutes(round($expectedHours * 60));
        $this->assertEquals($expectedDeadline->timestamp, $pickup->deadline->timestamp);
    }

    public function test_reminder_service_schedules_dynamic_reminders_according_to_transit_intervals()
    {
        $now = Carbon::now()->startOfHour();
        Carbon::setTestNow($now);

        // Create 2 hour express service
        $customService = LogisticsService::create([
            'name' => 'Hyper 2-Hour Express',
            'code' => 'hyper_2h',
            'category' => 'domestic',
            'transit_time_hours' => 2.0,
            'reminder_intervals' => [50, 75],
            'is_active' => true,
        ]);

        $pickup = PickupRequest::create([
            'seller_id' => $this->admin->id,
            'partner_id' => $this->partner->id,
            'service_tier' => 'hyper_2h',
            'customer_name' => 'Hari Thapa',
            'customer_phone' => '9841111111',
            'pickup_address' => 'Patan, Lalitpur',
            'delivery_address' => 'Thamel, Kathmandu',
            'status' => 'in_transit',
            'scheduled_pickup_time' => $now,
            'tracking_number' => 'NP-TEST-123456',
        ]);

        $reminderService = app(ReminderService::class);
        $reminderService->scheduleReminders($pickup);

        // For a 2h service with [50, 75] intervals:
        // Milestone 1 (50% = 1 hour from now): scheduled_at = now + 1h
        // Milestone 2 (75% = 1.5 hours from now): scheduled_at = now + 1.5h
        $partnerReminders = DeliveryReminder::where('pickup_request_id', $pickup->id)
            ->where('reminder_type', 'partner')
            ->orderBy('reminder_number')
            ->get();

        $this->assertCount(2, $partnerReminders);

        $firstReminder = $partnerReminders[0];
        $this->assertEquals(1, $firstReminder->reminder_number);
        $this->assertEquals($now->copy()->addMinutes(60)->timestamp, $firstReminder->scheduled_at->timestamp);
        $this->assertStringContainsString('Hyper 2-Hour Express', $firstReminder->message);
        $this->assertStringContainsString('50%', $firstReminder->message);

        $secondReminder = $partnerReminders[1];
        $this->assertEquals(2, $secondReminder->reminder_number);
        $this->assertEquals($now->copy()->addMinutes(90)->timestamp, $secondReminder->scheduled_at->timestamp);
        $this->assertStringContainsString('75%', $secondReminder->message);

        // Fast forward time to 65 minutes later (after reminder 1 is due)
        Carbon::setTestNow($now->copy()->addMinutes(65));

        $processed = $reminderService->processPendingReminders();
        $this->assertGreaterThanOrEqual(1, $processed);

        // First reminder should now be sent
        $firstReminder->refresh();
        $this->assertTrue($firstReminder->is_sent);

        // ReminderLog should exist for partner
        $this->assertDatabaseHas('reminder_logs', [
            'pickup_request_id' => $pickup->id,
            'reminder_type' => 'partner',
            'sent_to' => 'partner@test.local',
        ]);

        Carbon::setTestNow(null);
    }

    public function test_partner_can_access_attention_page_and_see_reminders()
    {
        $now = Carbon::now();

        $pickup = PickupRequest::create([
            'seller_id' => $this->admin->id,
            'partner_id' => $this->partner->id,
            'service_tier' => 'flash',
            'customer_name' => 'Bikash KC',
            'customer_phone' => '9841222222',
            'pickup_address' => 'Koteshwor',
            'delivery_address' => 'Baneshwor',
            'status' => 'in_transit',
            'is_delayed' => false,
            'created_at' => $now,
            'scheduled_pickup_time' => $now,
        ]);

        ReminderLog::create([
            'pickup_request_id' => $pickup->id,
            'reminder_type' => 'partner',
            'sent_to' => 'partner@test.local',
            'message' => '⏱️ REMINDER #1 (50% SLA elapsed): Flash Express deadline is approaching.',
            'channel' => 'email',
            'status' => 'sent',
            'sent_at' => $now,
            'metadata' => [
                'reminder_number' => 1,
                'service_tier' => 'flash',
            ],
        ]);

        $response = $this->actingAs($this->partnerUser)->get(route('partner.deliveries.attention'));

        $response->assertStatus(200);
        $response->assertSee('Attention Needed');
        $response->assertSee('Partner SLA & Transit Reminders', false);
        $response->assertSee('Flash Express');
    }
}
