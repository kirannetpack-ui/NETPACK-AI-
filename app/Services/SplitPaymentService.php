<?php
// app/Services/SplitPaymentService.php

namespace App\Services;

use App\Models\PaymentIntent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SplitPaymentService
{
    // Default split percentages (70% seller, 15% NETPACK, 10% rider, 5% tax)
    protected $defaultPercentages = [
        'seller' => 70,
        'netpack' => 15,
        'rider' => 10,
        'tax' => 5
    ];
    
    public function calculateSplit($totalAmount, $customPercentages = null)
    {
        $percentages = $customPercentages ?? $this->defaultPercentages;
        $split = [];
        
        foreach ($percentages as $recipient => $percentage) {
            $amount = round($totalAmount * $percentage / 100, 2);
            $split[$recipient] = $amount;
        }
        
        // Adjust for rounding differences
        $totalSplit = array_sum($split);
        if ($totalSplit != $totalAmount) {
            $diff = $totalAmount - $totalSplit;
            $split['netpack'] += $diff;
        }
        
        return $split;
    }
    
    public function processInstantSplit(PaymentIntent $paymentIntent)
    {
        return DB::transaction(function () use ($paymentIntent) {
            $paymentIntent = PaymentIntent::whereKey($paymentIntent->id)->lockForUpdate()->firstOrFail();

            if ($paymentIntent->status === 'paid') {
                return [];
            }

            // A verified gateway payment is customer money held by NETPACK,
            // not proof that the shipment was fulfilled. The former code
            // released wallet credits immediately, could run multiple times,
            // and had no safe tax or unassigned-rider handling. Payouts are
            // intentionally deferred until the finance settlement workflow is
            // implemented and verified.
            $paymentIntent->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            Log::info('Gateway payment captured; payout deferred for settlement', [
                'payment_intent_id' => $paymentIntent->id,
                'split' => $paymentIntent->split_breakdown,
            ]);

            return [];
        });
    }
}
