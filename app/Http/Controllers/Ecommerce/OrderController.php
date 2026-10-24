<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\PickupRequest;
use App\Models\ReminderLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Seller creates a new order
     */
    public function create()
    {
    return view('seller.orders.create');
    }

    /**
     * Store new order from seller
     */
    public function store(Request $request)
{
    $seller = Auth::user();
    
    $request->validate([
        'customer_name' => 'required|string|max:255',
        'customer_phone' => 'required|string|max:20',
        'customer_email' => 'nullable|email|max:255',
        'items' => 'required|array|min:1',
        'items.*.name' => 'required|string',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
        'deliveries' => 'required|array|min:1',
        'deliveries.*.recipient_name' => 'required|string|max:255',
        'deliveries.*.recipient_phone' => 'required|string|max:20',
        'deliveries.*.address' => 'required|string',
        'payment_method' => 'required|in:prepaid,cod',
        'payment_status' => 'required_if:payment_method,prepaid|in:paid,pending',
        'delivery_date' => 'nullable|date',
        'delivery_time_slot' => 'nullable|string',
        'special_instructions' => 'nullable|string',
    ]);

    // Additional validation for COD
    if ($request->payment_method === 'cod') {
        $request->validate([
            'cod_amount' => 'required|numeric|min:0.01',
            'delivery_charge' => 'required|numeric|min:0',
            'cod_invoice' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
    }

    DB::beginTransaction();
    
    try {
        // Calculate totals
        $subtotal = 0;
        foreach ($request->items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        $tax = $subtotal * 0.13;
        $deliveryCharge = $request->payment_method === 'cod' ? $request->delivery_charge : (100 * count($request->deliveries));
        $shippingCost = $deliveryCharge;
        $total = $subtotal + $tax + $shippingCost;

        // Handle COD invoice upload
        $invoicePath = null;
        if ($request->hasFile('cod_invoice')) {
            $invoicePath = $request->file('cod_invoice')->store('cod-invoices', 'public');
        }

        // Create order
        $orderData = [
            'order_number' => Order::generateOrderNumber(),
            'seller_id' => $seller->id,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'shipping_address' => $request->deliveries[0]['address'],
            'delivery_type' => count($request->deliveries) > 1 ? 'multiple' : 'single',
            'delivery_count' => count($request->deliveries),
            'delivery_data' => $request->deliveries,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping_cost' => $shippingCost,
            'total_amount' => $total,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'payment_status' => $request->payment_method === 'cod' ? 'pending' : $request->payment_status,
            'order_date' => now(),
            'delivery_date' => $request->delivery_date,
            'delivery_time_slot' => $request->delivery_time_slot,
            'special_instructions' => $request->special_instructions,
        ];

        // COD specific fields
        if ($request->payment_method === 'cod') {
            $orderData['cod_amount'] = $request->cod_amount;
            $orderData['cod_invoice_file'] = $invoicePath;
            $orderData['delivery_charge'] = $request->delivery_charge;
            $orderData['cod_status'] = 'pending';
            $orderData['settlement_status'] = 'pending';
            
            // Calculate amounts
            $sellerAmount = $request->cod_amount;
            $riderAmount = $request->delivery_charge;
            $marginAmount = $riderAmount * 0.10; // Admin margin (10% of delivery charge)
            
            $orderData['seller_amount'] = $sellerAmount;
            $orderData['rider_amount'] = $riderAmount;
            $orderData['admin_margin'] = $marginAmount;
            $orderData['margin_amount'] = $marginAmount;
        }

        $order = Order::create($orderData);

        // Create order items
        foreach ($request->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
            ]);
        }

        // Create deliveries
        foreach ($request->deliveries as $index => $deliveryData) {
            Delivery::create([
                'order_id' => $order->id,
                'delivery_number' => 'DLV-' . $order->id . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'recipient_name' => $deliveryData['recipient_name'],
                'recipient_phone' => $deliveryData['recipient_phone'],
                'address' => $deliveryData['address'],
                'latitude' => $deliveryData['latitude'] ?? null,
                'longitude' => $deliveryData['longitude'] ?? null,
                'address_type' => $deliveryData['address_type'] ?? 'home',
                'landmark' => $deliveryData['landmark'] ?? null,
                'instructions' => $deliveryData['instructions'] ?? null,
                'status' => 'pending',
                'tracking_number' => Delivery::generateTrackingNumber(),
                'delivery_fee' => 100,
            ]);
        }

        // Create COD settlement record
        if ($request->payment_method === 'cod') {
            CODSettlement::create([
                'order_id' => $order->id,
                'seller_id' => $seller->id,
                'cod_amount' => $request->cod_amount,
                'delivery_charge' => $request->delivery_charge,
                'admin_margin' => $marginAmount,
                'seller_amount' => $sellerAmount,
                'rider_amount' => $riderAmount,
                'margin_amount' => $marginAmount,
                'settlement_status' => 'pending',
                'invoice_file' => $invoicePath,
            ]);
        }

        DB::commit();

        // Notify admins and riders
        $this->notifyAdminsAboutNewOrder($order);
        $this->notifyRidersAboutNewOrder($order);

        return redirect()->route('seller.orders.show', $order->id)
            ->with('success', 'Order created successfully with ' . count($request->deliveries) . ' deliveries!');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Order creation failed: ' . $e->getMessage());
        return redirect()->back()
            ->with('error', 'Failed to create order: ' . $e->getMessage())
            ->withInput();
    }
}



    /**
     * Notify admins about new order
     */
    private function notifyAdminsAboutNewOrder($order)
    {
        $admins = User::whereIn('user_type', ['admin', 'super_admin', 'domestic_admin'])->get();
        
        $message = "🆕 NEW E-COMMERCE ORDER\n\n";
        $message .= "Order #: {$order->order_number}\n";
        $message .= "Customer: {$order->customer_name}\n";
        $message .= "Phone: {$order->customer_phone}\n";
        $message .= "Address: {$order->shipping_address}\n";
        $message .= "Amount: Rs. {$order->total_amount}\n";
        $message .= "Status: Waiting for rider assignment\n\n";
        $message .= "Track: " . route('domestic.ecommerce.orders.show', $order->id);

        foreach ($admins as $admin) {
            ReminderLog::create([
                'pickup_request_id' => null,
                'reminder_id' => null,
                'reminder_type' => 'new_order_alert',
                'sent_to' => $admin->email,
                'message' => $message,
                'channel' => 'database',
                'status' => 'sent',
                'sent_at' => now(),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]
            ]);
        }
    }

    /**
     * Notify available riders about new order (Uber-style)
     */
    private function notifyRidersAboutNewOrder($order)
    {
        // Get available riders (online and active)
        $riders = User::where('user_type', 'rider')
            ->where('verification_status', 'approved')
            ->where('is_online', true)
            ->where('is_available', true)
            ->get();

        foreach ($riders as $rider) {
            // Create a notification for each rider
            ReminderLog::create([
                'pickup_request_id' => null,
                'reminder_id' => null,
                'reminder_type' => 'rider_order_alert',
                'sent_to' => $rider->email,
                'message' => "📦 New delivery order available!\n\nOrder #: {$order->order_number}\nCustomer: {$order->customer_name}\nDistance: " . ($order->distance ? $order->distance . ' km' : 'N/A') . "\nAmount: Rs. {$order->total_amount}\n\nReview available orders: " . route('rider.orders.available'),
                'channel' => 'database',
                'status' => 'sent',
                'sent_at' => now(),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'rider_id' => $rider->id,
                ]
            ]);
        }
    }

    private function logOrderEvent($order, $event, $description)
    {
        ReminderLog::create([
            'pickup_request_id' => null,
            'reminder_id' => null,
            'reminder_type' => 'order_event',
            'sent_to' => 'system',
            'message' => "Order #{$order->order_number}: {$description}",
            'channel' => 'database',
            'status' => 'sent',
            'sent_at' => now(),
            'metadata' => [
                'order_id' => $order->id,
                'event' => $event,
            ]
        ]);
    }
}
