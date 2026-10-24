<?php

namespace App\Services;

use App\Models\EcommerceOrder;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EcommerceService
{
    // Platform fee percentages
    protected $platformFees = [
        'daraz' => 0.05,      // 5%
        'hamrobazar' => 0.03,  // 3%
        'sastodeal' => 0.04,   // 4%
        'facebook' => 0.02,    // 2%
        'custom' => 0.05       // 5%
    ];
    
    // Delivery charges by tier
    protected $deliveryCharges = [
        'flash' => 150,
        'same_day' => 100,
        'standard' => 80,
        'himalayan' => 500
    ];
    
    public function calculateSellerEarnings($orderAmount, $platform, $deliveryTier)
    {
        $platformFee = $this->platformFees[$platform] ?? 0.05;
        $platformFeeAmount = $orderAmount * $platformFee;
        
        $deliveryCharge = $this->deliveryCharges[$deliveryTier] ?? 80;
        
        $sellerEarnings = $orderAmount - $platformFeeAmount;
        
        return [
            'order_amount' => $orderAmount,
            'platform_fee_percentage' => $platformFee * 100,
            'platform_fee_amount' => round($platformFeeAmount, 2),
            'delivery_charge' => $deliveryCharge,
            'seller_earnings' => round($sellerEarnings, 2),
            'netpack_earnings' => round($platformFeeAmount + $deliveryCharge, 2)
        ];
    }
    
    public function generateOrderQR($orderId, $trackingNumber)
    {
        $qrData = json_encode([
            'order_id' => $orderId,
            'tracking_number' => $trackingNumber,
            'type' => 'ecommerce_delivery',
            'url' => route('ecommerce.track', $trackingNumber)
        ]);
        
        $qrCode = QrCode::format('png')->size(200)->generate($qrData);
        $filename = "ecommerce_qr_{$orderId}.png";
        $path = storage_path("app/public/qrcodes/{$filename}");
        
        // Ensure directory exists
        if (!file_exists(storage_path('app/public/qrcodes'))) {
            mkdir(storage_path('app/public/qrcodes'), 0777, true);
        }
        
        file_put_contents($path, $qrCode);
        
        return asset("storage/qrcodes/{$filename}");
    }
    
    public function generateDeliveryLabel($order)
    {
        // Create HTML for delivery label
        $labelHtml = view('ecommerce.components.delivery-label', compact('order'))->render();
        
        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($labelHtml);
        $filename = "delivery_label_{$order->id}.pdf";
        $path = storage_path("app/public/labels/{$filename}");
        
        if (!file_exists(storage_path('app/public/labels'))) {
            mkdir(storage_path('app/public/labels'), 0777, true);
        }
        
        $pdf->save($path);
        
        return asset("storage/labels/{$filename}");
    }
    
    public function processCODPayment($orderId)
    {
        DB::beginTransaction();
        
        try {
            $order = EcommerceOrder::findOrFail($orderId);
            
            if ($order->payment_status !== 'cod') {
                throw new \Exception('Not a COD order');
            }
            
            // Update payment status
            $order->update(['payment_status' => 'paid']);
            
            // Credit seller's wallet
            $seller = User::find($order->seller_id);
            if ($seller && $seller->wallet) {
                $seller->wallet->credit(
                    $order->seller_earnings,
                    'ecommerce_cod',
                    $order->id,
                    "COD payment for order #{$order->order_reference}"
                );
            }
            
            DB::commit();
            
            return ['success' => true, 'message' => 'COD payment processed'];
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}