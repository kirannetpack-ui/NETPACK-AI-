<?php
// app/Http/Controllers/Partner/ScanController.php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    /**
     * Show the QR scanning page
     * URL: /partner/staff/scan
     */
    public function scan()
    {
        return view('partner.scan');
    }
    
    /**
     * Fetch shipment by ID or order reference
     * URL: GET /partner/scan/fetch/{identifier}
     */
    public function fetchShipment($identifier)
    {
        $pickupRequest = PickupRequest::where('id', $identifier)
            ->orWhere('order_reference', $identifier)
            ->first();
        
        if (!$pickupRequest) {
            return response()->json([
                'success' => false, 
                'message' => 'Shipment not found'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'shipment' => [
                'id' => $pickupRequest->id,
                'order_reference' => $pickupRequest->order_reference,
                'pickup_address' => $pickupRequest->pickup_address,
                'delivery_address' => $pickupRequest->delivery_address,
                'status' => $pickupRequest->status
            ]
        ]);
    }
    
    /**
     * Process the scan action (arrival, departure, delivery)
     * URL: POST /partner/process-scan
     */
    public function processScan(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'action' => 'required|in:arrival,departure,delivery',
            'status_note' => 'nullable|string'
        ]);
        
        $pickupRequest = PickupRequest::where('id', $request->tracking_number)
            ->orWhere('order_reference', $request->tracking_number)
            ->first();
        
        if (!$pickupRequest) {
            return response()->json([
                'success' => false, 
                'message' => 'Request not found'
            ]);
        }
        
        $staff = Auth::guard('partner_staff')->user();
        
        // Check permissions
        if (!$this->checkPermission($staff, $request->action)) {
            return response()->json([
                'success' => false, 
                'message' => 'You do not have permission for this action'
            ]);
        }
        
        switch ($request->action) {
            case 'arrival':
                return $this->processArrival($pickupRequest, $staff, $request->status_note);
            case 'departure':
                return $this->processDeparture($pickupRequest, $staff, $request->status_note);
            case 'delivery':
                return $this->processDelivery($pickupRequest, $staff, $request->status_note);
            default:
                return response()->json([
                    'success' => false, 
                    'message' => 'Invalid action'
                ]);
        }
    }
    
    /**
     * Check if staff has permission for the action
     */
    private function checkPermission($staff, $action)
    {
        if ($staff->role === 'admin') return true;
        
        switch ($action) {
            case 'arrival': 
                return $staff->can_scan_arrival;
            case 'departure': 
                return $staff->can_scan_departure;
            case 'delivery': 
                return $staff->can_scan_delivery;
            default: 
                return false;
        }
    }
    
    /**
     * Process arrival scan
     */
    private function processArrival($pickupRequest, $staff, $note)
    {
        // Update status
        $pickupRequest->update([
            'status' => 'arrived_at_partner',
            'status_notes' => $note,
            'arrived_at' => now(),
            'partner_id' => $staff->partner_id,
            'partner_staff_id' => $staff->id
        ]);
        
        // Add to status history
        $history = $pickupRequest->status_history ?? [];
        $history[] = [
            'action' => 'arrival',
            'staff' => $staff->name,
            'staff_id' => $staff->id,
            'timestamp' => now()->toIso8601String(),
            'note' => $note,
            'location' => $pickupRequest->pickup_address
        ];
        $pickupRequest->status_history = $history;
        $pickupRequest->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Arrival recorded successfully',
            'data' => [
                'status' => 'Arrived at Partner Hub',
                'time' => now()->format('Y-m-d H:i:s'),
                'staff' => $staff->name
            ]
        ]);
    }
    
    /**
     * Process departure scan
     */
    private function processDeparture($pickupRequest, $staff, $note)
    {
        // Update status
        $pickupRequest->update([
            'status' => 'out_for_delivery',
            'status_notes' => $note,
            'departed_at' => now(),
            'partner_staff_id' => $staff->id
        ]);
        
        // Add to status history
        $history = $pickupRequest->status_history ?? [];
        $history[] = [
            'action' => 'departure',
            'staff' => $staff->name,
            'staff_id' => $staff->id,
            'timestamp' => now()->toIso8601String(),
            'note' => $note
        ];
        $pickupRequest->status_history = $history;
        $pickupRequest->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Departure recorded successfully',
            'data' => [
                'status' => 'Out for Delivery',
                'time' => now()->format('Y-m-d H:i:s'),
                'staff' => $staff->name
            ]
        ]);
    }
    
    /**
     * Process delivery scan
     */
    private function processDelivery($pickupRequest, $staff, $note)
    {
        // Update status
        $pickupRequest->update([
            'status' => 'delivered',
            'status_notes' => $note,
            'delivered_at' => now(),
            'partner_staff_id' => $staff->id
        ]);
        
        // Add to status history
        $history = $pickupRequest->status_history ?? [];
        $history[] = [
            'action' => 'delivery',
            'staff' => $staff->name,
            'staff_id' => $staff->id,
            'timestamp' => now()->toIso8601String(),
            'note' => $note
        ];
        $pickupRequest->status_history = $history;
        $pickupRequest->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Delivery completed successfully',
            'data' => [
                'status' => 'Delivered',
                'time' => now()->format('Y-m-d H:i:s'),
                'staff' => $staff->name
            ]
        ]);
    }
    
    /**
     * List all deliveries for partner
     * URL: GET /partner/deliveries
     */
    public function deliveries()
    {
        $partnerId = Auth::guard('partner')->id() ?? Auth::guard('partner_staff')->user()->partner_id;
        
        $deliveries = PickupRequest::where('partner_id', $partnerId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('partner.deliveries', compact('deliveries'));
    }
}