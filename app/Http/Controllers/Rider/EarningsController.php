<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\RiderEarningsLedger;
use App\Models\RiderProfile;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EarningsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        /** @var User $rider */
        $rider = Auth::user();
        $profile = $rider->ensureRiderProfile();

        // Segregated Earnings from RiderEarningsLedger
        $earningsLedgers = $profile->earningsLedgers()->with('assignment')->latest()->paginate(15);
        $totalDeliveryEarnings = (float) $profile->earningsLedgers()->where('type', 'delivery_fee')->sum('amount');
        $totalBonus = (float) $profile->earningsLedgers()->where('type', 'bonus')->sum('amount');
        $totalPenalties = (float) $profile->earningsLedgers()->where('type', 'penalty')->sum('amount');
        $totalWithdrawn = (float) $profile->earningsLedgers()->where('type', 'withdrawal')->sum('amount');
        $availableBalance = (float) $profile->earnings_balance;

        // Legacy / aggregated stats
        $stats = [
            'total_earnings' => max($totalDeliveryEarnings + $totalBonus, (float) Delivery::where('rider_id', $rider->id)->where('status', 'delivered')->sum('delivery_fee')),
            'today_earnings' => (float) $profile->earningsLedgers()->whereDate('created_at', today())->whereIn('type', ['delivery_fee', 'bonus'])->sum('amount'),
            'week_earnings' => (float) $profile->earningsLedgers()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount'),
            'month_earnings' => (float) $profile->earningsLedgers()->whereMonth('created_at', now()->month)->sum('amount'),
            'total_deliveries' => max($profile->total_completed_deliveries, Delivery::where('rider_id', $rider->id)->where('status', 'delivered')->count()),
            'pending_deliveries' => $profile->assignments()->whereIn('status', ['accepted', 'arrived_pickup', 'picked_up', 'in_transit'])->count(),
        ];

        // Wallet balance
        $wallet = Wallet::where('user_id', $rider->id)->first();
        $walletBalance = $availableBalance ?: ($wallet ? (float) $wallet->balance : 0);

        return view('rider.earnings', compact(
            'rider',
            'profile',
            'stats',
            'walletBalance',
            'earningsLedgers',
            'totalDeliveryEarnings',
            'totalBonus',
            'totalPenalties',
            'totalWithdrawn'
        ));
    }
}