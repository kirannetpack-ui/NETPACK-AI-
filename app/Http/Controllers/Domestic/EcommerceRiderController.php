<?php

namespace App\Http\Controllers\Domestic;

use App\Http\Controllers\Controller;
use App\Models\RiderCodLedger;
use App\Models\RiderProfile;
use App\Models\ShipmentAssignment;
use App\Services\EcommerceDispatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EcommerceRiderController extends Controller
{
    protected EcommerceDispatchService $dispatchService;

    public function __construct(EcommerceDispatchService $dispatchService)
    {
        $this->dispatchService = $dispatchService;
    }

    /**
     * List all direct registered riders
     */
    public function index(Request $request)
    {
        $query = RiderProfile::with('user');

        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        if ($request->filled('affiliation') && $request->affiliation !== 'all') {
            if ($request->affiliation === 'independent') {
                $query->where('affiliation', 'none')->orWhere('has_other_platform_affiliation', false);
            } else {
                $query->where('affiliation', $request->affiliation);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('rider_code', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%");
            });
        }

        $riders = $query->latest()->paginate(15);
        $pendingCount = RiderProfile::where('verification_status', 'pending')->count();
        $verifiedCount = RiderProfile::where('verification_status', 'verified')->count();
        $totalCodOutstanding = RiderProfile::sum('current_outstanding_cod');

        return view('domestic.ecommerce.riders.index', compact(
            'riders',
            'pendingCount',
            'verifiedCount',
            'totalCodOutstanding'
        ));
    }

    /**
     * View detailed rider KYC profile
     */
    public function show($id)
    {
        $rider = RiderProfile::with(['user', 'codLedgers.approver', 'earningsLedgers', 'assignments' => function ($q) {
            $q->latest()->limit(10);
        }])->findOrFail($id);

        return view('domestic.ecommerce.riders.show', compact('rider'));
    }

    /**
     * Approve and verify rider KYC
     */
    public function verify(Request $request, $id)
    {
        $rider = RiderProfile::findOrFail($id);

        $codLevel = $request->input('cod_level', 'level_1');
        $codLimits = [
            'level_0' => 0.00,
            'level_1' => 5000.00,
            'level_2' => 20000.00,
            'level_3' => 50000.00,
            'level_4' => (float) $request->input('custom_limit', 100000.00),
        ];

        $limit = $codLimits[$codLevel] ?? 5000.00;

        $rider->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
            'badge_status' => 'verified',
            'cod_level' => $codLevel,
            'cod_limit' => $limit,
            'rejection_reason' => null,
        ]);

        if ($rider->user) {
            $rider->user->update(['verification_status' => 'approved']);
        }

        return redirect()->back()->with('success', "Rider {$rider->full_name} ({$rider->rider_code}) has been verified with COD limit of Rs. ".number_format($limit, 2));
    }

    /**
     * Reject rider KYC application
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $rider = RiderProfile::findOrFail($id);
        $rider->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'badge_status' => 'new',
        ]);

        if ($rider->user) {
            $rider->user->update(['verification_status' => 'rejected']);
        }

        return redirect()->back()->with('error', 'Rider application rejected.');
    }

    /**
     * Suspend rider
     */
    public function suspend(Request $request, $id)
    {
        $rider = RiderProfile::findOrFail($id);
        $rider->update([
            'verification_status' => 'suspended',
            'badge_status' => 'suspended',
            'availability_status' => 'offline',
            'rejection_reason' => $request->input('reason', 'Suspended by administration'),
        ]);

        return redirect()->back()->with('info', "Rider {$rider->full_name} has been suspended.");
    }

    /**
     * Rider Rate Rules configuration
     */
    public function rateRules()
    {
        $rule = $this->dispatchService->getActiveRateRule('motorcycle');

        return view('domestic.ecommerce.riders.rates', compact('rule'));
    }

    /**
     * Update Rider Rate Rules
     */
    public function updateRateRules(Request $request)
    {
        $request->validate([
            'base_distance_km' => 'required|numeric|min:1',
            'base_rate' => 'required|numeric|min:0',
            'additional_km_rate' => 'required|numeric|min:0',
            'weight_surcharge_per_kg' => 'required|numeric|min:0',
            'cod_handling_fee' => 'required|numeric|min:0',
        ]);

        $rule = $this->dispatchService->getActiveRateRule('motorcycle');
        $rule->update($request->only([
            'base_distance_km',
            'base_rate',
            'additional_km_rate',
            'weight_surcharge_per_kg',
            'cod_handling_fee',
        ]));

        return redirect()->back()->with('success', 'Rider delivery fare calculation formula updated successfully.');
    }

    /**
     * View all COD transactions and pending deposits
     */
    public function codLedger(Request $request)
    {
        $query = RiderCodLedger::with(['riderProfile.user', 'assignment', 'approver']);

        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('transaction_type', $request->type);
        }

        $ledgers = $query->latest()->paginate(20);
        $pendingDepositsCount = RiderCodLedger::where('transaction_type', 'deposited')->where('approval_status', 'pending')->count();
        $totalOutstandingCod = RiderProfile::sum('current_outstanding_cod');

        return view('domestic.ecommerce.riders.cod_ledger', compact(
            'ledgers',
            'pendingDepositsCount',
            'totalOutstandingCod'
        ));
    }

    /**
     * Approve rider COD deposit
     */
    public function approveDeposit(Request $request, $id)
    {
        return DB::transaction(function () use ($id) {
            $ledger = RiderCodLedger::lockForUpdate()->findOrFail($id);
            abort_unless($ledger->transaction_type === 'deposited' && $ledger->amount < 0, 422, 'Only deposit entries can be approved.');

            if ($ledger->approval_status === 'approved') {
                return redirect()->back()->with('info', 'Deposit already approved.');
            }

            $depositAmount = abs($ledger->amount);
            abort_unless($ledger->approval_status === 'pending', 422, 'Only pending deposits can be approved.');
            $rider = RiderProfile::lockForUpdate()->findOrFail($ledger->rider_profile_id);
            abort_if($depositAmount > $rider->current_outstanding_cod, 422, 'Deposit exceeds outstanding COD.');

            // Deduct from outstanding COD balance
            $newBalance = max(0, $rider->current_outstanding_cod - $depositAmount);
            $rider->update(['current_outstanding_cod' => $newBalance]);

            $ledger->update([
                'approval_status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'balance_after' => $newBalance,
            ]);

            return redirect()->back()->with('success', 'Deposit of Rs. '.number_format($depositAmount, 2)." approved for Rider {$rider->full_name}. Outstanding COD updated.");
        }, 3);
    }

    /**
     * Dispatcher direct assign a rider to a shipment assignment (Method A)
     */
    public function directAssign(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:shipment_assignments,id',
            'rider_profile_id' => 'required|exists:rider_profiles,id',
        ]);

        $assignment = ShipmentAssignment::findOrFail($request->assignment_id);
        $rider = RiderProfile::findOrFail($request->rider_profile_id);

        $result = $this->dispatchService->directAssignRider($assignment, $rider);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }
}
