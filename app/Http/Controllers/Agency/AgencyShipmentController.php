<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Manifest;
use App\Models\Shipment;
use App\Services\InternationalManifestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgencyShipmentController extends Controller
{
    public function __construct(private readonly InternationalManifestService $manifestService)
    {
    }

    public function scan()
    {
        return view('agency.scan');
    }
    
    /**
     * Display list of inbound flight manifests destined for this agency / hub
     */
    public function inboundManifests(Request $request)
    {
        [$agency, $staff] = $this->getCurrentAgencyAndUser();
        
        $query = Manifest::international()->with(['mawb', 'hub', 'shipments']);

        if ($agency) {
            $query->where(function ($q) use ($agency) {
                $q->where('agency_id', $agency->id)
                  ->orWhere('hub_id', $agency->hub_id);
            });
        }

        $manifests = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('agency.manifests.index', compact('manifests', 'agency'));
    }

    /**
     * Show Arrival Notice Verification Form for a Manifest
     */
    public function arrivalNoticeForm($manifestId)
    {
        [$agency, $staff] = $this->getCurrentAgencyAndUser();

        $manifest = Manifest::international()
            ->with(['mawb', 'hub', 'agency', 'shipments.shipment'])
            ->findOrFail($manifestId);

        $defaultLocation = $agency ? "{$agency->city}, {$agency->country} Hub Facility" : ($manifest->destination_city ?: 'Airport Cargo Hub');

        return view('agency.manifests.arrival-notice', compact('manifest', 'agency', 'staff', 'defaultLocation'));
    }

    /**
     * Submit Inbound Arrival Notice (Whole vs. Partial selection with Non-Arrival Remarks)
     */
    public function processArrivalNotice(Request $request, $manifestId)
    {
        return $this->submitArrivalNotice($request, $manifestId);
    }

    public function submitArrivalNotice(Request $request, $manifestId)
    {
        $manifest = Manifest::international()->findOrFail($manifestId);
        [$agency, $staff] = $this->getCurrentAgencyAndUser();

        $isWholeManifest = $request->input('arrival_mode') === 'whole' || $request->has('whole_manifest');
        $arrivedShipmentIds = $request->input('arrived_shipment_ids', $request->input('arrived_shipments', []));
        $nonArrivalRemarks = $request->input('non_arrival_remarks', []);
        
        $facilityLocation = $request->input('arrival_location', $request->input('facility_location', $agency ? "{$agency->city}, {$agency->country}" : 'Hub Facility'));
        
        $arrivalDate = $request->input('arrival_date', date('Y-m-d'));
        $arrivalTime = $request->input('arrival_time', date('H:i'));
        $timestamp = !empty($arrivalDate) && !empty($arrivalTime) 
            ? "{$arrivalDate} {$arrivalTime}:00" 
            : now()->toDateTimeString();

        $user = Auth::guard('agency_staff')->user() ?: (Auth::guard('agency')->user() ?: Auth::user());

        $result = $this->manifestService->processArrivalNotice(
            $manifest,
            $isWholeManifest,
            (array)$arrivedShipmentIds,
            (array)$nonArrivalRemarks,
            $facilityLocation,
            $timestamp,
            $user
        );

        return redirect()->route('agency.manifests.index')
            ->with('success', "Arrival Notice confirmed! Arrived: {$result['arrived_count']} packages, Non-arrival exceptions: {$result['missing_count']} at {$result['location']}.");
    }

    public function processScan(Request $request)
    {
        $request->validate([
            'hawb_number' => 'required|string',
            'action' => 'required|in:arrival,departure',
            'status_note' => 'nullable|string',
            'location' => 'nullable|string',
            'scan_time' => 'nullable|string',
        ]);
        
        $search = trim($request->hawb_number);
        $shipment = Shipment::where('hawb_number', $search)
            ->orWhere('tracking_number', $search)
            ->first();
        
        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Consignment not found for code: ' . $search
            ], 404);
        }
        
        [$agency, $staff] = $this->getCurrentAgencyAndUser();
        $location = $request->location ?: ($agency ? "{$agency->city}, {$agency->country} Hub" : 'Agency Hub Facility');
        $time = $request->scan_time ?: now()->format('Y-m-d H:i:s');
        
        if ($request->action === 'arrival') {
            return $this->processArrival($shipment, $agency, $staff, $request->status_note, $location, $time);
        } else {
            return $this->processDeparture($shipment, $agency, $staff, $request->status_note, $location, $time);
        }
    }
    
    private function processArrival($shipment, $agency, $staff, $note, $location, $time)
    {
        $staffName = $staff?->name ?? ($agency?->name ?? 'Agency Operations');

        $shipment->update([
            'current_agency_id' => $agency?->id ?: $shipment->current_agency_id,
            'arrived_at_agency' => \Carbon\Carbon::parse($time),
            'status' => 'in_transit',
            'current_location' => $location,
            'agency_milestone' => 'arrival_notice',
        ]);
        
        // Add to agency status history
        $history = $shipment->agency_status_history ?? [];
        $history[] = [
            'action' => 'arrival',
            'agency' => $agency?->name ?? 'Hub Agency',
            'staff' => $staffName,
            'location' => $location,
            'note' => $note ?: 'Physical box scanned at hub arrival desk',
            'timestamp' => \Carbon\Carbon::parse($time)->toIso8601String()
        ];
        $shipment->agency_status_history = $history;
        $shipment->save();
        
        // Add timeline entry
        $shipment->addTimeline(
            'arrival_notice',
            $note ?: "Consignment arrived at {$location}. Scanned and verified by {$staffName}.",
            $location
        );

        // Update manifest line item if exists
        if ($shipment->manifestShipment) {
            $shipment->manifestShipment->update([
                'arrival_status' => 'arrived',
                'arrived_at' => \Carbon\Carbon::parse($time),
                'arrived_location' => $location,
                'staff_name' => $staffName,
                'status' => 'received',
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Arrival recorded and telemetry stamped successfully!',
            'shipment' => [
                'hawb' => $shipment->hawb_number,
                'tracking' => $shipment->tracking_number,
                'receiver' => $shipment->receiver_name,
                'destination' => "{$shipment->receiver_city}, {$shipment->receiver_country}",
                'status' => 'Arrived at Hub (Arrival Notice)',
                'location' => $location,
                'time' => $time,
                'operator' => $staffName,
            ]
        ]);
    }
    
    private function processDeparture($shipment, $agency, $staff, $note, $location, $time)
    {
        $staffName = $staff?->name ?? ($agency?->name ?? 'Agency Operations');

        $shipment->update([
            'departed_from_agency' => \Carbon\Carbon::parse($time),
            'status' => 'out_for_delivery',
            'current_location' => $location,
            'agency_milestone' => 'last_mile_handover',
        ]);
        
        $history = $shipment->agency_status_history ?? [];
        $history[] = [
            'action' => 'departure',
            'agency' => $agency?->name ?? 'Hub Agency',
            'staff' => $staffName,
            'location' => $location,
            'note' => $note ?: 'Handed over for last mile courier delivery',
            'timestamp' => \Carbon\Carbon::parse($time)->toIso8601String()
        ];
        $shipment->agency_status_history = $history;
        $shipment->save();
        
        $shipment->addTimeline(
            'last_mile_handover',
            $note ?: "Handed over for last mile delivery at {$location} by {$staffName}.",
            $location
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Departure and handover recorded successfully!',
            'shipment' => [
                'hawb' => $shipment->hawb_number,
                'tracking' => $shipment->tracking_number,
                'status' => 'Out for delivery',
                'location' => $location,
                'time' => $time,
                'operator' => $staffName,
            ]
        ]);
    }
    
    public function shipments(Request $request)
    {
        [$agency, $staff] = $this->getCurrentAgencyAndUser();
        
        $query = Shipment::query();

        if ($agency) {
            $query->where('current_agency_id', $agency->id);
        }
        
        if ($request->filter == 'arrived') {
            $query->whereNotNull('arrived_at_agency')->whereNull('departed_from_agency');
        } elseif ($request->filter == 'departed') {
            $query->whereNotNull('departed_from_agency');
        }
        
        $shipments = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('agency.shipments', compact('shipments'));
    }
    
    public function show($id)
    {
        [$agency, $staff] = $this->getCurrentAgencyAndUser();
        
        $query = Shipment::where('id', $id);
        if ($agency) {
            $query->where('current_agency_id', $agency->id);
        }

        $shipment = $query->firstOrFail();
        
        return view('agency.shipment-detail', compact('shipment'));
    }

    private function getCurrentAgencyAndUser(): array
    {
        if (Auth::guard('agency_staff')->check()) {
            $staff = Auth::guard('agency_staff')->user();
            return [$staff->agency, $staff];
        }

        if (Auth::guard('agency')->check()) {
            $agency = Auth::guard('agency')->user();
            return [$agency, null];
        }

        // Fallback for admin or international admin accessing agency desk
        $admin = Auth::user();
        $agency = Agency::first();
        return [$agency, $admin];
    }
}