<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\RiderProfile;
use App\Models\ShipmentAssignment;
use App\Services\EcommerceDispatchService;
use Illuminate\Http\Request;

class RiderDeliveryDeskController extends Controller
{
    protected EcommerceDispatchService $dispatchService;

    public function __construct(EcommerceDispatchService $dispatchService)
    {
        $this->dispatchService = $dispatchService;
    }

    /**
     * Ensure current user has rider profile
     */
    protected function getRiderProfile(): RiderProfile
    {
        return auth()->user()->ensureRiderProfile();
    }

    /**
     * List available delivery jobs (Privacy Protected)
     */
    public function availableJobs(Request $request)
    {
        $rider = $this->getRiderProfile();

        // Get active offers for this rider or unassigned direct jobs
        $availableAssignments = ShipmentAssignment::where('status', 'assigned')
            ->where('provider_type', 'rider')
            ->whereNull('rider_profile_id')
            ->when(! $rider->isVerified() || ! $rider->isOnline() || $rider->current_active_packages >= $rider->max_active_packages, fn ($q) => $q->whereRaw('1 = 0'))
            ->whereNotExists(function ($q) {
                $q->selectRaw('1')->from('shipment_assignments as previous')
                    ->whereColumn('previous.master_awb', 'shipment_assignments.master_awb')
                    ->whereColumn('previous.sequence', '<', 'shipment_assignments.sequence')
                    ->where('previous.status', '!=', 'completed');
            })
            ->where(function ($q) use ($rider) {
                // If COD order, rider must have enough limit
                $q->where('cod_amount', '<=', ($rider->cod_limit - $rider->current_outstanding_cod))
                    ->orWhere('cod_amount', '<=', 0);
            })
            ->latest()
            ->paginate(10);

        return view('rider.jobs.available', compact('rider', 'availableAssignments'));
    }

    /**
     * Accept a delivery job
     */
    public function acceptJob(Request $request, $id)
    {
        $rider = $this->getRiderProfile();
        $result = $this->dispatchService->acceptJob($rider, (int) $id);

        if ($result['success']) {
            return redirect()->route('rider.delivery.show', $id)->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * List rider's active and historical delivery jobs
     */
    public function myDeliveries(Request $request)
    {
        $rider = $this->getRiderProfile();

        $activeDeliveries = ShipmentAssignment::where('rider_profile_id', $rider->id)
            ->whereIn('status', ['assigned', 'accepted', 'arrived_pickup', 'picked_up', 'in_transit'])
            ->latest()
            ->get();

        $completedDeliveries = ShipmentAssignment::where('rider_profile_id', $rider->id)
            ->whereIn('status', ['completed', 'failed'])
            ->latest()
            ->paginate(15);

        return view('rider.jobs.my_deliveries', compact('rider', 'activeDeliveries', 'completedDeliveries'));
    }

    /**
     * View delivery job details (reveals exact contacts after acceptance)
     */
    public function show($id)
    {
        $rider = $this->getRiderProfile();
        $assignment = ShipmentAssignment::where('rider_profile_id', $rider->id)->findOrFail($id);

        return view('rider.jobs.show', compact('rider', 'assignment'));
    }

    /**
     * Arrive at pickup location
     */
    public function arrivePickup(Request $request, $id)
    {
        $rider = $this->getRiderProfile();
        $assignment = ShipmentAssignment::where('rider_profile_id', $rider->id)->findOrFail($id);

        if (! $this->dispatchService->arriveAtPickup($assignment)) {
            return back()->with('error', 'This job cannot return to the pickup-arrival stage.');
        }

        return redirect()->back()->with('info', 'Arrived at pickup! Please request the 6-digit Pickup OTP from the seller.');
    }

    /**
     * Verify Seller's Pickup OTP & Handover Parcel
     */
    public function verifyPickup(Request $request, $id)
    {
        $request->validate(['pickup_otp' => 'required|string|size:6']);

        $rider = $this->getRiderProfile();
        $assignment = ShipmentAssignment::where('rider_profile_id', $rider->id)->findOrFail($id);

        $result = $this->dispatchService->verifyPickupOtp($assignment, $request->pickup_otp);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Complete delivery with Customer's Delivery OTP, COD collection, and POD
     */
    public function completeDelivery(Request $request, $id)
    {
        $request->validate([
            'delivery_otp' => 'required|string|size:6',
            'recipient_name' => 'nullable|string|max:100',
            'pod_photo' => 'nullable|image|max:5120',
        ]);

        $rider = $this->getRiderProfile();
        $assignment = ShipmentAssignment::where('rider_profile_id', $rider->id)->findOrFail($id);

        $podPhotoPath = null;
        if ($request->hasFile('pod_photo')) {
            $podPhotoPath = $request->file('pod_photo')->store('pod', 'public');
        }

        $result = $this->dispatchService->completeDelivery(
            $assignment,
            $request->delivery_otp,
            $request->recipient_name,
            $podPhotoPath
        );

        if ($result['success']) {
            return redirect()->route('rider.delivery.my')->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Mark delivery failed with reason
     */
    public function failDelivery(Request $request, $id)
    {
        $request->validate([
            'failure_reason' => 'required|string',
            'failure_notes' => 'nullable|string|max:500',
            'failure_photo' => 'nullable|image|max:5120',
        ]);

        $rider = $this->getRiderProfile();
        $assignment = ShipmentAssignment::where('rider_profile_id', $rider->id)->findOrFail($id);

        $photoPath = null;
        if ($request->hasFile('failure_photo')) {
            $photoPath = $request->file('failure_photo')->store('pod/failures', 'public');
        }

        $recorded = $this->dispatchService->recordFailure(
            $assignment,
            $request->failure_reason,
            $request->failure_notes,
            $photoPath
        );
        if (! $recorded) {
            return back()->with('error', 'This delivery is already closed.');
        }

        return redirect()->route('rider.delivery.my')->with('error', 'Delivery marked as failed. Parcel scheduled for RTO / Hub return.');
    }

    /**
     * Rider COD Ledger & Deposit Management
     */
    public function codLedger(Request $request)
    {
        $rider = $this->getRiderProfile();
        $ledgers = $rider->codLedgers()->latest()->paginate(15);

        return view('rider.cod.ledger', compact('rider', 'ledgers'));
    }

    /**
     * Submit a COD Deposit
     */
    public function depositCod(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'deposit_method' => 'required|in:cash_office,bank_transfer,digital_wallet,partner_point',
            'deposit_reference' => 'required|string|max:100',
            'deposit_receipt' => 'nullable|image|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $rider = $this->getRiderProfile();

        $receiptPath = null;
        if ($request->hasFile('deposit_receipt')) {
            $receiptPath = $request->file('deposit_receipt')->store('cod/receipts', 'public');
        }

        $rider->recordCodDeposit(
            (float) $request->amount,
            $request->deposit_method,
            $request->deposit_reference,
            $receiptPath,
            $request->notes ?? 'Rider submitted COD deposit',
            false // requires admin approval
        );

        return redirect()->back()->with('success', 'Deposit submitted successfully! Pending admin approval.');
    }
}
