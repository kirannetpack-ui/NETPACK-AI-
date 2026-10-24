<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomesticShipment;
use App\Models\DomesticRate;
use App\Models\User;
use Illuminate\Http\Request;

class DomesticShipmentController extends Controller
{
    public function index()
    {
        $shipments = DomesticShipment::with(['client', 'partner', 'domesticRate'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('admin.domestic.shipments.index', compact('shipments'));
    }

    public function show($id)
    {
        $shipment = DomesticShipment::with(['client', 'partner', 'domesticRate', 'trackingEvents'])
            ->findOrFail($id);
            
        return view('admin.domestic.shipments.show', compact('shipment'));
    }

    public function updateStatus(Request $request, $id)
    {
        $shipment = DomesticShipment::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,confirmed,picked_up,in_transit,out_for_delivery,delivered,failed_delivery,returned,cancelled'
        ]);

        $shipment->update([
            'status' => $request->status
        ]);

        // Add tracking event
        $shipment->addTrackingEvent(
            $request->status,
            $request->location ?? 'System Update',
            $request->description ?? 'Status updated by admin'
        );

        return redirect()->route('admin.domestic.shipments.show', $shipment->id)
            ->with('success', 'Shipment status updated successfully!');
    }
}