<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shipment;
use App\Models\DomesticShipment;
use App\Models\PickupRequest;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        // Get summary statistics
        $stats = [
            'total_users' => User::count(),
            'total_shipments' => Shipment::count(),
            'total_domestic_shipments' => DomesticShipment::count(),
            'total_pickups' => PickupRequest::count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_sellers' => User::where('user_type', 'seller')->count(),
            'total_riders' => User::where('user_type', 'rider')->count(),
            'total_partners' => User::where('user_type', 'partner')->count(),
            'total_overseas' => User::where('user_type', 'overseas')->count(),
            'pending_users' => User::where('verification_status', 'pending')->count(),
            'pending_shipments' => Shipment::where('status', 'pending')->count(),
            'in_transit_shipments' => Shipment::where('status', 'in_transit')->count(),
            'delivered_shipments' => Shipment::where('status', 'delivered')->count(),
            'revenue_today' => Shipment::whereDate('created_at', today())->sum('total_amount'),
            'revenue_month' => Shipment::whereMonth('created_at', now()->month)->sum('total_amount'),
            'revenue_total' => Shipment::sum('total_amount'),
        ];

        // Get recent shipments
        $recentShipments = Shipment::with(['customer', 'rider'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get top partners by shipment count
        $topPartners = User::where('user_type', 'partner')
            ->withCount(['shipments' => function($query) {
                $query->where('status', 'delivered');
            }])
            ->orderBy('shipments_count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.reports.index', compact('stats', 'recentShipments', 'topPartners'));
    }

    /**
     * Shipment reports
     */
    public function shipments(Request $request)
    {
        $query = Shipment::with(['customer', 'rider', 'seller']);

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by service type
        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        $shipments = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.reports.shipments', compact('shipments'));
    }

    /**
     * Financial reports
     */
    public function financial(Request $request)
    {
        // Get financial summary
        $summary = [
            'total_revenue' => Shipment::sum('total_amount'),
            'total_shipping_cost' => Shipment::sum('shipping_cost'),
            'total_handling_fee' => Shipment::sum('handling_fee'),
            'total_insurance_fee' => Shipment::sum('insurance_fee'),
        ];

        // Revenue by month
        $monthlyRevenue = Shipment::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Revenue by service type
        $serviceRevenue = Shipment::select(
                'service_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('service_type')
            ->get();

        return view('admin.reports.financial', compact('summary', 'monthlyRevenue', 'serviceRevenue'));
    }

    /**
     * Partner reports
     */
    public function partners(Request $request)
    {
        $partners = User::where('user_type', 'partner')
            ->withCount(['shipments' => function($query) {
                $query->where('status', 'delivered');
            }])
            ->withSum(['shipments' => function($query) {
                $query->where('status', 'delivered');
            }], 'total_amount')
            ->paginate(20);

        return view('admin.reports.partners', compact('partners'));
    }

    /**
     * Export reports
     */
    public function export(Request $request, $type)
    {
        // This is a placeholder - you can implement actual export functionality
        // using Laravel Excel or similar package
        
        return redirect()->route('admin.reports')
            ->with('info', 'Export functionality coming soon!');
    }
}