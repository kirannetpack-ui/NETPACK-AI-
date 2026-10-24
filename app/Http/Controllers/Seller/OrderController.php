<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $sellerId = Auth::id();
        
        $query = Order::where('seller_id', $sellerId);
        
        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('customer_email', 'LIKE', '%' . $request->search . '%');
            });
        }
        
        // Date range filter
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        $orders = $query->with(['customer', 'product'])
                       ->orderBy('created_at', 'desc')
                       ->paginate(20);
        
        // Get order statistics
        $stats = [
            'total' => Order::where('seller_id', $sellerId)->count(),
            'pending' => Order::where('seller_id', $sellerId)->where('status', 'pending')->count(),
            'processing' => Order::where('seller_id', $sellerId)->where('status', 'processing')->count(),
            'shipped' => Order::where('seller_id', $sellerId)->where('status', 'shipped')->count(),
            'completed' => Order::where('seller_id', $sellerId)->where('status', 'completed')->count(),
            'cancelled' => Order::where('seller_id', $sellerId)->where('status', 'cancelled')->count(),
        ];
        
        return view('seller.orders.index', compact('orders', 'stats'));
    }

    /**
     * Display the specified order.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $order = Order::where('seller_id', Auth::id())
                     ->with(['customer', 'product', 'shipment', 'transactions'])
                     ->findOrFail($id);
        
        return view('seller.orders.show', compact('order'));
    }

    /**
     * Update order status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::where('seller_id', Auth::id())->findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled'
        ]);
        
        $oldStatus = $order->status;
        $order->status = $request->status;
        
        // Update timestamps based on status
        switch ($request->status) {
            case 'shipped':
                $order->shipped_at = now();
                break;
            case 'completed':
                $order->delivered_at = now();
                break;
            case 'cancelled':
                $order->cancelled_at = now();
                break;
        }
        
        $order->save();
        
        // You can add notification logic here
        // event(new OrderStatusUpdated($order, $oldStatus));
        
        return redirect()->back()->with('success', 'Order status updated successfully from ' . ucfirst($oldStatus) . ' to ' . ucfirst($request->status) . '!');
    }

    /**
     * Cancel the specified order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel($id)
    {
        $order = Order::where('seller_id', Auth::id())->findOrFail($id);
        
        if (!$order->canBeCancelled()) {
            return redirect()->back()->with('error', 'This order cannot be cancelled. Current status: ' . ucfirst($order->status));
        }
        
        $order->status = 'cancelled';
        $order->cancelled_at = now();
        $order->save();
        
        // Restore product stock if needed
        // $order->product->increment('stock_quantity', $order->quantity);
        
        return redirect()->back()->with('success', 'Order #' . $order->order_number . ' cancelled successfully!');
    }

    /**
     * Generate invoice for order.
     *
     * @param  int  $id
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function invoice($id)
    {
        $order = Order::where('seller_id', Auth::id())
                     ->with(['customer', 'product'])
                     ->findOrFail($id);
        
        // If you have DomPDF installed, uncomment this:
        // $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('seller.orders.invoice', compact('order'));
        // return $pdf->download('invoice-' . $order->order_number . '.pdf');
        
        return view('seller.orders.invoice', compact('order'));
    }

    /**
     * Get order statistics via AJAX.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        $sellerId = Auth::id();
        
        $stats = [
            'total' => Order::where('seller_id', $sellerId)->count(),
            'pending' => Order::where('seller_id', $sellerId)->where('status', 'pending')->count(),
            'processing' => Order::where('seller_id', $sellerId)->where('status', 'processing')->count(),
            'shipped' => Order::where('seller_id', $sellerId)->where('status', 'shipped')->count(),
            'completed' => Order::where('seller_id', $sellerId)->where('status', 'completed')->count(),
            'cancelled' => Order::where('seller_id', $sellerId)->where('status', 'cancelled')->count(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Bulk update order status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:pending,processing,shipped,completed,cancelled'
        ]);
        
        $sellerId = Auth::id();
        $orderIds = $request->order_ids;
        $status = $request->status;
        
        // Update all orders
        Order::where('seller_id', $sellerId)
             ->whereIn('id', $orderIds)
             ->update(['status' => $status]);
        
        return redirect()->back()->with('success', count($orderIds) . ' orders updated to ' . ucfirst($status) . ' successfully!');
    }

/**
 * Export orders as CSV
 */
public function export(Request $request)
{
    $sellerId = Auth::id();
    
    $query = Order::where('seller_id', $sellerId);
    
    // Apply filters if provided
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    if ($request->filled('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }
    
    if ($request->filled('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }
    
    $orders = $query->with(['items'])->orderBy('created_at', 'desc')->get();
    
    if ($orders->count() === 0) {
        return redirect()->route('seller.orders')
            ->with('error', 'No orders found to export.');
    }
    
    $filename = "orders_export_" . date('Y-m-d_H-i-s') . ".csv";
    $handle = fopen('php://temp', 'w+');
    
    // Add UTF-8 BOM for Excel compatibility
    fwrite($handle, "\xEF\xBB\xBF");
    
    // Headers
    fputcsv($handle, [
        'Order ID',
        'Order Number',
        'Customer Name',
        'Customer Phone',
        'Total Amount',
        'Status',
        'Payment Status',
        'Items Count',
        'Created At',
        'Delivered At',
    ]);
    
    // Data rows
    foreach ($orders as $order) {
        fputcsv($handle, [
            $order->id,
            $order->order_number,
            $order->customer_name,
            $order->customer_phone,
            number_format($order->total_amount, 2),
            $order->status,
            $order->payment_status,
            $order->items->count(),
            $order->created_at->format('Y-m-d H:i:s'),
            $order->delivered_at ? $order->delivered_at->format('Y-m-d H:i:s') : 'N/A',
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