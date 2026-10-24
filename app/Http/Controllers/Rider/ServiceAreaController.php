<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\RiderProfile;
use App\Models\RiderServiceArea;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceAreaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function getRiderProfile(): RiderProfile
    {
        /** @var User $user */
        $user = Auth::user();
        return $user->ensureRiderProfile();
    }

    /**
     * Display rider's configured delivery service areas & coverage radius
     */
    public function index()
    {
        $rider = $this->getRiderProfile();
        $serviceAreas = $rider->serviceAreas()->latest()->get();

        return view('rider.service_areas', compact('rider', 'serviceAreas'));
    }

    /**
     * Add a service area for the rider
     */
    public function store(Request $request)
    {
        $request->validate([
            'district' => 'required|string|max:100',
            'province' => 'nullable|string|max:100',
            'municipality' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:10',
            'area_name' => 'nullable|string|max:100',
            'service_radius_km' => 'nullable|numeric|min:1|max:50',
        ]);

        $rider = $this->getRiderProfile();

        $rider->serviceAreas()->create([
            'province' => $request->province ?: $rider->province,
            'district' => $request->district,
            'municipality' => $request->municipality,
            'ward' => $request->ward,
            'area_name' => $request->area_name,
            'service_radius_km' => (float) ($request->service_radius_km ?: $rider->service_radius_km ?: 10.0),
            'is_active' => true,
        ]);

        return redirect()->route('rider.service-areas.index')
            ->with('success', "Service area for {$request->district}" . ($request->area_name ? " ({$request->area_name})" : '') . " added successfully!");
    }

    /**
     * Remove a service area
     */
    public function destroy($id)
    {
        $rider = $this->getRiderProfile();
        $serviceArea = $rider->serviceAreas()->findOrFail($id);
        $serviceArea->delete();

        return redirect()->route('rider.service-areas.index')
            ->with('success', 'Service area removed successfully.');
    }

    /**
     * Toggle active state for service area
     */
    public function toggle($id)
    {
        $rider = $this->getRiderProfile();
        $serviceArea = $rider->serviceAreas()->findOrFail($id);
        $serviceArea->update(['is_active' => !$serviceArea->is_active]);

        $status = $serviceArea->is_active ? 'activated' : 'deactivated';
        return redirect()->route('rider.service-areas.index')
            ->with('success', "Service area {$status}.");
    }
}
