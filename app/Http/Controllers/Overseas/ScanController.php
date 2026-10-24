<?php

namespace App\Http\Controllers\Overseas;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\ShipmentScanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function scan()
    {
        return view('hawb.scan');
    }

    public function processScan(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string|exists:shipments,tracking_number',
            'event_code' => 'nullable|required_without:status|string|max:80',
            'status' => 'nullable|required_without:event_code|string',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $overseas = Auth::user();
        $shipment = Shipment::where('overseas_partner_id', $overseas->id)
            ->where('tracking_number', $request->tracking_number)
            ->first();

        if (!$shipment) {
            return redirect()->back()
                ->with('error', 'Shipment not found or not assigned to you.');
        }

        $scanService = app(ShipmentScanService::class);
        $eventCode = $request->event_code ?: $scanService->eventCodeForStatus($request->status);
        abort_unless($eventCode, 422, 'No operational scan event is configured for this status.');
        $shipment = $scanService->record($shipment, $eventCode, $request->location, $request->notes, $request->user(), 'overseas_scan');

        return redirect()->route('overseas.scan')
            ->with('success', "Shipment {$shipment->tracking_number} updated to: " . ucfirst(str_replace('_', ' ', $shipment->status)));
    }

    public function fetchShipment($trackingNumber)
    {
        $overseas = Auth::user();
        $shipment = Shipment::where('overseas_partner_id', $overseas->id)
            ->where('tracking_number', $trackingNumber)
            ->first();

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found or not assigned to you.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $shipment,
        ]);
    }
}
