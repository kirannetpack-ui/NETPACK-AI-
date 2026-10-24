<?php
// app/Http/Controllers/Agency/AgencyDashboardController.php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;

class AgencyDashboardController extends Controller
{
    public function index()
    {
        $agency = Auth::guard('agency')->user();
        
        $stats = [
            'arrived' => Shipment::where('current_agency_id', $agency->id)
                ->whereNotNull('arrived_at_agency')
                ->whereNull('departed_from_agency')
                ->count(),
            'departed' => Shipment::where('current_agency_id', $agency->id)
                ->whereNotNull('departed_from_agency')
                ->count(),
            'total_processed' => Shipment::where('current_agency_id', $agency->id)->count(),
        ];
        
        $recentArrivals = Shipment::where('current_agency_id', $agency->id)
            ->whereNotNull('arrived_at_agency')
            ->whereNull('departed_from_agency')
            ->latest('arrived_at_agency')
            ->limit(10)
            ->get();
        
        return view('agency.dashboard', compact('agency', 'stats', 'recentArrivals'));
    }
}