<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\PickupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $rider = Auth::user();

        $deliveries = Delivery::where('rider_id', $rider->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('rider.deliveries.index', compact('deliveries'));
    }

    public function show($id)
    {
        $delivery = Delivery::where('rider_id', Auth::id())->findOrFail($id);
        return view('rider.deliveries.show', compact('delivery'));
    }

    public function updateStatus(Request $request, $id)
    {
        $delivery = Delivery::where('rider_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:picked_up,in_transit,arrived,delivered,failed',
            'notes' => 'nullable|string',
            'proof_image' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $allowedTransitions = [
            'assigned' => ['picked_up', 'failed'],
            'accepted' => ['picked_up', 'failed'],
            'picked_up' => ['in_transit', 'failed'],
            'in_transit' => ['arrived', 'failed'],
            'arrived' => ['delivered', 'failed'],
        ];

        if (!in_array($request->status, $allowedTransitions[$delivery->status] ?? [], true)) {
            return redirect()->back()->with('error', "Delivery cannot move from {$delivery->status} to {$request->status}.");
        }

        if ($request->status === 'delivered' && !$request->hasFile('proof_image')) {
            return redirect()->back()->withErrors(['proof_image' => 'Proof of delivery is required before completing delivery.'])->withInput();
        }

        $delivery->status = $request->status;
        
        // Update timestamps based on status
        switch ($request->status) {
            case 'picked_up':
                $delivery->picked_up_at = now();
                break;
            case 'in_transit':
                $delivery->in_transit_at = now();
                break;
            case 'arrived':
                $delivery->arrived_at = now();
                break;
            case 'delivered':
                $delivery->delivered_at = now();
                break;
            case 'failed':
                $delivery->failed_at = now();
                $delivery->failure_reason = $request->notes;
                break;
        }

        if ($request->hasFile('proof_image')) {
            $path = $request->file('proof_image')->store('delivery_proofs', 'private');
            $delivery->proof_of_delivery = $path;
        }

        $delivery->delivery_notes = $request->notes;
        $delivery->save();

        return redirect()->route('rider.deliveries.show', $id)
            ->with('success', 'Delivery status updated successfully!');
    }

    public function activeDeliveries()
    {
        $rider = Auth::user();

        $deliveries = Delivery::where('rider_id', $rider->id)
            ->whereIn('status', ['assigned', 'accepted', 'picked_up', 'in_transit'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('rider.deliveries.active', compact('deliveries'));
    }

    public function history()
    {
        $rider = Auth::user();

        $deliveries = Delivery::where('rider_id', $rider->id)
            ->whereIn('status', ['delivered', 'failed', 'returned'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('rider.deliveries.history', compact('deliveries'));
    }
}
