<?php
// app/Http/Controllers/Domestic/PickupController.php

namespace App\Http\Controllers\Domestic;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use App\Models\User;
use App\Services\DomesticService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PickupController extends Controller
{
    protected $domesticService;
    
    public function __construct(DomesticService $domesticService)
    {
        $this->domesticService = $domesticService;
    }
    
    public function create()
    {
        return view('domestic.pickup.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'pickup_address' => 'required|string',
            'pickup_ward_no' => 'required|string',
            'pickup_municipality' => 'required|string',
            'pickup_district' => 'required|string',
            'pickup_province' => 'required|string',
            'scheduled_pickup_time' => 'required|date',
            'items_description' => 'required|string',
            'estimated_weight_kg' => 'required|numeric|min:0.1',
            'delivery_address' => 'required|string',
            'delivery_ward_no' => 'required|string',
            'delivery_municipality' => 'required|string',
            'delivery_district' => 'required|string',
            'delivery_province' => 'nullable|string',
            'service_tier' => 'required|in:flash,same_day,standard,himalayan',
        ]);
        
        $pickupRequest = PickupRequest::create([
            'seller_id' => Auth::id(),
            'pickup_address' => $request->pickup_address,
            'pickup_ward_no' => $request->pickup_ward_no,
            'pickup_municipality' => $request->pickup_municipality,
            'pickup_district' => $request->pickup_district,
            'pickup_province' => $request->pickup_province,
            'delivery_address' => $request->delivery_address,
            'delivery_ward_no' => $request->delivery_ward_no,
            'delivery_municipality' => $request->delivery_municipality,
            'delivery_district' => $request->delivery_district,
            'delivery_province' => $request->delivery_province ?? 'Bagmati',
            'scheduled_pickup_time' => $request->scheduled_pickup_time,
            'items_description' => $request->items_description,
            'estimated_weight_kg' => $request->estimated_weight_kg,
            'service_tier' => $request->service_tier,
            'status' => 'pending',
        ]);
        
        // Calculate estimated price
        $price = $this->domesticService->calculateDomesticRate(
            27.7172, 85.3240, // Default pickup (Kathmandu)
            27.7172, 85.3240, // Default delivery
            $request->estimated_weight_kg,
            $request->service_tier
        );
        
        return redirect()->route('domestic.pickup.show', $pickupRequest)
            ->with('success', 'Pickup request created! Estimated cost: NPR ' . number_format($price['total'], 2));
    }
    
    public function show(PickupRequest $pickupRequest)
    {
        $this->authorize('view', $pickupRequest);
        
        return view('domestic.pickup.show', compact('pickupRequest'));
    }
    
    public function myRequests()
    {
        $requests = PickupRequest::where('seller_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('domestic.pickup.my-requests', compact('requests'));
    }

public function cancel(PickupRequest $pickupRequest)
{
    if ($pickupRequest->seller_id !== Auth::id()) {
        abort(403);
    }
    
    if (!in_array($pickupRequest->status, ['pending', 'assigned'])) {
        return back()->with('error', 'Request cannot be cancelled at this stage');
    }
    
    $pickupRequest->update(['status' => 'cancelled']);
    
    return back()->with('success', 'Request cancelled successfully');
}

}