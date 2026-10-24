<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\DomesticPartner;
use App\Models\ShipmentAssignment;
use App\Services\EcommerceDispatchService;
use Illuminate\Http\Request;

class EcommerceBookingController extends Controller
{
    protected EcommerceDispatchService $dispatchService;

    public function __construct(EcommerceDispatchService $dispatchService)
    {
        $this->dispatchService = $dispatchService;
    }

    /**
     * List all E-Commerce deliveries booked by this seller
     */
    public function index(Request $request)
    {
        $seller = auth()->user();
        $deliveries = ShipmentAssignment::where('seller_id', $seller->id)->latest()->paginate(15);

        return view('seller.ecommerce.index', compact('deliveries', 'seller'));
    }

    /**
     * Show booking form for Direct Rider Delivery (Seller -> Rider -> Customer)
     */
    public function createDirect()
    {
        $seller = auth()->user();
        $rateRule = $this->dispatchService->getActiveRateRule('motorcycle');

        return view('seller.ecommerce.create_direct', compact('seller', 'rateRule'));
    }

    /**
     * Store Direct Rider Delivery
     */
    public function storeDirect(Request $request)
    {
        $request->validate([
            'delivery_name' => 'required|string|max:100',
            'delivery_phone' => 'required|string|max:30',
            'delivery_address' => 'required|string|max:255',
            'distance_km' => 'required|numeric|min:0.5',
            'parcel_weight' => 'required|numeric|min:0.1',
            'cod_amount' => 'nullable|numeric|min:0',
            'vehicle_type' => 'nullable|in:motorcycle,scooter,bicycle,car,van',
        ]);

        $seller = auth()->user();
        $assignment = $this->dispatchService->createDirectDelivery($seller, $request->all());

        return redirect()->route('seller.ecommerce.show', $assignment->id)
            ->with('success', "Direct Rider Delivery booked successfully! Master AWB: {$assignment->master_awb}. Your Pickup OTP is: {$assignment->pickup_otp}");
    }

    /**
     * Show booking form for Multi-Leg Domestic Courier Delivery
     */
    public function createMultiLeg()
    {
        $seller = auth()->user();
        $partners = DomesticPartner::where('is_active', true)->get();

        return view('seller.ecommerce.create_multileg', compact('seller', 'partners'));
    }

    /**
     * Store Multi-Leg Hybrid Delivery
     */
    public function storeMultiLeg(Request $request)
    {
        $request->validate([
            'delivery_name' => 'required|string|max:100',
            'delivery_phone' => 'required|string|max:30',
            'delivery_address' => 'required|string|max:255',
            'destination_city' => 'required|string|max:100',
            'parcel_weight' => 'required|numeric|min:0.1',
            'cod_amount' => 'nullable|numeric|min:0',
            'partner_id' => 'nullable|exists:domestic_partners,id',
        ]);

        $seller = auth()->user();
        $partner = $request->filled('partner_id') ? DomesticPartner::find($request->partner_id) : null;

        $result = $this->dispatchService->createMultiLegDelivery($seller, $request->all(), $partner);

        return redirect()->route('seller.ecommerce.index')
            ->with('success', "Multi-Leg Hybrid Delivery booked successfully! Master AWB: {$result['master_awb']}. First-Mile Pickup OTP: {$result['leg_1']->pickup_otp}");
    }

    /**
     * View consignment tracking and details
     */
    public function show($id)
    {
        $seller = auth()->user();
        $assignment = ShipmentAssignment::with('riderProfile')->where('seller_id', $seller->id)->findOrFail($id);
        $recipientLink = \Illuminate\Support\Facades\URL::temporarySignedRoute('recipient.handover', now()->addDays(2), ['assignment' => $assignment->id]);

        return view('seller.ecommerce.show', compact('assignment', 'seller', 'recipientLink'));
    }
}
