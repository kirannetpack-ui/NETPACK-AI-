<?php

namespace App\Http\Controllers\Overseas;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $staff = Auth::user();

        $stats = [
            'total_processed' => Shipment::where('processed_by', $staff->id)->count(),
            'today_processed' => Shipment::where('processed_by', $staff->id)
                ->whereDate('updated_at', today())
                ->count(),
            'pending_shipments' => Shipment::where('status', 'pending')->count(),
        ];

        $recentShipments = Shipment::where('processed_by', $staff->id)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('overseas.staff.dashboard', compact('stats', 'recentShipments'));
    }
}