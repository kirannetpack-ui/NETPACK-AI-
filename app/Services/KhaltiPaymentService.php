<?php

// app/Services/KhaltiPaymentService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KhaltiPaymentService
{
    protected $secretKey;

    protected $publicKey;

    protected $baseUrl;

    protected $isLive;

    public function __construct()
    {
        $this->isLive = (bool) config('services.khalti.live', false);
        $this->baseUrl = $this->isLive
            ? 'https://khalti.com/api/v2/'
            : 'https://dev.khalti.com/api/v2/';

        $this->secretKey = config('services.khalti.secret_key');
        $this->publicKey = config('services.khalti.public_key');
    }

    public function initiatePayment($amount, $orderId, $productName, $customerName, $customerEmail, $customerPhone)
    {
        try {
            $response = Http::timeout(15)->withHeaders([
                'Authorization' => 'Key '.$this->secretKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'epayment/initiate/', [
                'return_url' => route('payment.khalti.verify'),
                'website_url' => url('/'),
                'amount' => $amount * 100, // Khalti expects amount in paisa
                'purchase_order_id' => $orderId,
                'purchase_order_name' => $productName,
                'customer_info' => [
                    'name' => $customerName,
                    'email' => $customerEmail,
                    'phone' => $customerPhone,
                ],
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'payment_url' => $response->json('payment_url'),
                    'pidx' => $response->json('pidx'),
                ];
            }

            Log::error('Khalti payment initiation failed', [
                'response' => $response->json(),
                'order_id' => $orderId,
            ]);

            return [
                'success' => false,
                'message' => $response->json('detail') ?? 'Payment initiation failed',
            ];

        } catch (\Exception $e) {
            Log::error('Khalti payment exception', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
            ]);

            return [
                'success' => false,
                'message' => 'Payment service error',
            ];
        }
    }

    public function verifyPayment($pidx)
    {
        try {
            $response = Http::timeout(15)->withHeaders([
                'Authorization' => 'Key '.$this->secretKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'epayment/lookup/', [
                'pidx' => $pidx,
            ]);

            if ($response->successful() && $response->json('status') === 'Completed') {
                return [
                    'success' => true,
                    'transaction_id' => $response->json('transaction_id'),
                    'amount' => $response->json('total_amount') / 100,
                    'status' => $response->json('status'),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('detail') ?? 'Payment verification failed',
            ];

        } catch (\Exception $e) {
            Log::error('Khalti verification exception', [
                'error' => $e->getMessage(),
                'pidx' => $pidx,
            ]);

            return [
                'success' => false,
                'message' => 'Verification service error',
            ];
        }
    }
}
