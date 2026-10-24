<?php

// app/Services/EsewaPaymentService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EsewaPaymentService
{
    protected $merchantCode;

    protected $secretKey;

    protected $baseUrl;

    protected $isLive;

    public function __construct()
    {
        $this->isLive = (bool) config('services.esewa.live', false);
        $this->baseUrl = $this->isLive
            ? 'https://esewa.com.np/api/'
            : 'https://rc.esewa.com.np/api/';

        $this->merchantCode = config('services.esewa.merchant_code');
        $this->secretKey = config('services.esewa.secret_key');
    }

    public function initiatePayment($amount, $orderId, $productName)
    {
        $paymentData = [
            'amount' => $amount,
            'tax_amount' => 0,
            'total_amount' => $amount,
            'transaction_uuid' => $orderId,
            'product_code' => $this->merchantCode,
            'product_service_charge' => 0,
            'product_delivery_charge' => 0,
            'success_url' => route('payment.esewa.verify'),
            'failure_url' => route('payment.esewa.failure'),
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
        ];

        // Generate signature
        $signatureString = "total_amount={$paymentData['total_amount']},transaction_uuid={$paymentData['transaction_uuid']},product_code={$paymentData['product_code']}";
        $paymentData['signature'] = base64_encode(hash_hmac('sha256', $signatureString, $this->secretKey, true));

        return [
            'success' => true,
            'payment_url' => $this->isLive ? 'https://epay.esewa.com.np/api/epay/main/v2/form' : 'https://rc-epay.esewa.com.np/api/epay/main/v2/form',
            'form_data' => $paymentData,
        ];
    }

    public function verifyPayment($transactionUuid, $amount)
    {
        try {
            $response = Http::timeout(15)->get($this->baseUrl.'epay/transaction/status/', [
                'product_code' => $this->merchantCode,
                'transaction_uuid' => $transactionUuid,
                'total_amount' => $amount,
            ]);

            if ($response->successful() && $response->json('status') === 'COMPLETE'
                && $response->json('transaction_uuid') === $transactionUuid
                && $response->json('product_code') === $this->merchantCode
                && is_numeric($response->json('total_amount'))
                && (int) round($response->json('total_amount') * 100) === (int) round($amount * 100)) {
                return [
                    'success' => true,
                    'transaction_id' => $response->json('ref_id'),
                    'amount' => $amount,
                ];
            }

            return [
                'success' => false,
                'message' => 'Payment verification failed',
            ];

        } catch (\Exception $e) {
            Log::error('eSewa verification exception', [
                'error' => $e->getMessage(),
                'transaction_uuid' => $transactionUuid,
            ]);

            return [
                'success' => false,
                'message' => 'Verification service error',
            ];
        }
    }
}
