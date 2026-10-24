<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\PaymentIntent;
use App\Models\Shipment;
use App\Services\SplitPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected $splitService;
    
    public function __construct(SplitPaymentService $splitService)
    {
        $this->splitService = $splitService;
    }
    
    public function createPaymentIntent(Shipment $shipment)
    {
        abort_unless((int) $shipment->customer_id === (int) auth()->id(), 403);

        // Calculate split based on total amount
        $split = $this->splitService->calculateSplit($shipment->total_amount);
        
        $paymentIntent = PaymentIntent::create([
            'intent_id' => 'PI_' . Str::uuid(),
            'shipment_id' => $shipment->id,
            'customer_id' => auth()->id(),
            'seller_id' => $shipment->seller_id ?? null,
            'rider_id' => $shipment->rider_id ?? null,
            'total_amount' => $shipment->total_amount,
            'split_breakdown' => $split,
            'split_percentages' => [
                'seller' => 70,
                'netpack' => 15,
                'rider' => 10,
                'tax' => 5
            ],
            'status' => 'pending',
            'payment_gateway' => 'khalti'
        ]);
        
        return response()->json([
            'success' => true,
            'payment_intent' => $paymentIntent,
            'amount' => $paymentIntent->total_amount,
            'split' => $split
        ]);
    }
    
    public function paymentSuccess(Request $request)
    {
        $paymentIntent = PaymentIntent::where('intent_id', $request->intent_id)
            ->where('customer_id', auth()->id())
            ->first();
        
        if (!$paymentIntent) {
            return redirect()->route('shipments.index')->with('error', 'Payment intent not found');
        }
        
        if ($paymentIntent->status !== 'paid') {
            return redirect()->route('shipments.show', $paymentIntent->shipment)
                ->with('error', 'Payment must be confirmed by the payment provider before the shipment can be confirmed.');
        }

        // The gateway callback has already verified the transaction. This page
        // only reflects its result and cannot trigger a payout itself.
        $shipment = $paymentIntent->shipment;

        return redirect()->route('shipments.show', $shipment)
            ->with('success', 'Payment successful! Your shipment is confirmed.');
    }
}
