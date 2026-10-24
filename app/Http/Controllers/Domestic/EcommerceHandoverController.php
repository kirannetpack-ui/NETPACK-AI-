<?php

namespace App\Http\Controllers\Domestic;

use App\Http\Controllers\Controller;
use App\Models\ShipmentAssignment;
use App\Services\EcommerceDispatchService;
use Illuminate\Http\Request;

class EcommerceHandoverController extends Controller
{
    public function index()
    {
        $legs = ShipmentAssignment::with('partner')->where('assignment_type', 'line_haul')->latest()->paginate(20);
        $assignments = ShipmentAssignment::whereIn('master_awb', $legs->pluck('master_awb'))->get()->groupBy('master_awb');
        return view('domestic.ecommerce.handovers', compact('legs', 'assignments'));
    }

    public function store(Request $request, int $assignment, EcommerceDispatchService $dispatch)
    {
        $data = $request->validate([
            'event' => 'required|in:dispatched,received',
            'location' => 'required|string|max:255',
            'notes' => 'required|string|min:5|max:1000',
        ]);
        $dispatch->recordLinehaulHandover($assignment, $data['event'], $request->user(), $data['location'], $data['notes']);
        return back()->with('success', 'Linehaul handover recorded.');
    }
}
