<?php

namespace App\Http\Controllers\International;

use App\Http\Controllers\Controller;
use App\Models\MAWB;
use App\Models\OverseasHub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MAWBController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = MAWB::with(['hub', 'manifest', 'creator']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('hub_id')) {
            $query->where('hub_id', $request->hub_id);
        }

        if ($request->filled('airline')) {
            $query->where('airline_name', 'like', "%{$request->airline}%");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('mawb_number', 'like', "%{$search}%")
                  ->orWhere('airline_name', 'like', "%{$search}%")
                  ->orWhere('flight_number', 'like', "%{$search}%");
            });
        }

        $mawbs = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total' => MAWB::count(),
            'unused' => MAWB::unused()->count(),
            'assigned' => MAWB::where('status', 'assigned')->count(),
            'in_transit' => MAWB::where('status', 'in_transit')->count(),
        ];

        $hubs = OverseasHub::active()->orderBy('sort_order')->get();

        return view('international.mawbs.index', compact('mawbs', 'stats', 'hubs'));
    }

    public function create()
    {
        $hubs = OverseasHub::active()->orderBy('sort_order')->get();
        return view('international.mawbs.create', compact('hubs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mawb_number' => 'required|string|max:50|unique:mawbs,mawb_number',
            'airline_name' => 'required|string|max:100',
            'airline_code' => 'nullable|string|max:10',
            'origin_airport' => 'required|string|max:100',
            'destination_airport' => 'nullable|string|max:100',
            'hub_id' => 'nullable|exists:overseas_hubs,id',
            'flight_number' => 'nullable|string|max:30',
            'flight_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        MAWB::create([
            'mawb_number' => trim($validated['mawb_number']),
            'airline_name' => $validated['airline_name'],
            'airline_code' => strtoupper($validated['airline_code'] ?? ''),
            'origin_airport' => $validated['origin_airport'],
            'destination_airport' => $validated['destination_airport'] ?? null,
            'hub_id' => $validated['hub_id'] ?? null,
            'flight_number' => $validated['flight_number'] ?? null,
            'flight_date' => $validated['flight_date'] ?? null,
            'status' => 'unused',
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('international.mawbs.index')
            ->with('success', "MAWB #{$validated['mawb_number']} pre-fed into system inventory as UNUSED.");
    }

    public function edit($id)
    {
        $mawb = MAWB::findOrFail($id);
        $hubs = OverseasHub::active()->orderBy('sort_order')->get();

        return view('international.mawbs.edit', compact('mawb', 'hubs'));
    }

    public function update(Request $request, $id)
    {
        $mawb = MAWB::findOrFail($id);

        $validated = $request->validate([
            'mawb_number' => 'required|string|max:50|unique:mawbs,mawb_number,' . $mawb->id,
            'airline_name' => 'required|string|max:100',
            'airline_code' => 'nullable|string|max:10',
            'origin_airport' => 'required|string|max:100',
            'destination_airport' => 'nullable|string|max:100',
            'hub_id' => 'nullable|exists:overseas_hubs,id',
            'flight_number' => 'nullable|string|max:30',
            'flight_date' => 'nullable|date',
            'status' => 'required|in:unused,assigned,in_transit,cleared,completed',
            'notes' => 'nullable|string|max:1000',
        ]);

        $previousStatus = $mawb->status;

        $mawb->update([
            'mawb_number' => trim($validated['mawb_number']),
            'airline_name' => $validated['airline_name'],
            'airline_code' => strtoupper($validated['airline_code'] ?? ''),
            'origin_airport' => $validated['origin_airport'],
            'destination_airport' => $validated['destination_airport'] ?? null,
            'hub_id' => $validated['hub_id'] ?? null,
            'flight_number' => $validated['flight_number'] ?? null,
            'flight_date' => $validated['flight_date'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Automated Tracking Cascade to all attached consignments
        $cascadeInfo = '';
        if ($validated['status'] !== $previousStatus && in_array($validated['status'], ['in_transit', 'cleared', 'completed'])) {
            $cascadeRes = app(\App\Services\AutomatedTrackingService::class)->cascadeMawbMilestone(
                $mawb,
                $validated['status'],
                null,
                $validated['notes'] ?? null,
                Auth::user()
            );
            $cascadeInfo = " Automatically cascaded milestone to {$cascadeRes['updated_count']} consignments.";
        }

        return redirect()->route('international.mawbs.index')
            ->with('success', "MAWB #{$mawb->mawb_number} updated successfully.{$cascadeInfo}");
    }

    public function destroy($id)
    {
        $mawb = MAWB::findOrFail($id);

        if ($mawb->assigned_manifest_id || $mawb->status !== 'unused') {
            return back()->with('error', "Cannot delete MAWB #{$mawb->mawb_number}. It is already assigned to a manifest.");
        }

        $mawb->delete();

        return redirect()->route('international.mawbs.index')
            ->with('success', "MAWB #{$mawb->mawb_number} removed from inventory.");
    }
}
