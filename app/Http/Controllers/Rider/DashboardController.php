<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\Transaction;
use App\Models\Wallet;
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
        $rider = Auth::user();
        $riderId = $rider->id;

        // Today's stats
        $today = now()->startOfDay();
        $todayDeliveries = Delivery::where('rider_id', $riderId)
            ->whereDate('created_at', $today)
            ->count();
        $todayCompleted = Delivery::where('rider_id', $riderId)
            ->whereDate('delivered_at', $today)
            ->where('status', 'delivered')
            ->count();

        // Active deliveries
        $activeDeliveries = Delivery::where('rider_id', $riderId)
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])
            ->count();

        // Earnings
        $totalEarnings = Delivery::where('rider_id', $riderId)
            ->where('status', 'delivered')
            ->sum('delivery_fee');

        $todayEarnings = Delivery::where('rider_id', $riderId)
            ->whereDate('delivered_at', $today)
            ->where('status', 'delivered')
            ->sum('delivery_fee');

        // Rating
        $rating = $rider->rating ?? 0;

        // Wallet balance
        $wallet = Wallet::where('user_id', $riderId)->first();
        $balance = $wallet ? $wallet->balance : 0;

        // Recent deliveries
        $recentDeliveries = Delivery::where('rider_id', $riderId)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Available orders count
        $availableOrders = Order::where('status', 'pending')
            ->whereNull('rider_id')
            ->count();

        return view('rider.dashboard', compact(
            'rider',
            'todayDeliveries',
            'todayCompleted',
            'activeDeliveries',
            'totalEarnings',
            'todayEarnings',
            'rating',
            'balance',
            'recentDeliveries',
            'availableOrders'
        ));
    }

    /**
     * Toggle rider online/offline status
     */
    public function toggleStatus()
    {
        /** @var User $rider */
        $rider = Auth::user();
        $rider->is_online = !$rider->is_online;
        $rider->is_available = $rider->is_online;
        $rider->save();

        $profile = $rider->ensureRiderProfile();
        $status = $rider->is_online ? 'online' : 'offline';
        $profile->update([
            'availability_status' => $status,
        ]);

        return redirect()->route('rider.dashboard')
            ->with('success', "You are now {$status}");
    }

    /**
     * Update rider location
     */
    public function updateLocation(Request $request)
    {
        $rider = Auth::user();
        
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $rider->update([
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude,
            'last_location_update' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully'
        ]);
    }

    /**
     * Get rider stats for API
     */
    public function getStats()
    {
        $riderId = Auth::id();
        
        $stats = [
            'online' => Auth::user()->is_online,
            'available' => Auth::user()->is_available,
            'active_deliveries' => Delivery::where('rider_id', $riderId)
                ->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])
                ->count(),
            'today_earnings' => Delivery::where('rider_id', $riderId)
                ->whereDate('delivered_at', now()->startOfDay())
                ->where('status', 'delivered')
                ->sum('delivery_fee'),
        ];

        return response()->json($stats);
    }
}