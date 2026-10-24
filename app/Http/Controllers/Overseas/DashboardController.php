<?php

namespace App\Http\Controllers\Overseas;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $overseas = Auth::user();

        $stats = [
            'total_shipments' => Shipment::where('overseas_partner_id', $overseas->id)->count(),
            'pending_shipments' => Shipment::where('overseas_partner_id', $overseas->id)
                ->where('status', 'pending')
                ->count(),
            'in_transit_shipments' => Shipment::where('overseas_partner_id', $overseas->id)
                ->where('status', 'in_transit')
                ->count(),
            'delivered_shipments' => Shipment::where('overseas_partner_id', $overseas->id)
                ->where('status', 'delivered')
                ->count(),
            'total_revenue' => Shipment::where('overseas_partner_id', $overseas->id)
                ->where('status', 'delivered')
                ->sum('total_amount'),
            'total_agents' => User::where('user_type', 'overseas_agent')->count(),
            'active_hubs' => \App\Models\OverseasHub::where('partner_id', $overseas->id)
                ->where('is_active', true)
                ->count(),
        ];

        $recentShipments = Shipment::where('overseas_partner_id', $overseas->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('overseas.dashboard', compact('stats', 'recentShipments'));
    }
}