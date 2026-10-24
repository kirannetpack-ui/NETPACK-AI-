<?php

namespace App\Http\Controllers\Overseas;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\ShipmentScanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShipmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $overseas = Auth::user();

        $shipments = Shipment::where('overseas_partner_id', $overseas->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('overseas.shipments.index', compact('shipments'));
    }

    public function show($id)
    {
        $overseas = Auth::user();
        $shipment = Shipment::where('overseas_partner_id', $overseas->id)
            ->with(['customer', 'rider'])
            ->findOrFail($id);

        return view('overseas.shipments.show', compact('shipment'));
    }

    public function updateStatus(Request $request, $id)
    {
        $overseas = Auth::user();
        $shipment = Shipment::where('overseas_partner_id', $overseas->id)->findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,picked_up,in_transit,customs_clearance,out_for_delivery,delivered,returned,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($request->status !== $shipment->status) {
            $scanService = app(ShipmentScanService::class);
            $eventCode = $scanService->eventCodeForStatus($request->status);
            abort_unless($eventCode, 422, 'No operational scan event is configured for this status.');
            $scanService->record($shipment, $eventCode, null, $request->notes, $request->user(), 'overseas_partner');
        }

        return redirect()->route('overseas.shipments.show', $id)
            ->with('success', 'Shipment status updated successfully!');
    }

    public function documents()
    {
        $overseas = Auth::user();

        $shipments = Shipment::where('overseas_partner_id', $overseas->id)
            ->whereNotNull('invoice_file')
            ->orWhereNotNull('customs_declaration_file')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('overseas.documents.index', compact('shipments'));
    }
}
