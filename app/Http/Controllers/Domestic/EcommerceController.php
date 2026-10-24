<?php

namespace App\Http\Controllers\Domestic;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\PickupRequest;
use App\Models\Shipment;
use App\Models\DeliveryZone;
use App\Models\DomesticRate;
use App\Models\ReminderLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EcommerceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $allowedTypes = ['domestic_admin', 'super_admin', 'admin', 'staff'];
            if (!in_array($user->user_type, $allowedTypes)) {
                abort(403, 'Unauthorized access. Domestic admin only.');
            }
            return $next($request);
        });
    }

    /**
     * E-commerce Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'total_sellers' => User::where('user_type', 'seller')->count(),
            'active_sellers' => User::where('user_type', 'seller')->where('verification_status', 'approved')->count(),
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
        ];

        $recentOrders = Order::with(['seller', 'client'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $topProducts = Product::withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get();

        return view('domestic.ecommerce.dashboard', compact('stats', 'recentOrders', 'topProducts'));
    }

    /**
     * List all orders
     */
    public function orders(Request $request)
    {
        $query = Order::with(['seller', 'client']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_phone', 'LIKE', "%{$search}%");
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        $sellers = User::where('user_type', 'seller')->get();

        return view('domestic.ecommerce.orders', compact('orders', 'sellers'));
    }

    /**
     * Show order details
     */
    public function showOrder($id)
    {
        $order = Order::with(['seller', 'client', 'items.product'])
            ->findOrFail($id);

        // Get related pickup request if exists
        $pickupRequest = PickupRequest::where('order_id', $order->id)->first();
        
        // Get related shipment if exists
        $shipment = null;
        if ($pickupRequest && $pickupRequest->shipment_id) {
            $shipment = Shipment::find($pickupRequest->shipment_id);
        }

        return view('domestic.ecommerce.order-details', compact('order', 'pickupRequest', 'shipment'));
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        $order->update([
            'status' => $newStatus,
            'admin_notes' => $request->notes,
        ]);

        // If order is completed, create pickup request
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $this->createPickupRequest($order);
        }

        // Log the status change
        ReminderLog::create([
            'pickup_request_id' => null,
            'reminder_id' => null,
            'reminder_type' => 'order_status',
            'sent_to' => $order->seller->email ?? 'admin',
            'message' => "Order #{$order->order_number} status changed from {$oldStatus} to {$newStatus}",
            'channel' => 'database',
            'status' => 'sent',
            'sent_at' => now(),
            'metadata' => [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]
        ]);

        return redirect()->route('domestic.ecommerce.orders.show', $id)
            ->with('success', 'Order status updated successfully!');
    }

    /**
     * Create pickup request from order
     */
    private function createPickupRequest($order)
    {
        // Check if pickup request already exists
        $existing = PickupRequest::where('order_id', $order->id)->first();
        if ($existing) {
            return $existing;
        }

        // Generate tracking number
        $trackingNumber = app(\App\Services\TrackingNumberService::class)->ecommerce();

        $pickupRequest = PickupRequest::create([
            'order_id' => $order->id,
            'seller_id' => $order->seller_id,
            'customer_name' => $order->customer_name ?? $order->client->name ?? 'N/A',
            'customer_phone' => $order->customer_phone ?? $order->client->phone ?? 'N/A',
            'pickup_address' => $order->seller->business_address ?? 'N/A',
            'delivery_address' => $order->shipping_address ?? 'N/A',
            'service_tier' => 'ecommerce',
            'tracking_number' => $trackingNumber,
            'status' => 'pending',
            'scheduled_pickup_time' => now()->addHours(2),
            'order_reference' => $order->order_number,
            'total_amount' => $order->total_amount,
        ]);

        // Notify admin
        ReminderLog::create([
            'pickup_request_id' => $pickupRequest->id,
            'reminder_id' => null,
            'reminder_type' => 'pickup_created',
            'sent_to' => 'admin',
            'message' => "New pickup request created for order #{$order->order_number}",
            'channel' => 'database',
            'status' => 'sent',
            'sent_at' => now(),
            'metadata' => [
                'order_id' => $order->id,
                'tracking_number' => $trackingNumber,
            ]
        ]);

        return $pickupRequest;
    }

    /**
     * List all sellers
     */
    public function sellers(Request $request)
    {
        $query = User::where('user_type', 'seller');

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('business_name', 'LIKE', "%{$search}%");
            });
        }

        $sellers = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('domestic.ecommerce.sellers', compact('sellers'));
    }

    /**
     * Show seller details
     */
    public function showSeller($id)
    {
        $seller = User::where('user_type', 'seller')->findOrFail($id);
        
        $stats = [
            'total_products' => Product::where('user_id', $id)->count(),
            'active_products' => Product::where('user_id', $id)->where('is_active', true)->count(),
            'total_orders' => Order::where('seller_id', $id)->count(),
            'pending_orders' => Order::where('seller_id', $id)->where('status', 'pending')->count(),
            'completed_orders' => Order::where('seller_id', $id)->where('status', 'completed')->count(),
            'total_revenue' => Order::where('seller_id', $id)->where('status', 'completed')->sum('total_amount'),
        ];

        $recentOrders = Order::where('seller_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('domestic.ecommerce.seller-details', compact('seller', 'stats', 'recentOrders'));
    }

    /**
     * List all products
     */
    public function products(Request $request)
    {
        $query = Product::with('user');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        if ($request->filled('seller_id')) {
            $query->where('user_id', $request->seller_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(20);
        $sellers = User::where('user_type', 'seller')->get();

        return view('domestic.ecommerce.products', compact('products', 'sellers'));
    }

    /**
     * Show product details
     */
    public function showProduct($id)
    {
        $product = Product::with('user')->findOrFail($id);
        
        $orderCount = Order::whereHas('items', function($query) use ($id) {
            $query->where('product_id', $id);
        })->count();

        return view('domestic.ecommerce.product-details', compact('product', 'orderCount'));
    }

    /**
     * Bulk update order status
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
        ]);

        $count = Order::whereIn('id', $request->order_ids)
            ->update(['status' => $request->status]);

        return redirect()->route('domestic.ecommerce.orders')
            ->with('success', "{$count} orders updated successfully!");
    }

    /**
     * Export orders report
     */
    public function exportOrders(Request $request)
    {
        $query = Order::with(['seller', 'client']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $filename = "orders_report_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://temp', 'w+');

        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'Order ID', 'Order Number', 'Seller', 'Customer', 'Phone',
            'Items Count', 'Total Amount', 'Status', 'Payment Method',
            'Payment Status', 'Created At', 'Completed At'
        ]);

        foreach ($orders as $order) {
            fputcsv($handle, [
                $order->id,
                $order->order_number,
                $order->seller->name ?? 'N/A',
                $order->customer_name ?? $order->client->name ?? 'N/A',
                $order->customer_phone ?? $order->client->phone ?? 'N/A',
                $order->items->count(),
                number_format($order->total_amount, 2),
                $order->status,
                $order->payment_method ?? 'N/A',
                $order->payment_status ?? 'N/A',
                $order->created_at->format('Y-m-d H:i:s'),
                $order->completed_at ? $order->completed_at->format('Y-m-d H:i:s') : 'N/A',
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

    /**
     * Get revenue analytics
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', 'month');

        $revenueData = $this->getRevenueData($period);
        $orderData = $this->getOrderData($period);

        return view('domestic.ecommerce.analytics', compact('revenueData', 'orderData', 'period'));
    }

    private function getRevenueData($period)
    {
        switch ($period) {
            case 'week':
                $start = Carbon::now()->startOfWeek();
                $groupBy = 'day';
                break;
            case 'month':
                $start = Carbon::now()->startOfMonth();
                $groupBy = 'day';
                break;
            case 'year':
                $start = Carbon::now()->startOfYear();
                $groupBy = 'month';
                break;
            default:
                $start = Carbon::now()->startOfMonth();
                $groupBy = 'day';
        }

        $revenue = Order::where('status', 'completed')
            ->where('created_at', '>=', $start)
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as date"), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return $revenue;
    }

    private function getOrderData($period)
    {
        $start = Carbon::now()->subDays(30);

        $orders = Order::where('created_at', '>=', $start)
            ->select(DB::raw('status'), DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return $orders;
    }
}
