<?php

namespace App\Http\Controllers\Overseas;

use App\Http\Controllers\Controller;
use App\Models\OverseasHub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HubController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $overseas = Auth::user();
        
        $hubs = OverseasHub::where('partner_id', $overseas->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('overseas.hubs.index', compact('hubs'));
    }

    public function create()
    {
        return view('overseas.hubs.create');
    }

    public function store(Request $request)
    {
        $overseas = Auth::user();

        $validator = Validator::make($request->all(), [
            'hub_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'hub_type' => 'required|in:main_hub,transit_point,sorting_center,delivery_hub',
            'address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        OverseasHub::create([
            'partner_id' => $overseas->id,
            'hub_name' => $request->hub_name,
            'hub_code' => Str::upper(Str::random(8)),
            'location' => $request->location,
            'hub_type' => $request->hub_type,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_active' => true,
        ]);

        return redirect()->route('overseas.hubs.index')
            ->with('success', 'Hub created successfully!');
    }

    public function edit($id)
    {
        $overseas = Auth::user();
        $hub = OverseasHub::where('partner_id', $overseas->id)->findOrFail($id);
        
        return view('overseas.hubs.edit', compact('hub'));
    }

    public function update(Request $request, $id)
    {
        $overseas = Auth::user();
        $hub = OverseasHub::where('partner_id', $overseas->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'hub_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'hub_type' => 'required|in:main_hub,transit_point,sorting_center,delivery_hub',
            'address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $hub->update([
            'hub_name' => $request->hub_name,
            'location' => $request->location,
            'hub_type' => $request->hub_type,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('overseas.hubs.index')
            ->with('success', 'Hub updated successfully!');
    }

    public function destroy($id)
    {
        $overseas = Auth::user();
        $hub = OverseasHub::where('partner_id', $overseas->id)->findOrFail($id);
        $hub->delete();

        return redirect()->route('overseas.hubs.index')
            ->with('success', 'Hub deleted successfully!');
    }
}