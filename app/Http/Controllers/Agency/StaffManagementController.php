<?php
// app/Http/Controllers/Agency/StaffManagementController.php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\AgencyStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffManagementController extends Controller
{
    public function dashboard()
    {
        $staff = Auth::guard('agency_staff')->user();
        $agency = $staff->agency;
        
        return view('agency.staff.dashboard', compact('staff', 'agency'));
    }
    
    public function scan()
    {
        $staff = Auth::guard('agency_staff')->user();
        
        return view('agency.staff.scan', compact('staff'));
    }
    
    public function processScan(Request $request)
    {
        $staff = Auth::guard('agency_staff')->user();
        
        $request->validate([
            'hawb_number' => 'required|string',
            'action' => 'required|in:arrival,departure',
            'status_note' => 'nullable|string'
        ]);
        
        // Check permissions
        if ($request->action == 'arrival' && !$staff->can_scan_arrival) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to mark arrivals'
            ]);
        }
        
        if ($request->action == 'departure' && !$staff->can_scan_departure) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to mark departures'
            ]);
        }
        
        $shipment = \App\Models\Shipment::where('hawb_number', $request->hawb_number)
            ->orWhere('tracking_number', $request->hawb_number)
            ->first();
        
        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found'
            ]);
        }
        
        if ($request->action == 'arrival') {
            return $this->processArrival($shipment, $staff, $request->status_note);
        } else {
            return $this->processDeparture($shipment, $staff, $request->status_note);
        }
    }
    
    private function processArrival($shipment, $staff, $note)
    {
        if ($shipment->arrived_at_agency) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment already marked as arrived'
            ]);
        }
        
        $shipment->update([
            'current_agency_id' => $staff->agency_id,
            'arrived_at_agency' => now(),
            'status' => 'arrived_at_agency'
        ]);
        
        // Add to history with staff info
        $history = $shipment->agency_status_history ?? [];
        $history[] = [
            'action' => 'arrival',
            'agency' => $staff->agency->name,
            'staff' => $staff->name,
            'staff_role' => $staff->position,
            'note' => $note,
            'timestamp' => now()->toIso8601String()
        ];
        $shipment->agency_status_history = $history;
        $shipment->save();
        
        // Add tracking event
        $shipment->addTrackingEvent(
            'arrived_at_agency',
            $staff->agency->city . ', ' . $staff->agency->country,
            ($note ?: 'Package arrived at international hub') . ' - Updated by ' . $staff->name
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Arrival recorded successfully',
            'shipment' => [
                'hawb' => $shipment->hawb_number,
                'tracking' => $shipment->tracking_number,
                'status' => 'Arrived at agency',
                'location' => $staff->agency->city . ', ' . $staff->agency->country,
                'time' => now()->format('Y-m-d H:i:s'),
                'processed_by' => $staff->name
            ]
        ]);
    }
    
    private function processDeparture($shipment, $staff, $note)
    {
        if (!$shipment->arrived_at_agency) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment must arrive before departure'
            ]);
        }
        
        if ($shipment->departed_from_agency) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment already marked as departed'
            ]);
        }
        
        $shipment->update([
            'departed_from_agency' => now(),
            'status' => 'out_for_delivery'
        ]);
        
        // Add to history
        $history = $shipment->agency_status_history ?? [];
        $history[] = [
            'action' => 'departure',
            'agency' => $staff->agency->name,
            'staff' => $staff->name,
            'staff_role' => $staff->position,
            'note' => $note,
            'timestamp' => now()->toIso8601String()
        ];
        $shipment->agency_status_history = $history;
        $shipment->save();
        
        $shipment->addTrackingEvent(
            'handover_to_local',
            $staff->agency->city . ', ' . $staff->agency->country,
            ($note ?: 'Package handed over for last mile delivery') . ' - Processed by ' . $staff->name
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Departure recorded successfully',
            'shipment' => [
                'hawb' => $shipment->hawb_number,
                'tracking' => $shipment->tracking_number,
                'status' => 'Out for delivery',
                'location' => $staff->agency->city . ', ' . $staff->agency->country,
                'time' => now()->format('Y-m-d H:i:s'),
                'processed_by' => $staff->name
            ]
        ]);
    }
    
    public function shipments(Request $request)
    {
        $staff = Auth::guard('agency_staff')->user();
        $agencyId = $staff->agency_id;
        
        $query = \App\Models\Shipment::where('current_agency_id', $agencyId);
        
        if ($request->filter == 'arrived') {
            $query->whereNotNull('arrived_at_agency')->whereNull('departed_from_agency');
        } elseif ($request->filter == 'departed') {
            $query->whereNotNull('departed_from_agency');
        }
        
        $shipments = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('agency.staff.shipments', compact('shipments', 'staff'));
    }
}