<?php

namespace Tests\Feature;

use App\Models\PaymentIntent;
use App\Models\Shipment;
use App\Models\User;
use App\Services\EsewaPaymentService;
use App\Services\KhaltiPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentGatewayContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_khalti_lookup_uses_provider_total_amount_in_paisa(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*/epayment/lookup/' => Http::response([
            'pidx' => 'test-pidx', 'total_amount' => 12550, 'status' => 'Completed',
            'transaction_id' => 'test-transaction', 'refunded' => false,
        ])]);
        $result = app(KhaltiPaymentService::class)->verifyPayment('test-pidx');
        $this->assertTrue($result['success']);
        $this->assertEquals(125.50, $result['amount']);
    }

    public function test_esewa_verifies_complete_status_amount_and_merchant_identity(): void
    {
        config(['services.esewa.merchant_code' => 'TEST-MERCHANT', 'services.esewa.live' => false]);
        $service = app(EsewaPaymentService::class);
        $valid = ['status' => 'COMPLETE', 'transaction_uuid' => 'PI-test',
            'product_code' => 'TEST-MERCHANT', 'total_amount' => 125.5, 'ref_id' => 'ref-1'];
        Http::fake(['rc.esewa.com.np/api/epay/transaction/status/*' => Http::sequence()
            ->push($valid)->push(array_merge($valid, ['total_amount' => 1]))
            ->push(array_merge($valid, ['status' => 'PENDING']))->push(array_merge($valid, ['product_code' => 'OTHER']))]);
        foreach ([true, false, false, false] as $expected) {
            $this->assertSame($expected, $service->verifyPayment('PI-test', 125.50)['success']);
        }
    }

    public function test_repeated_late_payment_callback_preserves_delivery_and_timeline(): void
    {
        $customer = User::factory()->create(['user_type' => 'client', 'role' => 'customer']);
        $shipment = Shipment::create([
            'customer_id' => $customer->id, 'tracking_number' => 'NPI-PAY-SAFE',
            'hawb_number' => 'HAWB-PAY-SAFE', 'sender_name' => 'Sender',
            'receiver_name' => 'Recipient', 'receiver_city' => 'Kathmandu', 'receiver_country' => 'Nepal',
            'shipment_type' => 'domestic', 'service_type' => 'standard',
            'status' => 'delivered', 'total_amount' => 125.50,
        ]);
        PaymentIntent::create([
            'intent_id' => 'PI-late', 'shipment_id' => $shipment->id, 'customer_id' => $customer->id,
            'seller_id' => null, 'total_amount' => 125.50, 'split_breakdown' => [],
            'split_percentages' => [], 'status' => 'pending', 'payment_gateway' => 'khalti',
            'gateway_transaction_id' => 'test-pidx',
        ]);
        Http::fake(['*/epayment/lookup/' => Http::response([
            'pidx' => 'test-pidx', 'total_amount' => 12550, 'status' => 'Completed',
            'transaction_id' => 'txn-safe', 'refunded' => false,
        ])]);
        $this->actingAs($customer)->get(route('payment.khalti.verify', ['pidx' => 'test-pidx']))->assertRedirect(route('payment.success', $shipment->id));
        $timeline = $shipment->fresh()->tracking_timeline;
        $this->get(route('payment.khalti.verify', ['pidx' => 'test-pidx']))->assertRedirect();
        $this->assertSame('delivered', $shipment->fresh()->status);
        $this->assertSame('paid', $shipment->fresh()->payment_status);
        $this->assertSame($timeline, $shipment->fresh()->tracking_timeline);
        $this->actingAs(User::factory()->create())->get(route('payment.success', $shipment->id))->assertNotFound();
    }

    public function test_demo_credentials_are_not_exposed_in_production_login(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->get(route('login'))->assertOk()->assertDontSee('Netpack!Admin#2026')->assertDontSee('Quick Role Demo Autofill');
    }
}
