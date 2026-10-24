<?php

namespace App\Http\Controllers;

use App\Models\ShipmentAssignment;

class RecipientHandoverController extends Controller
{
    public function show(ShipmentAssignment $assignment)
    {
        return response()->view('tracking.recipient-handover', compact('assignment'))
            ->header('Cache-Control', 'private, no-store, max-age=0')
            ->header('Referrer-Policy', 'no-referrer');
    }
}
