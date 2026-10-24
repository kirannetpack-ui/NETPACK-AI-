<?php
// app/Http/Controllers/Api/RiderDeliveryController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiderDeliveryController extends Controller
{
    public function pendingDeliveries(Request $request)
    {
        $shipments = Shipment::where('status', 'confirmed')
            ->whereNull('rider_id')
            ->where('payment_status', 'paid')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $this->formatShipmentsForRider($shipments)
        ]);
    }
    
    public function activeDeliveries(Request $request)
    {
        $shipments = Shipment::where('rider_id', $request->user()->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $this->formatShipmentsForRider($shipments)
        ]);
    }
    
    public function completedDeliveries(Request $request)
    {
        $shipments = Shipment::where('rider_id', $request->user()->id)
            ->where('status', 'delivered')
            ->latest()
            ->limit(20)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $this->formatShipmentsForRider($shipments)
        ]);
    }
    
    public function acceptDelivery(Request $request, Shipment $shipment)
    {
        if ($shipment->rider_id) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery already assigned'
            ], 400);
        }
        
        DB::beginTransaction();
        
        try {
            $shipment->update([
                'rider_id' => $request->user()->id,
                'status' => 'processing'
            ]);
            
            // Add tracking event
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'status' => 'processing',
                'location' => 'Assigned to rider',
                'description' => "Delivery assigned to " . $request->user()->name,
                'event_time' => now()
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Delivery accepted',
                'data' => $this->formatShipmentForRider($shipment)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept delivery'
            ], 500);
        }
    }
    
    public function startDelivery(Request $request, Shipment $shipment)
    {
        if ($shipment->rider_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        DB::beginTransaction();
        
        try {
            $shipment->update(['status' => 'out_for_delivery']);
            
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'status' => 'out_for_delivery',
                'location' => $request->user()->current_location ?? 'On the way',
                'description' => "Rider is on the way to deliver",
                'latitude' => $request->user()->current_latitude,
                'longitude' => $request->user()->current_longitude,
                'event_time' => now()
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Delivery started'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to start delivery'
            ], 500);
        }
    }
    
    public function completeDelivery(Request $request, Shipment $shipment)
    {
        if ($shipment->rider_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        DB::beginTransaction();
        
        try {
            $shipment->update([
                'status' => 'delivered',
                'delivered_at' => now()
            ]);
            
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'status' => 'delivered',
                'location' => $shipment->receiver_address,
                'description' => "Package delivered successfully",
                'event_time' => now()
            ]);
            
            // Calculate rider earnings (10% of shipping cost)
            $riderEarnings = $shipment->shipping_cost * 0.10;
            
            // Credit rider's wallet
            $wallet = $request->user()->wallet;
            $wallet->credit($riderEarnings, 'delivery', $shipment->id, 
                "Delivery completed for shipment #{$shipment->tracking_number}");
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Delivery completed',
                'earnings' => $riderEarnings
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete delivery'
            ], 500);
        }
    }
    
    public function uploadProof(Request $request, Shipment $shipment)
    {
        $request->validate([
            'proof_image' => 'required|image|max:2048'
        ]);
        
        if ($shipment->rider_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $image = $request->file('proof_image');
        $path = $image->store('delivery-proof', 'public');
        
        $shipment->delivery_proof_image = $path;
        $shipment->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Proof uploaded',
            'image_url' => asset('storage/' . $path)
        ]);
    }
    
    public function trackDelivery(Request $request, Shipment $shipment)
    {
        if ($shipment->rider_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'status' => $shipment->status,
                'current_location' => $shipment->getCurrentLocationAttribute(),
                'tracking_history' => $shipment->trackingEvents,
                'estimated_arrival' => $shipment->estimated_delivery
            ]
        ]);
    }
    
    private function formatShipmentsForRider($shipments)
    {
        return $shipments->map(function($shipment) {
            return $this->formatShipmentForRider($shipment);
        });
    }
    
    private function formatShipmentForRider($shipment)
    {
        return [
            'id' => $shipment->id,
            'hawb_number' => $shipment->hawb_number,
            'tracking_number' => $shipment->tracking_number,
            'sender_name' => $shipment->sender_name,
            'sender_phone' => $shipment->sender_phone,
            'sender_address' => $shipment->sender_address,
            'receiver_name' => $shipment->receiver_name,
            'receiver_phone' => $shipment->receiver_phone,
            'receiver_address' => $shipment->receiver_address,
            'status' => $shipment->status,
            'total_amount' => $shipment->total_amount,
            'shipping_cost' => $shipment->shipping_cost,
            'boxes' => json_decode($shipment->boxes, true)
        ];
    }
}