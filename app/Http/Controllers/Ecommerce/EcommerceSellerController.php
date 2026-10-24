<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceOrder;
use App\Models\PickupRequest;
use App\Services\EcommerceService;
use App\Services\DomesticService;
use App\Services\TrackingNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  


class EcommerceSellerController extends Controller
{
    protected $ecommerceService;
    protected $domesticService;
    
    public function __construct(EcommerceService $ecommerceService, DomesticService $domesticService)
    {
        $this->ecommerceService = $ecommerceService;
        $this->domesticService = $domesticService;
    }
    
    /**
     * Seller Dashboard - Shows all e-commerce orders
     * Located at: /seller/ecommerce/dashboard
     */
    public function dashboard()
    {
        $sellerId = Auth::id();
        
        $stats = [
            'total_orders' => EcommerceOrder::where('seller_id', $sellerId)->where('is_ecommerce', true)->count(),
            'pending_orders' => EcommerceOrder::where('seller_id', $sellerId)->where('is_ecommerce', true)->where('status', 'pending')->count(),
            'delivered_orders' => EcommerceOrder::where('seller_id', $sellerId)->where('is_ecommerce', true)->where('status', 'delivered')->count(),
            'total_earnings' => EcommerceOrder::where('seller_id', $sellerId)->where('is_ecommerce', true)->where('payment_status', 'paid')->sum('seller_earnings'),
            'pending_cod' => EcommerceOrder::where('seller_id', $sellerId)->where('is_ecommerce', true)->where('payment_status', 'cod')->where('status', 'delivered')->sum('cod_amount'),
        ];
        
        $recentOrders = EcommerceOrder::where('seller_id', $sellerId)
            ->where('is_ecommerce', true)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $platforms = EcommerceOrder::where('seller_id', $sellerId)
            ->where('is_ecommerce', true)
            ->select('platform', DB::raw('count(*) as total'))
            ->groupBy('platform')
            ->get();
        
        return view('ecommerce.seller.dashboard', compact('stats', 'recentOrders', 'platforms'));
    }
    
    /**
     * Create new e-commerce order
     * Located at: /seller/ecommerce/create
     */
    public function create()
    {
        $platforms = [
            'daraz' => 'Daraz Nepal',
            'hamrobazar' => 'Hamrobazar',
            'sastodeal' => 'Sasto Deal',
            'facebook' => 'Facebook Marketplace',
            'custom' => 'Own Website / Other'
        ];
        
        $serviceTiers = [
            'flash' => 'Flash (2-4 hours)',
            'same_day' => 'Same Day (By 8 PM)',
            'standard' => 'Standard (1-3 days)',
            'himalayan' => 'Himalayan (3-7 days)'
        ];
        
        return view('ecommerce.seller.create-order', compact('platforms', 'serviceTiers'));
    }
    
    /**
     * Store new e-commerce order
     * Located at: POST /seller/ecommerce/orders
     */
    public function store(Request $request)
    {
        $request->validate([
            'platform' => 'required|string',
            'order_reference' => 'required|string',
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'customer_email' => 'nullable|email',
            'cod_amount' => 'nullable|numeric|min:0',
            'pickup_address' => 'required|string',
            'pickup_ward_no' => 'required|string',
            'pickup_municipality' => 'required|string',
            'pickup_district' => 'required|string',
            'delivery_address' => 'required|string',
            'delivery_ward_no' => 'required|string',
            'delivery_municipality' => 'required|string',
            'delivery_district' => 'required|string',
            'service_tier' => 'required|in:flash,same_day,standard,himalayan',
            'estimated_weight_kg' => 'required|numeric|min:0.1',
            'items_description' => 'required|string',
            'scheduled_pickup_time' => 'required|date'
        ]);
        
        // Calculate earnings
        $earnings = $this->ecommerceService->calculateSellerEarnings(
            $request->cod_amount ?? 0,
            $request->platform,
            $request->service_tier
        );
        
        // Create pickup request
        $order = PickupRequest::create([
            'seller_id' => Auth::id(),
            'is_ecommerce' => true,
            'platform' => $request->platform,
            'order_reference' => $request->order_reference,
            'cod_amount' => $request->cod_amount ?? 0,
            'platform_fee' => $earnings['platform_fee_amount'],
            'seller_earnings' => $earnings['seller_earnings'],
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'product_items' => json_encode([
                'description' => $request->items_description,
                'weight' => $request->estimated_weight_kg
            ]),
            'payment_status' => $request->cod_amount > 0 ? 'cod' : 'pending',
            'pickup_address' => $request->pickup_address,
            'pickup_ward_no' => $request->pickup_ward_no,
            'pickup_municipality' => $request->pickup_municipality,
            'pickup_district' => $request->pickup_district,
            'pickup_province' => $request->pickup_province ?? 'Bagmati',
            'delivery_address' => $request->delivery_address,
            'delivery_ward_no' => $request->delivery_ward_no,
            'delivery_municipality' => $request->delivery_municipality,
            'delivery_district' => $request->delivery_district,
            'delivery_province' => $request->delivery_province ?? 'Bagmati',
            'service_tier' => $request->service_tier,
            'estimated_weight_kg' => $request->estimated_weight_kg,
            'items_description' => $request->items_description,
            'scheduled_pickup_time' => $request->scheduled_pickup_time,
            'status' => 'pending',
            'calculated_price' => $earnings['delivery_charge']
        ]);
        
        // Generate QR code for tracking
        $trackingNumber = app(TrackingNumberService::class)->ecommerce();
        $order->update([
            'tracking_number' => $trackingNumber,
            'qr_code' => $this->ecommerceService->generateOrderQR($order->id, $trackingNumber),
        ]);
        
        return redirect()->route('ecommerce.seller.orders')
            ->with('success', "E-commerce order created successfully. Tracking number: {$trackingNumber}");
    }
    
    /**
     * List all e-commerce orders
     * Located at: /seller/ecommerce/orders
     */
    public function orders(Request $request)
    {
        $query = EcommerceOrder::where('seller_id', Auth::id())
            ->where('is_ecommerce', true);
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->platform) {
            $query->where('platform', $request->platform);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('ecommerce.seller.orders', compact('orders'));
    }
    
    /**
     * View single order details
     * Located at: /seller/ecommerce/orders/{id}
     */
    public function show($id)
    {
        $order = EcommerceOrder::where('seller_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        return view('ecommerce.seller.order-detail', compact('order'));
    }
    
    /**
     * Generate delivery label
     * Located at: GET /seller/ecommerce/orders/{id}/label
     */
    public function generateLabel($id)
    {
        $order = EcommerceOrder::where('seller_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        $labelUrl = $this->ecommerceService->generateDeliveryLabel($order);
        $order->update(['delivery_label' => $labelUrl]);
        
        return response()->json(['success' => true, 'label_url' => $labelUrl]);
    }
    
    /**
     * Print delivery label
     * Located at: GET /seller/ecommerce/orders/{id}/print
     */
    public function printLabel($id)
    {
        $order = EcommerceOrder::where('seller_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        return view('ecommerce.components.print-label', compact('order'));
    }
    
    /**
     * Cancel order
     * Located at: POST /seller/ecommerce/orders/{id}/cancel
     */
    public function cancel($id)
    {
        $order = EcommerceOrder::where('seller_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        if (!in_array($order->status, ['pending', 'assigned'])) {
            return back()->with('error', 'Order cannot be cancelled at this stage');
        }
        
        $order->update(['status' => 'cancelled']);
        
        return back()->with('success', 'Order cancelled successfully');
    }

/**
 * Calculate earnings for AJAX request
 * Located at: POST /ecommerce/calculate
 */
public function calculateEarnings(Request $request)
{
    $earnings = $this->ecommerceService->calculateSellerEarnings(
        $request->cod_amount,
        $request->platform,
        $request->service_tier
    );
    
    return response()->json($earnings);
}

}
