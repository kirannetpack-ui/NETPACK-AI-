<?php

namespace App\Http\Controllers\Overseas;

use App\Http\Controllers\Controller;
use App\Models\OverseasTransitPoint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransitPointController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($this->canManageAllPartners()) {
            return redirect()->route('international.hubs.index');
        }

        $query = OverseasTransitPoint::with('partner');
        $query->where('partner_id', auth()->id());

        $transitPoints = $query->orderBy('created_at', 'desc')->paginate(20);
        $partners = $this->availablePartners();
        $routePrefix = $this->routePrefix();

        return view('overseas.transit-points.index', compact('transitPoints', 'partners', 'routePrefix'));
    }

    public function create()
    {
        if ($this->canManageAllPartners()) {
            return redirect()->route('international.hubs.create');
        }

        $types = OverseasTransitPoint::TYPES;
        $partners = $this->availablePartners();
        $routePrefix = $this->routePrefix();
        
        return view('overseas.transit-points.create', compact('types', 'partners', 'routePrefix'));
    }

    public function store(Request $request)
    {
        if ($this->canManageAllPartners()) {
            return redirect()->route('international.hubs.index');
        }

        $validator = Validator::make($request->all(), [
            'partner_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:hub,transit',
            'location' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'is_mandatory' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if hub already exists for this partner
        if ($request->type === 'hub') {
            $existingHub = OverseasTransitPoint::where('partner_id', $request->partner_id)
                ->where('type', 'hub')
                ->first();

            if ($existingHub) {
                return redirect()->back()
                    ->withErrors(['type' => 'This partner already has a main hub assigned. Only one hub is allowed per partner.'])
                    ->withInput();
            }
        }

        // If this is set as mandatory, ensure no other is mandatory for this partner
        if ($request->boolean('is_mandatory')) {
            OverseasTransitPoint::where('partner_id', $request->partner_id)
                ->update(['is_mandatory' => false]);
        }

        OverseasTransitPoint::create([
            'partner_id' => $request->partner_id,
            'name' => $request->name,
            'type' => $request->type,
            'location' => $request->location,
            'country' => $request->country,
            'is_mandatory' => $request->boolean('is_mandatory'),
            'is_active' => true,
        ]);

        return redirect()->route($this->routePrefix().'.transit-points.index')
            ->with('success', 'Transit point added successfully!');
    }

    public function edit($id)
    {
        if ($this->canManageAllPartners()) {
            return redirect()->route('international.hubs.index');
        }

        $transitPoint = $this->findManagedTransitPoint($id)->load('partner');
        $types = OverseasTransitPoint::TYPES;
        $partners = $this->availablePartners();
        $routePrefix = $this->routePrefix();

        return view('overseas.transit-points.edit', compact('transitPoint', 'types', 'partners', 'routePrefix'));
    }

    public function update(Request $request, $id)
    {
        $transitPoint = $this->findManagedTransitPoint($id);

        $validator = Validator::make($request->all(), [
            'partner_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:hub,transit',
            'location' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'is_mandatory' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // If changing type to hub, check if hub already exists for this partner
        if ($request->type === 'hub') {
            $existingHub = OverseasTransitPoint::where('partner_id', $request->partner_id)
                ->where('type', 'hub')
                ->where('id', '!=', $id)
                ->first();
            
            if ($existingHub) {
                return redirect()->back()
                    ->with('error', 'This partner already has a Hub. Only one Hub is allowed per partner.')
                    ->withInput();
            }
        }

        $transitPoint->update([
            'partner_id' => $this->canManageAllPartners() ? $request->integer('partner_id') : auth()->id(),
            'name' => $request->name,
            'type' => $request->type,
            'location' => $request->location,
            'country' => $request->country,
            'is_mandatory' => $request->has('is_mandatory'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route($this->routePrefix().'.transit-points.index')
            ->with('success', 'Transit point updated successfully!');
    }

    public function destroy($id)
    {
        $transitPoint = $this->findManagedTransitPoint($id);
        $transitPoint->delete();

        return redirect()->route($this->routePrefix().'.transit-points.index')
            ->with('success', 'Transit point deleted successfully!');
    }

    public function toggle($id)
    {
        $transitPoint = $this->findManagedTransitPoint($id);
        $transitPoint->update(['is_active' => !$transitPoint->is_active]);

        $status = $transitPoint->is_active ? 'activated' : 'deactivated';
        return redirect()->route($this->routePrefix().'.transit-points.index')
            ->with('success', "Transit point {$status} successfully!");
    }

    private function canManageAllPartners(): bool
    {
        return in_array(auth()->user()->user_type, ['super_admin', 'admin', 'staff', 'international_admin'], true);
    }

    private function availablePartners()
    {
        if (!$this->canManageAllPartners()) {
            return User::whereKey(auth()->id())->get();
        }

        return User::whereIn('user_type', ['overseas', 'overseas_partner'])->get();
    }

    private function findManagedTransitPoint($id): OverseasTransitPoint
    {
        return OverseasTransitPoint::query()
            ->when(!$this->canManageAllPartners(), fn ($query) => $query->where('partner_id', auth()->id()))
            ->findOrFail($id);
    }

    private function routePrefix(): string
    {
        return $this->canManageAllPartners() ? 'international' : 'overseas';
    }
}
