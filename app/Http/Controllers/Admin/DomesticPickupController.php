<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Http\Request;

class DomesticPickupController extends Controller
{
    public function index()
    {
        $pickups = PickupRequest::with(['seller', 'assignedRider', 'partner'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('admin.domestic.pickups.index', compact('pickups'));
    }

    public function show($id)
    {
        $pickup = PickupRequest::with(['seller', 'assignedRider', 'partner', 'partnerStaff'])
            ->findOrFail($id);
            
        return view('admin.domestic.pickups.show', compact('pickup'));
    }
}