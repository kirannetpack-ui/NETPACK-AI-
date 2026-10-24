<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DomesticPartner;
use App\Models\DeliveryZone;
use App\Models\DomesticRate;
use App\Models\PickupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Use web guard for partner authentication
        $this->middleware('auth');
    }

    public function index()
{
    // Get the authenticated user (partner)
    $partner = Auth::user();
    
    // Check if user is a partner
    if (!$partner || $partner->user_type !== 'partner') {
        abort(403, 'Unauthorized access. Partner only area.');
    }

    // Get partner statistics
    $stats = [
        'total_pickups' => PickupRequest::where('partner_id', $partner->id)->count(),
        'pending_pickups' => PickupRequest::where('partner_id', $partner->id)
            ->where('status', 'pending')
            ->count(),
        'completed_pickups' => PickupRequest::where('partner_id', $partner->id)
            ->where('status', 'delivered')
            ->count(),
        'total_zones' => DeliveryZone::where('partner_user_id', $partner->id)->count(),
        'total_rates' => DomesticRate::where('partner_id', $partner->id)->count(),
        'active_rates' => DomesticRate::where('partner_id', $partner->id)
            ->where('is_active', true)
            ->count(),
        'delayed_deliveries' => PickupRequest::where('partner_id', $partner->id)
            ->where('is_delayed', true)
            ->where('status', '!=', 'delivered')
            ->where('status', '!=', 'cancelled')
            ->count(),
    ];

    // Get recent pickups
    $recentPickups = PickupRequest::where('partner_id', $partner->id)
        ->with(['seller', 'assignedRider'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    // Get zones with rates count - use withCount only if relationships exist
    $zones = DeliveryZone::where('partner_user_id', $partner->id)
        ->get()
        ->map(function ($zone) {
            // Manually count rates if relationships don't exist
            $zone->origin_rates_count = DomesticRate::where('origin_zone_id', $zone->id)->count();
            $zone->destination_rates_count = DomesticRate::where('destination_zone_id', $zone->id)->count();
            return $zone;
        });

    return view('partner.dashboard', compact('partner', 'stats', 'recentPickups', 'zones'));
}


    public function staffDashboard()
    {
        $staff = Auth::user();
        
        if (!$staff || $staff->user_type !== 'partner_staff') {
            abort(403, 'Unauthorized access.');
        }

        $partner = User::find($staff->partner_id);
        
        return view('partner.staff.dashboard', compact('staff', 'partner'));
    }
}