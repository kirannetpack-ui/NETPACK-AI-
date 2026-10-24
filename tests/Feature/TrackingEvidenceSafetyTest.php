<?php

namespace Tests\Feature;

use App\Models\MAWB;
use App\Models\Shipment;
use App\Models\User;
use App\Services\AutomatedTrackingService;
use App\Services\CarrierTrackingSyncService;
use App\Services\Tracking\MawbFlightTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrackingEvidenceSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_unavailable_carrier_does_not_deliver_an_old_shipment(): void
    {
        config(['services.dhl.api_key' => null, 'services.17track.key' => null]);
        Http::fake(['*' => Http::response([], 503)]);
        $shipment = Shipment::create([
            'customer_id' => User::factory()->create()->id,
            'tracking_number' => 'NPI-EVIDENCE-01',
            'hawb_number' => 'HAWB-EVIDENCE-01',
            'shipment_type' => 'international', 'service_type' => 'express',
            'sender_name' => 'Sender', 'sender_city' => 'Kathmandu',
            'receiver_name' => 'Receiver', 'receiver_city' => 'London', 'receiver_country' => 'United Kingdom',
            'actual_weight' => 1, 'chargeable_weight' => 1,
            'status' => 'in_transit',
            'last_mile_carrier_name' => 'DHL',
            'last_mile_tracking_number' => '1234567890',
        ]);
        $shipment->forceFill(['created_at' => now()->subDays(10)])->save();
        $before = $shipment->fresh()->getAttributes();

        $result = app(CarrierTrackingSyncService::class)->syncShipment($shipment);

        $this->assertFalse($result['success']);
        $this->assertFalse($result['updated']);
        $this->assertSame($before, $shipment->fresh()->getAttributes());
        $this->assertStringContainsString('dhl.com', $result['direct_url']);
    }

    public function test_flight_time_and_landing_cannot_confirm_customs_or_release(): void
    {
        foreach ([null, ['status' => 'landed'], ['status' => 'active']] as $telemetry) {
            $service = new class(app(AutomatedTrackingService::class), $telemetry) extends MawbFlightTrackingService
            {
                public function __construct(AutomatedTrackingService $tracking, private ?array $telemetry)
                {
                    parent::__construct($tracking);
                }

                public function queryAviationStack(?string $airlineIata, ?string $flightNumber): ?array
                {
                    return $this->telemetry;
                }
            };

            foreach (['assigned', 'in_transit', 'cleared'] as $status) {
                if ($telemetry === ['status' => 'active'] && $status === 'assigned') {
                    continue; // Verified departure is covered by MawbFlightTrackingTest.
                }
                $mawb = MAWB::create([
                    'mawb_number' => '176-'.str_pad((string) (MAWB::count() + 1), 8, '0', STR_PAD_LEFT),
                    'airline_name' => 'Emirates SkyCargo', 'airline_code' => 'EK',
                    'origin_airport' => 'KTM', 'destination_airport' => 'DXB',
                    'flight_date' => now()->subDays(10), 'status' => $status,
                ]);

                $result = $service->syncMawb($mawb);

                $this->assertFalse($result['cascaded']);
                $this->assertSame($status, $mawb->fresh()->status);
            }
        }
    }
}
