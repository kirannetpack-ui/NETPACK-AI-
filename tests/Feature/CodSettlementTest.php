<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\CODSettlement;
use App\Models\RiderDeposit;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodSettlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_settlement_finalizes_existing_hold_without_deducting_rider_twice(): void
    {
        User::factory()->create(['user_type' => User::TYPE_SUPER_ADMIN]);
        $seller = User::factory()->create(['user_type' => User::TYPE_SELLER]);
        $rider = User::factory()->create([
            'user_type' => User::TYPE_RIDER,
            'rider_deposit_balance' => 400,
            'rider_delivery_fee' => 100,
            'rider_commission_rate' => 10,
            'rider_margin_rate' => 15,
        ]);
        $order = Order::create([
            'order_number' => 'ORD-COD-001',
            'seller_id' => $seller->id,
            'rider_id' => $rider->id,
            'customer_name' => 'COD Customer',
            'customer_phone' => '9800000000',
            'shipping_address' => 'Kathmandu',
            'total_amount' => 100,
            'cod_amount' => 100,
            'payment_method' => 'cod',
            'status' => 'out_for_delivery',
            'tracking_number' => 'NPE-2026-000002-4',
        ]);
        $hold = RiderDeposit::create([
            'rider_id' => $rider->id,
            'amount' => -100,
            'balance' => 400,
            'type' => 'settlement',
            'reference_type' => 'order',
            'reference_id' => $order->id,
            'description' => 'COD deposit hold',
            'status' => 'pending',
        ]);

        $this->actingAs($rider)
            ->post(route('rider.cod.settle', $order), [
                'cod_collected_amount' => 100,
                'signature' => 'COD Customer',
            ])
            ->assertRedirect(route('rider.orders.my'));

        $this->assertSame('400.00', $rider->fresh()->rider_deposit_balance);
        $this->assertSame('completed', $hold->fresh()->status);
        $this->assertSame('delivered', $order->fresh()->status);
        $this->assertSame('processing', $order->fresh()->settlement_status);
        $this->assertSame('collected', $order->fresh()->cod_status);
        $this->assertDatabaseCount('cod_settlements', 1);
        $this->assertSame('processing', CODSettlement::firstOrFail()->settlement_status);
        $this->assertDatabaseCount('wallets', 0);
    }

    public function test_cod_settlement_cannot_complete_before_out_for_delivery(): void
    {
        $seller = User::factory()->create(['user_type' => User::TYPE_SELLER]);
        $rider = User::factory()->create(['user_type' => User::TYPE_RIDER, 'rider_deposit_balance' => 500]);
        $order = Order::create([
            'order_number' => 'ORD-COD-002',
            'seller_id' => $seller->id,
            'rider_id' => $rider->id,
            'customer_name' => 'COD Customer',
            'customer_phone' => '9800000000',
            'shipping_address' => 'Kathmandu',
            'total_amount' => 100,
            'cod_amount' => 100,
            'payment_method' => 'cod',
            'status' => 'assigned',
            'tracking_number' => 'NPE-2026-000003-5',
        ]);

        $this->actingAs($rider)
            ->post(route('rider.cod.settle', $order), [
                'cod_collected_amount' => 100,
                'signature' => 'COD Customer',
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('cod_settlements', 0);
        $this->assertSame('assigned', $order->fresh()->status);
    }

    public function test_finance_completion_releases_cod_once(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_DOMESTIC_ADMIN]);
        $seller = User::factory()->create(['user_type' => User::TYPE_SELLER]);
        $rider = User::factory()->create(['user_type' => User::TYPE_RIDER]);
        $order = Order::create([
            'order_number' => 'ORD-COD-003',
            'seller_id' => $seller->id,
            'rider_id' => $rider->id,
            'customer_name' => 'COD Customer',
            'customer_phone' => '9800000000',
            'shipping_address' => 'Kathmandu',
            'total_amount' => 100,
            'cod_amount' => 100,
            'payment_method' => 'cod',
            'status' => 'delivered',
            'cod_status' => 'collected',
            'settlement_status' => 'processing',
            'tracking_number' => 'NPE-2026-000004-6',
        ]);
        $settlement = CODSettlement::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'rider_id' => $rider->id,
            'cod_amount' => 100,
            'delivery_charge' => 100,
            'admin_margin' => 15,
            'seller_amount' => 100,
            'rider_amount' => 110,
            'margin_amount' => 15,
            'settlement_status' => 'processing',
            'settlement_reference' => 'SET-TEST-003',
            'collected_at' => now(),
        ]);

        $this->actingAs($admin)
            ->put(route('admin.cod-settlements.update-status', $settlement), ['status' => 'completed'])
            ->assertRedirect(route('admin.cod-settlements.index'));

        $this->assertSame('completed', $settlement->fresh()->settlement_status);
        $this->assertSame('settled', $order->fresh()->cod_status);
        $this->assertSame('100.00', Wallet::where('user_id', $seller->id)->value('balance'));
        $this->assertSame('110.00', Wallet::where('user_id', $rider->id)->value('balance'));
        $this->assertSame('15.00', Wallet::where('user_id', $admin->id)->value('balance'));

        $this->actingAs($admin)
            ->put(route('admin.cod-settlements.update-status', $settlement), ['status' => 'completed'])
            ->assertRedirect();

        $this->assertSame('100.00', Wallet::where('user_id', $seller->id)->value('balance'));
        $this->assertSame('110.00', Wallet::where('user_id', $rider->id)->value('balance'));
        $this->assertSame('15.00', Wallet::where('user_id', $admin->id)->value('balance'));
    }
}
