<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnerCharge;
use App\Models\Shipment;
use App\Models\User;
use App\Services\RateCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PartnerChargeController extends Controller
{
    protected $rateService;

    public function __construct(RateCalculationService $rateService)
    {
        $this->middleware('auth');
        $this->rateService = $rateService;
    }

    /**
     * Display list of partner charges
     */
    public function index(Request $request)
    {
        $query = PartnerCharge::with(['partner', 'shipment', 'submittedBy']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('submitted_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('submitted_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('shipment_reference', 'LIKE', "%{$search}%")
                  ->orWhereHas('shipment', function($sq) use ($search) {
                      $sq->where('tracking_number', 'LIKE', "%{$search}%");
                  });
            });
        }

        $charges = $query->orderBy('submitted_at', 'desc')->paginate(20);
        
        // Statistics
        $stats = [
            'total' => PartnerCharge::count(),
            'pending' => PartnerCharge::pending()->count(),
            'under_review' => PartnerCharge::underReview()->count(),
            'verified' => PartnerCharge::verified()->count(),
            'disputed' => PartnerCharge::disputed()->count(),
            'approved' => PartnerCharge::approved()->count(),
            'total_amount' => PartnerCharge::sum('total_charge'),
            'total_difference' => PartnerCharge::sum('charge_difference'),
        ];

        $partners = User::where('user_type', 'partner')->get();

        return view('admin.partner-charges.index', compact('charges', 'stats', 'partners'));
    }

    /**
     * Show partner charge details
     */
    public function show($id)
    {
        $charge = PartnerCharge::with(['partner', 'shipment', 'submittedBy', 'verifiedBy', 'approvedBy', 'disputedBy', 'history'])
            ->findOrFail($id);

        // Get system calculation for comparison
        $systemCalculation = null;
        if ($charge->shipment) {
            try {
                $params = [
                    'overseas_partner_id' => $charge->partner_id,
                    'country_to' => $charge->shipment->receiver_country ?? 'USA',
                    'weight' => $charge->weight_kg ?? $charge->shipment->chargeable_weight ?? 1,
                    'service_type' => $charge->service_type ?? 'standard',
                ];
                $systemCalculation = $this->rateService->calculateRate($params);
            } catch (\Exception $e) {
                // If calculation fails, just show null
            }
        }

        return view('admin.partner-charges.show', compact('charge', 'systemCalculation'));
    }

    /**
     * Store partner charge (submitted by partner)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipment_id' => 'required|exists:shipments,id',
            'base_charge' => 'required|numeric|min:0',
            'weight_charge' => 'nullable|numeric|min:0',
            'distance_charge' => 'nullable|numeric|min:0',
            'additional_charges' => 'nullable|numeric|min:0',
            'fuel_surcharge' => 'nullable|numeric|min:0',
            'handling_fee' => 'nullable|numeric|min:0',
            'insurance_charge' => 'nullable|numeric|min:0',
            'customs_charge' => 'nullable|numeric|min:0',
            'total_charge' => 'required|numeric|min:0',
            'weight_kg' => 'nullable|numeric|min:0',
            'distance_km' => 'nullable|integer|min:0',
            'service_type' => 'nullable|string',
            'service_tier' => 'nullable|string',
            'notes' => 'nullable|string',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'supporting_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $charge = PartnerCharge::create([
            'shipment_id' => $request->shipment_id,
            'partner_id' => Auth::id(),
            'shipment_reference' => $request->shipment_reference ?? null,
            'base_charge' => $request->base_charge,
            'weight_charge' => $request->weight_charge ?? 0,
            'distance_charge' => $request->distance_charge ?? 0,
            'additional_charges' => $request->additional_charges ?? 0,
            'fuel_surcharge' => $request->fuel_surcharge ?? 0,
            'handling_fee' => $request->handling_fee ?? 0,
            'insurance_charge' => $request->insurance_charge ?? 0,
            'customs_charge' => $request->customs_charge ?? 0,
            'total_charge' => $request->total_charge,
            'weight_kg' => $request->weight_kg,
            'distance_km' => $request->distance_km,
            'service_type' => $request->service_type,
            'service_tier' => $request->service_tier,
            'notes' => $request->notes,
            'status' => 'pending',
            'verification_status' => 'pending',
            'submitted_at' => now(),
            'submitted_by' => Auth::id(),
        ]);

        // Handle file uploads
        if ($request->hasFile('invoice_file')) {
            $path = $request->file('invoice_file')->store('partner_charges/invoices', 'public');
            $charge->update(['invoice_file' => $path]);
        }

        if ($request->hasFile('supporting_document')) {
            $path = $request->file('supporting_document')->store('partner_charges/documents', 'public');
            $charge->update(['supporting_document' => $path]);
        }

        // Calculate system comparison
        $charge->calculateDifference();

        return redirect()->route('admin.partner-charges.show', $charge->id)
            ->with('success', 'Charge submitted successfully! Waiting for admin verification.');
    }

    /**
     * Update charge status (Admin action)
     */
    public function updateStatus(Request $request, $id)
    {
        $charge = PartnerCharge::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:verify,dispute,adjust,approve,reject',
            'notes' => 'nullable|string',
            'dispute_reason' => 'required_if:action,dispute|string',
            'adjusted_amount' => 'required_if:action,adjust|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        switch ($request->action) {
            case 'verify':
                $charge->markAsVerified(Auth::id(), $request->notes);
                $message = 'Charge verified successfully!';
                break;

            case 'dispute':
                $charge->markAsDisputed(Auth::id(), $request->dispute_reason, $request->notes);
                $message = 'Charge disputed. Partner will be notified.';
                break;

            case 'adjust':
                $charge->markAsAdjusted(Auth::id(), $request->adjusted_amount, $request->notes);
                $message = 'Charge adjusted successfully!';
                break;

            case 'approve':
                $charge->markAsApproved(Auth::id(), $request->notes);
                $message = 'Charge approved for payment!';
                break;

            case 'reject':
                $charge->update([
                    'status' => 'rejected',
                    'admin_notes' => $request->notes,
                ]);
                $charge->addHistory('rejected', $request->notes);
                $message = 'Charge rejected.';
                break;

            default:
                return redirect()->back()->with('error', 'Invalid action.');
        }

        return redirect()->route('admin.partner-charges.show', $charge->id)
            ->with('success', $message);
    }

    /**
     * Get pending charges count (for sidebar badge)
     */
    public function getPendingCount()
    {
        return response()->json([
            'pending' => PartnerCharge::pending()->count(),
            'under_review' => PartnerCharge::underReview()->count(),
        ]);
    }

    /**
     * Export charges report
     */
    public function export(Request $request)
    {
        // This will be implemented with Laravel Excel
        return redirect()->back()->with('info', 'Export functionality coming soon!');
    }
}