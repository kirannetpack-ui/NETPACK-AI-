<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DeliveryZoneController extends Controller
{
    public function index()
    {
        $zones = DeliveryZone::with('partner')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        $partners = User::where('user_type', 'partner')->get();
        
        return view('admin.domestic.zones.index', compact('zones', 'partners'));
    }

    public function create()
    {
        $partners = User::where('user_type', 'partner')->get();
        $zoneTypes = ['urban', 'semi_urban', 'rural', 'hilly', 'himalayan'];
        
        return view('admin.domestic.zones.create', compact('partners', 'zoneTypes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'partner_id' => 'required|exists:users,id',
            'zone_name' => 'required|string|max:255',
            'zone_type' => 'required|in:partner,urban,semi_urban,rural,hilly,himalayan',
            'districts' => 'nullable|array',
            'municipalities' => 'nullable|array',
            'wards' => 'nullable|array',
            'postal_codes' => 'nullable|array',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        do {
            $zoneCode = Str::upper(Str::random(6));
        } while (DeliveryZone::where('zone_code', $zoneCode)->exists());

        DeliveryZone::create([
            'partner_user_id' => $request->partner_id,
            'partner_id' => null,
            'admin_id' => $request->user()->id,
            'zone_name' => $request->zone_name,
            'zone_code' => $zoneCode,
            'zone_type' => $request->zone_type,
            'districts' => $request->districts ?? [],
            'municipalities' => $request->municipalities ?? [],
            'wards' => $request->wards ?? [],
            'postal_codes' => $request->postal_codes ?? [],
            'description' => $request->description,
            'is_active' => true,
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.domestic.zones')
            ->with('success', 'Delivery zone created successfully! Code: ' . $zoneCode);
    }

    public function edit($id)
    {
        $zone = DeliveryZone::findOrFail($id);
        $partners = User::where('user_type', 'partner')->get();
        $zoneTypes = ['urban', 'semi_urban', 'rural', 'hilly', 'himalayan'];
        
        return view('admin.domestic.zones.edit', compact('zone', 'partners', 'zoneTypes'));
    }

    public function update(Request $request, $id)
    {
        $zone = DeliveryZone::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'partner_id' => 'required|exists:users,id',
            'zone_name' => 'required|string|max:255',
            'zone_type' => 'required|in:partner,urban,semi_urban,rural,hilly,himalayan',
            'districts' => 'nullable|array',
            'municipalities' => 'nullable|array',
            'wards' => 'nullable|array',
            'postal_codes' => 'nullable|array',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $zone->update([
            'partner_user_id' => $request->partner_id,
            'zone_name' => $request->zone_name,
            'zone_type' => $request->zone_type,
            'districts' => $request->districts ?? [],
            'municipalities' => $request->municipalities ?? [],
            'wards' => $request->wards ?? [],
            'postal_codes' => $request->postal_codes ?? [],
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.domestic.zones')
            ->with('success', 'Delivery zone updated successfully!');
    }

    public function destroy($id)
    {
        $zone = DeliveryZone::findOrFail($id);
        $zone->delete();

        return redirect()->route('admin.domestic.zones')
            ->with('success', 'Delivery zone deleted successfully!');
    }

    public function approve(Request $request, DeliveryZone $zone)
    {
        $zone->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
            'is_active' => true,
            'admin_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Partner territory approved and activated.');
    }

    public function reject(Request $request, DeliveryZone $zone)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|min:5|max:1000']);
        $zone->update([
            'approval_status' => 'rejected',
            'approved_at' => now(),
            'rejection_reason' => $data['rejection_reason'],
            'is_active' => false,
            'admin_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Partner territory rejected and kept unavailable.');
    }
}
