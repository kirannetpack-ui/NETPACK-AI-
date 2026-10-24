<?php

namespace App\Http\Controllers\Overseas;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\ShipmentScanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffScanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function scan()
    {
        abort_unless(in_array(Auth::user()?->user_type, ['staff', 'super_admin', 'international_admin'], true), 403);

        return view('hawb.scan');
    }

    public function processScan(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string|exists:shipments,tracking_number',
            'action' => 'required|in:arrival,departure,customs_clearance',
            'notes' => 'nullable|string',
        ]);

        $staff = Auth::user();
        abort_unless(in_array($staff?->user_type, ['staff', 'super_admin', 'international_admin'], true), 403);
        $shipment = Shipment::where('tracking_number', $request->tracking_number)->first();

        if (!$shipment) {
            return redirect()->back()
                ->with('error', 'Shipment not found.');
        }

        $eventCode = match ($request->action) {
            'arrival' => 'transit_facility_arrival',
            'departure' => 'transit_facility_departure',
            'customs_clearance' => 'customs_hold',
        };
        app(ShipmentScanService::class)->record($shipment, $eventCode, null, $request->notes, $staff, 'overseas_staff_scan');

        return redirect()->route('overseas.staff.scan')
            ->with('success', "Shipment {$shipment->tracking_number} processed successfully!");
    }
}
