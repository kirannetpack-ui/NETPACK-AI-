<?php

namespace Tests\Feature;

use App\Models\PaymentIntent;
use App\Models\Shipment;
use App\Models\User;
use App\Services\SplitPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentCaptureSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_gateway_capture_does_not_create_wallet_payouts(): void
    {
        $customer = User::factory()->create();
        $seller = User::factory()->create(['user_type' => User::TYPE_SELLER]);
        $shipment = Shipment::create([
            'hawb_number' => 'HAWB-PAY-001',
            'tracking_number' => 'NPE-PAY-001',
            'customer_id' => $customer->id,
            'seller_id' => $seller->id,
            'sender_name' => 'Sender',
            'sender_phone' => '9800000000',
            'sender_address' => 'Kathmandu',
            'receiver_name' => 'Receiver',
            'receiver_phone' => '9811111111',
            'receiver_address' => 'Pokhara',
            'receiver_city' => 'Pokhara',
            'receiver_country' => 'Nepal',
            'actual_weight' => 1,
            'chargeable_weight' => 1,
            'shipping_cost' => 100,
            'total_amount' => 100,
        ]);
        $intent = PaymentIntent::create([
            'intent_id' => 'PI-PAY-001',
            'shipment_id' => $shipment->id,
            'customer_id' => $customer->id,
            'seller_id' => $seller->id,
            'total_amount' => 100,
            'split_breakdown' => ['seller' => 70, 'netpack' => 15, 'rider' => 10, 'tax' => 5],
            'split_percentages' => ['seller' => 70, 'netpack' => 15, 'rider' => 10, 'tax' => 5],
            'status' => 'pending',
            'payment_gateway' => 'khalti',
        ]);

        $service = app(SplitPaymentService::class);
        $service->processInstantSplit($intent);
        $service->processInstantSplit($intent->fresh());

        $this->assertSame('paid', $intent->fresh()->status);
        $this->assertDatabaseCount('settlements', 0);
        $this->assertDatabaseCount('wallets', 0);
    }
}
