<?php

// app/Http/Controllers/Api/CustomerPaymentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentIntent;
use App\Models\Shipment;
use App\Services\EsewaPaymentService;
use App\Services\KhaltiPaymentService;
use App\Services\SplitPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerPaymentController extends Controller
{
    protected $khaltiService;

    protected $esewaService;

    protected $splitService;

    public function __construct(
        KhaltiPaymentService $khaltiService,
        EsewaPaymentService $esewaService,
        SplitPaymentService $splitService
    ) {
        $this->khaltiService = $khaltiService;
        $this->esewaService = $esewaService;
        $this->splitService = $splitService;
    }

    public function initiatePayment(Request $request, $shipmentId)
    {
        $shipment = Shipment::where('customer_id', auth()->id())
            ->where('id', $shipmentId)
            ->firstOrFail();

        if ($shipment->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Payment already completed',
            ], 400);
        }

        $request->validate([
            'gateway' => 'required|in:khalti,esewa',
        ]);

        $customer = auth()->user();
        $gateway = $request->gateway;
        if (! filled(config("services.{$gateway}.secret_key"))
            || ($gateway === 'esewa' && ! filled(config('services.esewa.merchant_code')))) {
            return response()->json(['success' => false, 'message' => 'Payment gateway is not configured.'], 503);
        }
        abort_if((float) $shipment->total_amount <= 0 || in_array($shipment->status, ['cancelled', 'returned'], true), 422, 'Shipment cannot be charged.');

        // Create payment intent
        $split = $this->splitService->calculateSplit($shipment->total_amount);

        $paymentIntent = PaymentIntent::create([
            'intent_id' => 'PI_'.Str::uuid(),
            'shipment_id' => $shipment->id,
            'customer_id' => $customer->id,
            'seller_id' => $shipment->seller_id,
            'total_amount' => $shipment->total_amount,
            'split_breakdown' => $split,
            'split_percentages' => [
                'seller' => 70,
                'netpack' => 15,
                'rider' => 10,
                'tax' => 5,
            ],
            'status' => 'pending',
            'payment_gateway' => $gateway,
        ]);

        if ($gateway === 'khalti') {
            $result = $this->khaltiService->initiatePayment(
                $shipment->total_amount,
                $paymentIntent->intent_id,
                'NETPACK Shipment #'.$shipment->hawb_number,
                $customer->name,
                $customer->email,
                $customer->phone
            );

            if ($result['success']) {
                $paymentIntent->update([
                    'gateway_transaction_id' => $result['pidx'],
                ]);

                return response()->json([
                    'success' => true,
                    'payment_url' => $result['payment_url'],
                    'pidx' => $result['pidx'],
                ]);
            }
        }

        if ($gateway === 'esewa') {
            $result = $this->esewaService->initiatePayment(
                $shipment->total_amount,
                $paymentIntent->intent_id,
                'NETPACK Shipment #'.$shipment->hawb_number
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'payment_url' => $result['payment_url'],
                    'form_data' => $result['form_data'],
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment initiation failed',
        ], 500);
    }

    public function verifyKhaltiPayment(Request $request)
    {
        $pidx = $request->pidx;

        if (! $pidx) {
            return redirect()->route('home')->with('error', 'Invalid payment request');
        }

        $paymentIntent = PaymentIntent::where('gateway_transaction_id', $pidx)->first();

        if (! $paymentIntent) {
            return redirect()->route('home')->with('error', 'Payment record not found');
        }

        abort_unless((int) $paymentIntent->customer_id === (int) auth()->id(), 403);

        $verification = $this->khaltiService->verifyPayment($pidx);

        if ($verification['success'] && abs((float) $verification['amount'] - (float) $paymentIntent->total_amount) < 0.01) {
            $shipment = $this->captureVerifiedPayment($paymentIntent);

            return redirect()->route('payment.success', $shipment->id)
                ->with('success', 'Payment successful! Your order is confirmed.');
        }

        return redirect()->route('payment.failure')
            ->with('error', 'Payment verification failed');
    }

    public function verifyEsewaPayment(Request $request)
    {
        $transactionUuid = $request->transaction_uuid;
        if (! $transactionUuid && is_string($request->input('data'))) {
            $decoded = base64_decode($request->input('data'), true);
            $callback = $decoded !== false ? json_decode($decoded, true) : null;
            $transactionUuid = is_array($callback) ? ($callback['transaction_uuid'] ?? null) : null;
        }
        abort_unless(is_string($transactionUuid) && strlen($transactionUuid) <= 100, 422, 'Invalid payment reference.');
        $paymentIntent = PaymentIntent::where('intent_id', $transactionUuid)->first();

        if (! $paymentIntent) {
            return redirect()->route('home')->with('error', 'Payment record not found');
        }

        abort_unless((int) $paymentIntent->customer_id === (int) auth()->id(), 403);

        // Never send a browser-provided amount to the gateway status check.
        $verification = $this->esewaService->verifyPayment($transactionUuid, $paymentIntent->total_amount);

        if ($verification['success']) {
            $shipment = $this->captureVerifiedPayment($paymentIntent);

            return redirect()->route('payment.success', $shipment->id)
                ->with('success', 'Payment successful! Your order is confirmed.');
        }

        return redirect()->route('payment.failure')
            ->with('error', 'Payment verification failed');
    }

    public function paymentSuccess($shipmentId)
    {
        $shipment = Shipment::where('customer_id', auth()->id())->findOrFail($shipmentId);
        abort_unless($shipment->payment_status === 'paid', 409, 'Payment has not been verified.');

        return view('payment.success', compact('shipment'));
    }

    public function paymentFailure()
    {
        return view('payment.failure');
    }

    public function getPaymentStatus($shipmentId)
    {
        $shipment = Shipment::where('customer_id', auth()->id())
            ->where('id', $shipmentId)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'payment_status' => $shipment->payment_status,
            'payment_method' => $shipment->payment_method,
            'status' => $shipment->status,
        ]);
    }

    private function captureVerifiedPayment(PaymentIntent $intent): Shipment
    {
        return DB::transaction(function () use ($intent) {
            $intent = PaymentIntent::lockForUpdate()->findOrFail($intent->id);
            $shipment = Shipment::lockForUpdate()->findOrFail($intent->shipment_id);
            if ($intent->status === 'paid') {
                return $shipment;
            }
            $this->splitService->processInstantSplit($intent);
            $updates = ['payment_status' => 'paid'];
            // A late provider callback cannot roll a fulfilled or cancelled shipment backwards.
            if ($shipment->status === 'pending') {
                $updates['status'] = 'confirmed';
            }
            $shipment->update($updates);
            $shipment->addTimeline($shipment->status, 'Payment confirmed by provider', 'System');

            return $shipment;
        }, 3);
    }
}
