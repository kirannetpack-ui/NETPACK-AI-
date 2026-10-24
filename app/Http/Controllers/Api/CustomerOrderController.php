<?php
// app/Http/Controllers/Api/CustomerOrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Product;
use App\Services\SplitPaymentService;
use App\Services\TrackingNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $request->validate([
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string',
            'receiver_address' => 'required|string',
            'receiver_city' => 'required|string',
            'receiver_country' => 'required|string',
            'boxes' => 'required|array',
            'total_weight' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'shipping_cost' => 'required|numeric',
            'payment_method' => 'required|in:online,cod'
        ]);
        
        DB::beginTransaction();
        
        try {
            $shipment = Shipment::create([
                'tracking_number' => app(TrackingNumberService::class)->ecommerce(),
                'customer_id' => auth()->id(),
                'sender_name' => auth()->user()->name,
                'sender_phone' => auth()->user()->phone,
                'sender_address' => 'Kathmandu, Nepal',
                'sender_city' => 'Kathmandu',
                'sender_country' => 'Nepal',
                'receiver_name' => $request->receiver_name,
                'receiver_phone' => $request->receiver_phone,
                'receiver_address' => $request->receiver_address,
                'receiver_city' => $request->receiver_city,
                'receiver_country' => $request->receiver_country,
                'receiver_postal_code' => $request->receiver_postal_code,
                'actual_weight' => $request->total_weight,
                'chargeable_weight' => $request->total_weight,
                'boxes' => json_encode($request->boxes),
                'shipping_cost' => $request->shipping_cost,
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method == 'online' ? 'pending' : 'pending',
                'status' => 'pending',
                'shipment_type' => 'grocery',
                'service_type' => 'standard'
            ]);
            
            // Add initial tracking event
            $shipment->addTrackingEvent('pending', 'NETPACK Hub, Kathmandu', 'Order created successfully');
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'shipment_id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'total_amount' => $shipment->total_amount,
                    'payment_method' => $shipment->payment_method,
                    'payment_status' => $shipment->payment_status
                ]
            ]);
            
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order. Please try again.'
            ], 500);
        }
    }
    
    public function getOrders(Request $request)
    {
        $orders = Shipment::where('customer_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);
        
        return response()->json([
            'success' => true,
            'data' => $orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'hawb_number' => $order->hawb_number,
                    'tracking_number' => $order->tracking_number,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'estimated_delivery' => $order->estimated_delivery ? $order->estimated_delivery->format('Y-m-d') : null,
                    'items_count' => $this->countItems($order->boxes),
                    'tracking_url' => route('tracking.show', $order->tracking_number)
                ];
            }),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total()
            ]
        ]);
    }
    
    public function getOrderDetails($id)
    {
        $order = Shipment::where('customer_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'hawb_number' => $order->hawb_number,
                'tracking_number' => $order->tracking_number,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'shipping_cost' => $order->shipping_cost,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'estimated_delivery' => $order->estimated_delivery,
                'delivered_at' => $order->delivered_at,
                'sender' => [
                    'name' => $order->sender_name,
                    'address' => $order->sender_address,
                    'phone' => $order->sender_phone
                ],
                'receiver' => [
                    'name' => $order->receiver_name,
                    'address' => $order->receiver_address,
                    'city' => $order->receiver_city,
                    'country' => $order->receiver_country,
                    'phone' => $order->receiver_phone,
                    'postal_code' => $order->receiver_postal_code
                ],
                'boxes' => json_decode($order->boxes, true),
                'tracking_history' => $order->tracking_history,
                'current_location' => $order->getCurrentLocationAttribute(),
                'progress_percentage' => $order->getProgressPercentageAttribute()
            ]
        ]);
    }
    
    public function cancelOrder($id)
    {
        $order = Shipment::where('customer_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();
        
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled at this stage'
            ], 400);
        }
        
        $order->update(['status' => 'cancelled']);
        $order->addTrackingEvent('cancelled', 'System', 'Order cancelled by customer');
        
        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully'
        ]);
    }
    
    private function countItems($boxes)
    {
        $boxes = json_decode($boxes, true);
        $count = 0;
        if ($boxes) {
            foreach ($boxes as $box) {
                $count += count($box);
            }
        }
        return $count;
    }
}
