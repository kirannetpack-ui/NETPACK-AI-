<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\RiderDeposit;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiderMonitoringController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,super_admin,domestic_admin']);
    }

    /**
     * Rider Monitoring Dashboard
     */
    public function dashboard()
    {
        // Get all riders
        $riders = User::where('user_type', 'rider')
            ->withCount(['deliveries' => function($query) {
                $query->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery']);
            }])
            ->get();

        // Active orders count
        $activeOrders = Order::whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])
            ->whereNotNull('rider_id')
            ->count();

        // Online riders
        $onlineRiders = User::where('user_type', 'rider')
            ->where('is_online', true)
            ->count();

        // Today's earnings
        $todayEarnings = Delivery::where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->sum('delivery_fee');

        // Total deliveries
        $totalDeliveries = Delivery::where('status', 'delivered')->count();

        // Active deliveries with rider info
        $activeDeliveries = Delivery::with(['order', 'rider'])
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Recent deliveries history
        $recentDeliveries = Delivery::with(['order', 'rider'])
            ->where('status', 'delivered')
            ->orderBy('delivered_at', 'desc')
            ->limit(20)
            ->get();

        // Rider performance stats
        $riderStats = User::where('user_type', 'rider')
            ->withCount(['deliveries as total_deliveries' => function($query) {
                $query->where('status', 'delivered');
            }])
            ->withSum(['deliveries as total_earnings' => function($query) {
                $query->where('status', 'delivered');
            }], 'delivery_fee')
            ->get();

        return view('admin.riders.dashboard', compact(
            'riders',
            'activeOrders',
            'onlineRiders',
            'todayEarnings',
            'totalDeliveries',
            'activeDeliveries',
            'recentDeliveries',
            'riderStats'
        ));
    }

    /**
     * Get rider locations for map (AJAX)
     */
    public function getRiderLocations()
    {
        $riders = User::where('user_type', 'rider')
            ->where('is_online', true)
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->with(['deliveries' => function($query) {
                $query->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])
                    ->with('order');
            }])
            ->get();

        $data = [];
        foreach ($riders as $rider) {
            $delivery = $rider->deliveries->first();
            $data[] = [
                'id' => $rider->id,
                'name' => $rider->name,
                'latitude' => $rider->current_latitude,
                'longitude' => $rider->current_longitude,
                'status' => $rider->is_online ? 'online' : 'offline',
                'delivery_status' => $delivery ? $delivery->status : null,
                'order_number' => $delivery && $delivery->order ? $delivery->order->order_number : null,
                'last_update' => $rider->last_location_update ? $rider->last_location_update->diffForHumans() : 'N/A',
            ];
        }

        return response()->json($data);
    }

    /**
     * Get rider details
     */
    public function riderDetails($id)
    {
        $rider = User::where('user_type', 'rider')->findOrFail($id);

        // Get rider statistics
        $stats = [
            'total_deliveries' => Delivery::where('rider_id', $id)->where('status', 'delivered')->count(),
            'total_earnings' => Delivery::where('rider_id', $id)->where('status', 'delivered')->sum('delivery_fee'),
            'active_deliveries' => Delivery::where('rider_id', $id)
                ->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])
                ->count(),
            'pending_deliveries' => Delivery::where('rider_id', $id)->where('status', 'pending')->count(),
            'deposit_balance' => $rider->rider_deposit_balance ?? 0,
            'deposit_limit' => $rider->rider_deposit_limit ?? 50000,
            'rating' => $rider->rating ?? 0,
            'today_earnings' => Delivery::where('rider_id', $id)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', today())
                ->sum('delivery_fee'),
            'week_earnings' => Delivery::where('rider_id', $id)
                ->where('status', 'delivered')
                ->whereBetween('delivered_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('delivery_fee'),
            'month_earnings' => Delivery::where('rider_id', $id)
                ->where('status', 'delivered')
                ->whereMonth('delivered_at', now()->month)
                ->sum('delivery_fee'),
        ];

        // Get delivery history
        $deliveries = Delivery::where('rider_id', $id)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get deposit history
        $deposits = RiderDeposit::where('rider_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Get recent transactions
        $transactions = Transaction::whereHas('wallet', function($query) use ($id) {
            $query->where('user_id', $id);
        })->orderBy('created_at', 'desc')
          ->limit(20)
          ->get();

        return view('admin.riders.details', compact(
            'rider',
            'stats',
            'deliveries',
            'deposits',
            'transactions'
        ));
    }

    /**
     * Get delivery tracking details
     */
    public function trackDelivery($id)
    {
        $delivery = Delivery::with(['order', 'rider'])
            ->findOrFail($id);

        return view('admin.riders.track-delivery', compact('delivery'));
    }

    /**
     * Export rider report
     */
    public function exportReport(Request $request)
    {
        $query = User::where('user_type', 'rider');

        if ($request->filled('status')) {
            $query->where('is_online', $request->status === 'online');
        }

        $riders = $query->withCount(['deliveries as total_deliveries' => function($q) {
            $q->where('status', 'delivered');
        }])->get();

        $filename = "rider_report_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://temp', 'w+');

        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'Rider Name',
            'Email',
            'Phone',
            'Vehicle Type',
            'Status',
            'Total Deliveries',
            'Total Earnings',
            'Deposit Balance',
            'Rating',
            'Joined Date',
        ]);

        foreach ($riders as $rider) {
            fputcsv($handle, [
                $rider->name,
                $rider->email,
                $rider->phone ?? 'N/A',
                $rider->vehicle_type ?? 'N/A',
                $rider->is_online ? 'Online' : 'Offline',
                $rider->total_deliveries ?? 0,
                number_format(Delivery::where('rider_id', $rider->id)->where('status', 'delivered')->sum('delivery_fee'), 2),
                number_format($rider->rider_deposit_balance ?? 0, 2),
                $rider->rating ?? 0,
                $rider->created_at->format('Y-m-d'),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename={$filename}")
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Pragma', 'public');
    }
}