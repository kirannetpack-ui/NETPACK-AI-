<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use Illuminate\Http\Request;

class PickupController extends Controller
{
    public function index(Request $request)
    {
        $query = PickupRequest::orderBy('created_at', 'desc');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->type == 'ecommerce') {
            $query->where('is_ecommerce', true);
        }

        $pickups = $query->paginate(20);
        return view('admin.pickups.index', compact('pickups'));
    }

    public function show($id)
    {
        $pickup = PickupRequest::findOrFail($id);
        return view('admin.pickups.show', compact('pickup'));
    }

    public function updateStatus(Request $request, $id)
    {
        $pickup = PickupRequest::findOrFail($id);
        $pickup->update(['status' => $request->status]);
        return back()->with('success', 'Status updated successfully!');
    }

    public function assignRider(Request $request, $id)
    {
        $pickup = PickupRequest::findOrFail($id);
        $pickup->update(['assigned_rider_id' => $request->rider_id]);
        return back()->with('success', 'Rider assigned successfully!');
    }
}