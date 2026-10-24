<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\OrderTrackingLocation;
use App\Models\ReminderLog;
use App\Models\RiderDeposit;
use App\Models\User;
use App\Services\TrackingNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * List available orders (Uber-style)
     */
    public function availableOrders()
{
    $rider = Auth::user();
    
    if ($rider->user_type !== 'rider') {
        abort(403, 'Unauthorized. Rider only area.');
    }

    // Get pending orders without rider assigned
    $orders = Order::where('status', 'pending')
        ->whereNull('rider_id')
        ->whereDoesntHave('delivery', function($query) {
            $query->whereNotNull('rider_id');
        })
        ->orderBy('created_at', 'asc')
        ->get();

    // Calculate distance for each order
    if ($rider->current_latitude && $rider->current_longitude) {
        foreach ($orders as $order) {
            if ($order->delivery_latitude && $order->delivery_longitude) {
                $order->distance = $this->calculateDistance(
                    $rider->current_latitude,
                    $rider->current_longitude,
                    $order->delivery_latitude,
                    $order->delivery_longitude
                );
            }
        }
    }

    return view('rider.orders.available', compact('orders', 'rider'));
}



    /**
     * Accept an order (Uber-style)
     */
    public function accept($id)
{
    $rider = Auth::user();
    
    if ($rider->user_type !== 'rider') {
        abort(403, 'Unauthorized. Rider only area.');
    }

    $order = Order::findOrFail($id);
    
    // Check if order is still available
    if ($order->status !== 'pending' || $order->rider_id !== null) {
        return redirect()->back()
            ->with('error', 'This order is no longer available.');
    }

    // Check if rider can accept (max 3 active orders)
    $activeOrders = Order::where('rider_id', $rider->id)
        ->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])
        ->count();

    if ($activeOrders >= 3) {
        return redirect()->back()
            ->with('error', 'You have reached the maximum active orders (3).');
    }

    // =============================================
    // COD DEPOSIT CHECK
    // =============================================
    if ($order->payment_method === 'cod') {
        $depositBalance = $rider->rider_deposit_balance ?? 0;
        $codAmount = $order->cod_amount ?? 0;

        if ($depositBalance < $codAmount) {
            return redirect()->back()
                ->with('error', "Insufficient deposit balance. Required: Rs. {$codAmount}, Available: Rs. {$depositBalance}. Please deposit funds to accept COD orders.");
        }
    }

    DB::beginTransaction();

    try {
        // Serialize acceptance so two riders cannot claim the same order.
        $order = Order::whereKey($id)->lockForUpdate()->firstOrFail();
        $rider = User::whereKey($rider->id)->lockForUpdate()->firstOrFail();

        if ($order->status !== 'pending' || $order->rider_id !== null) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'This order is no longer available.');
        }

        $activeOrders = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])
            ->count();

        if ($activeOrders >= 3) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'You have reached the maximum active orders (3).');
        }

        if ($order->payment_method === 'cod' && ($rider->rider_deposit_balance ?? 0) < ($order->cod_amount ?? 0)) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Your deposit balance is no longer sufficient for this COD order.');
        }

        // If COD, hold deposit
        if ($order->payment_method === 'cod') {
            $codAmount = $order->cod_amount ?? 0;
            $rider->rider_deposit_balance -= $codAmount;
            $rider->save();

            // Create deposit hold record
            RiderDeposit::create([
                'rider_id' => $rider->id,
                'amount' => -$codAmount,
                'balance' => $rider->rider_deposit_balance,
                'type' => 'settlement',
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'description' => "Hold deposit for COD Order #{$order->order_number}",
                'status' => 'pending',
                'metadata' => [
                    'order_id' => $order->id,
                    'cod_amount' => $codAmount,
                    'action' => 'hold',
                ]
            ]);
        }

        // Assign order to rider
        $order->update([
            'rider_id' => $rider->id,
            'status' => 'assigned',
            'rider_assigned_at' => now(),
            'rider_acceptance_time' => now(),
        ]);

        // Create delivery record
        $delivery = Delivery::create([
            'order_id' => $order->id,
            'rider_id' => $rider->id,
            'recipient_name' => $order->customer_name,
            'recipient_phone' => $order->customer_phone,
            'address' => $order->shipping_address,
            'latitude' => $order->delivery_latitude,
            'longitude' => $order->delivery_longitude,
            'status' => 'assigned',
            'assigned_at' => now(),
            'delivery_fee' => $order->delivery_charge ?? 100,
            'tracking_number' => $this->generateTrackingNumber(),
        ]);

        DB::commit();

        $message = "Order accepted successfully!";
        if ($order->payment_method === 'cod') {
            $message .= " Deposit of Rs. {$codAmount} has been held.";
        }

        return redirect()->route('rider.orders.my')
            ->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        report($e);

        return redirect()->back()
            ->with('error', 'Failed to accept the order. Please try again.');
    }
}

    /**
     * Reject an order
     */
    public function reject($id)
    {
        $rider = Auth::user();
        
        $order = Order::findOrFail($id);
        
        // Log rejection
        ReminderLog::create([
            'pickup_request_id' => null,
            'reminder_id' => null,
            'reminder_type' => 'order_rejected',
            'sent_to' => 'system',
            'message' => "Rider {$rider->name} rejected order #{$order->order_number}",
            'channel' => 'database',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return redirect()->route('rider.orders.available')
            ->with('info', 'Order rejected. Showing next available order.');
    }

    /**
     * List rider's active orders
     */
    public function myOrders()
    {
        $rider = Auth::user();
        
        if ($rider->user_type !== 'rider') {
            abort(403, 'Unauthorized. Rider only area.');
        }

        $orders = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])
            ->orderBy('created_at', 'asc')
            ->get();

        $history = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['delivered', 'cancelled', 'failed'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('rider.orders.my', compact('orders', 'history', 'rider'));
    }

    /**
     * Mark order as picked up
     */
    public function markPickedUp($id)
    {
        $rider = Auth::user();
        
        $order = Order::where('rider_id', $rider->id)
            ->where('status', 'assigned')
            ->findOrFail($id);

        $order->update([
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ]);

        // Update delivery status
        $delivery = Delivery::where('order_id', $order->id)->first();
        if ($delivery) {
            $delivery->update([
                'status' => 'picked_up',
                'picked_up_at' => now(),
            ]);
        }

        $this->logOrderEvent($order, 'picked_up', "Rider picked up the order");
        $this->notifyCustomer($order, 'Your order has been picked up by the rider.');

        return redirect()->route('rider.orders.my')
            ->with('success', 'Order marked as picked up!');
    }

    /**
     * Mark order as in transit
     */
    public function markInTransit($id)
    {
        $rider = Auth::user();
        
        $order = Order::where('rider_id', $rider->id)
            ->where('status', 'picked_up')
            ->findOrFail($id);

        $order->update([
            'status' => 'in_transit',
            'in_transit_at' => now(),
        ]);

        $delivery = Delivery::where('order_id', $order->id)->first();
        if ($delivery) {
            $delivery->update([
                'status' => 'in_transit',
            ]);
        }

        $this->logOrderEvent($order, 'in_transit', "Order is in transit");
        $this->notifyCustomer($order, 'Your order is on the way!');

        return redirect()->route('rider.orders.my')
            ->with('success', 'Order is in transit!');
    }

    /**
     * Mark order as out for delivery
     */
    public function markOutForDelivery($id)
    {
        $rider = Auth::user();
        
        $order = Order::where('rider_id', $rider->id)
            ->where('status', 'in_transit')
            ->findOrFail($id);

        $order->update([
            'status' => 'out_for_delivery',
            'out_for_delivery_at' => now(),
        ]);

        $delivery = Delivery::where('order_id', $order->id)->first();
        if ($delivery) {
            $delivery->update([
                'status' => 'out_for_delivery',
            ]);
        }

        $this->logOrderEvent($order, 'out_for_delivery', "Order is out for delivery");
        $this->notifyCustomer($order, 'Your order is out for delivery! Rider is on the way.');

        return redirect()->route('rider.orders.my')
            ->with('success', 'Order is out for delivery!');
    }

    /**
     * Mark order as delivered
     */
    public function markDelivered(Request $request, $id)
    {
        $rider = Auth::user();
        
        $order = Order::where('rider_id', $rider->id)
            ->where('status', 'out_for_delivery')
            ->findOrFail($id);

        $request->validate([
            'signature' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $order->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);

            $delivery = Delivery::where('order_id', $order->id)->first();
            if ($delivery) {
                $delivery->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                    'metadata' => array_merge($delivery->metadata ?? [], [
                        'recipient_name_confirmed' => $request->signature,
                        'delivery_notes' => $request->notes,
                    ]),
                ]);
            }

            // Update rider stats
            $rider->increment('total_deliveries');
            $rider->total_earnings += $order->shipping_cost ?? 100;
            $rider->save();

            // Add to wallet
            $wallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $rider->id],
                ['balance' => 0, 'pending_balance' => 0]
            );
            $wallet->addBalance(
                $order->shipping_cost ?? 100,
                "Delivery fee for Order #{$order->order_number}",
                'delivery'
            );

            DB::commit();

            $this->logOrderEvent($order, 'delivered', "Order delivered successfully");
            $this->notifyDeliveryComplete($order, $rider);

            return redirect()->route('rider.orders.my')
                ->with('success', '🎉 Order delivered successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to mark delivery: ' . $e->getMessage());
        }
    }

    /**
     * Track order (customer view)
     */
    public function trackOrder($trackingNumber)
{
    $order = Order::where('tracking_number', $trackingNumber)
        ->where('rider_id', Auth::id())
        ->with(['rider'])
        ->firstOrFail();

    // Reuse the privacy-aware tracker. It polls actual GPS records written by
    // updateLocation; the previous screen animated a simulated rider position.
    return view('tracking.order', ['order' => $order, 'canViewLive' => true]);
}


    /**
     * Get order tracking for live map
     */
    public function getTracking($id)
    {
        $order = Order::where('rider_id', Auth::id())->findOrFail($id);
        
        $locations = OrderTrackingLocation::where('order_id', $order->id)
            ->orderBy('timestamp', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'order' => $order,
            'locations' => $locations,
            'rider_location' => $order->rider ? [
                'latitude' => $order->rider->current_latitude,
                'longitude' => $order->rider->current_longitude,
                'last_update' => $order->rider->last_location_update,
            ] : null,
        ]);
    }

    public function updateLocation(Request $request)
    {
        $rider = $request->user();
        abort_unless($rider && $rider->user_type === 'rider', 403);

        $validated = $request->validate([
            'order_id' => ['required', 'integer'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'speed' => ['nullable', 'numeric', 'min:0', 'max:150'],
            'bearing' => ['nullable', 'numeric', 'between:0,360'],
            'altitude' => ['nullable', 'numeric', 'between:-500,10000'],
        ]);

        $order = Order::whereKey($validated['order_id'])
            ->where('rider_id', $rider->id)
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])
            ->firstOrFail();

        $location = DB::transaction(function () use ($validated, $order, $rider) {
            $location = OrderTrackingLocation::create([
                ...$validated,
                'order_id' => $order->id,
                'rider_id' => $rider->id,
                'location_type' => 'gps',
                'status' => $order->status,
                'timestamp' => now(),
            ]);

            $rider->forceFill([
                'current_latitude' => $validated['latitude'],
                'current_longitude' => $validated['longitude'],
                'last_location_update' => now(),
            ])->save();

            return $location;
        });

        return response()->json([
            'message' => 'Location updated.',
            'location' => $location->only(['latitude', 'longitude', 'accuracy', 'timestamp']),
        ]);
    }

    /**
     * Calculate distance between two points
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return round($earthRadius * $c, 2);
    }

    private function generateTrackingNumber()
    {
        return app(TrackingNumberService::class)->generate('ecommerce');
    }

    private function notifyOrderAccepted($order, $rider)
    {
        ReminderLog::create([
            'pickup_request_id' => null,
            'reminder_id' => null,
            'reminder_type' => 'order_accepted',
            'sent_to' => 'system',
            'message' => "Order #{$order->order_number} accepted by rider {$rider->name}",
            'channel' => 'database',
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    private function notifyCustomer($order, $message)
    {
        // In production, this would send SMS/Email
        ReminderLog::create([
            'pickup_request_id' => null,
            'reminder_id' => null,
            'reminder_type' => 'customer_notification',
            'sent_to' => $order->customer_phone ?? 'N/A',
            'message' => $message,
            'channel' => 'database',
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    private function notifyDeliveryComplete($order, $rider)
    {
        ReminderLog::create([
            'pickup_request_id' => null,
            'reminder_id' => null,
            'reminder_type' => 'delivery_complete',
            'sent_to' => 'system',
            'message' => "Order #{$order->order_number} delivered by rider {$rider->name}",
            'channel' => 'database',
            'status' => 'sent',
            'sent_at' => now(),
        ]);
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
        ]);
    }
}
