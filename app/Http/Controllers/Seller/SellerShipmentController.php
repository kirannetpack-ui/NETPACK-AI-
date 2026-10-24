<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellerShipmentController extends Controller
{
    /**
     * Display a listing of shipments.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $sellerId = Auth::id();
        
        $query = Shipment::where('seller_id', $sellerId);
        
        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('tracking_number', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('reference_number', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('carrier', 'LIKE', '%' . $request->search . '%');
            });
        }
        
        // Date range filter
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        $shipments = $query->with(['order', 'tracking', 'payment'])
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);
        
        // Get shipment statistics
        $stats = [
            'total' => Shipment::where('seller_id', $sellerId)->count(),
            'pending' => Shipment::where('seller_id', $sellerId)->where('status', 'pending')->count(),
            'processing' => Shipment::where('seller_id', $sellerId)->where('status', 'processing')->count(),
            'shipped' => Shipment::where('seller_id', $sellerId)->where('status', 'shipped')->count(),
            'delivered' => Shipment::where('seller_id', $sellerId)->where('status', 'delivered')->count(),
            'cancelled' => Shipment::where('seller_id', $sellerId)->where('status', 'cancelled')->count(),
        ];
        
        return view('seller.shipments.index', compact('shipments', 'stats'));
    }

    /**
     * Display the specified shipment.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $sellerId = Auth::id();
        
        $shipment = Shipment::where(function($q) use ($sellerId) {
            $q->where('customer_id', $sellerId)
              ->orWhere('seller_id', $sellerId)
              ->orWhereHas('order', function($query) use ($sellerId) {
                  $query->where('seller_id', $sellerId);
              });
        })->findOrFail($id);
        
        return view('seller.shipments.show', compact('shipment'));
    }

    /**
     * Show form to create a new shipment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request)
    {
        return redirect()->route('shipments.create', $request->all());
    }

    /**
     * Store a newly created shipment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $sellerId = Auth::id();
        
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'receiver_address' => 'required|string',
            'receiver_city' => 'required|string|max:100',
            'receiver_country' => 'required|string|max:100',
            'weight' => 'required|numeric|min:0.01',
            'service_type' => 'required|in:standard,express,priority',
            'package_type' => 'required|in:parcel,box,envelope,other',
        ]);

        DB::beginTransaction();
        
        try {
            $order = Order::where('seller_id', $sellerId)->findOrFail($request->order_id);
            
            // Generate tracking number
            $trackingNumber = app(\App\Services\TrackingNumberService::class)->ecommerce();
            
            // Create shipment
            $shipment = Shipment::create([
                'order_id' => $order->id,
                'seller_id' => $sellerId,
                'tracking_number' => $trackingNumber,
                'receiver_name' => $request->receiver_name,
                'receiver_phone' => $request->receiver_phone,
                'receiver_address' => $request->receiver_address,
                'receiver_city' => $request->receiver_city,
                'receiver_country' => $request->receiver_country,
                'weight' => $request->weight,
                'service_type' => $request->service_type,
                'package_type' => $request->package_type,
                'status' => 'pending',
                'shipping_cost' => $this->calculateShippingCost($request->weight, $request->service_type),
            ]);

            DB::commit();

            return redirect()->route('seller.shipments.show', $shipment->id)
                ->with('success', 'Shipment created successfully! Tracking number: ' . $trackingNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create shipment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update shipment tracking information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTracking(Request $request, $id)
    {
        $sellerId = Auth::id();
        
        $shipment = Shipment::whereHas('order', function($query) use ($sellerId) {
            $query->where('seller_id', $sellerId);
        })->findOrFail($id);
        
        $request->validate([
            'tracking_number' => 'required|string|max:255',
        ]);
        
        $shipment->update([
            'tracking_number' => $request->tracking_number,
        ]);
        
        return redirect()->route('seller.shipments.show', $shipment->id)
            ->with('success', 'Tracking number updated successfully!');
    }

    /**
     * Update shipment status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $shipment = Shipment::where('seller_id', Auth::id())->findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);
        
        $oldStatus = $shipment->status;
        $shipment->status = $request->status;
        
        // Update timestamps
        switch ($request->status) {
            case 'shipped':
                $shipment->shipped_at = now();
                break;
            case 'delivered':
                $shipment->delivered_at = now();
                break;
            case 'cancelled':
                $shipment->cancelled_at = now();
                break;
        }
        
        $shipment->save();
        
        // Update order status if needed
        $order = Order::find($shipment->order_id);
        if ($order) {
            switch ($request->status) {
                case 'shipped':
                    $order->status = 'shipped';
                    $order->shipped_at = now();
                    break;
                case 'delivered':
                    $order->status = 'completed';
                    $order->delivered_at = now();
                    break;
                case 'cancelled':
                    $order->status = 'cancelled';
                    $order->cancelled_at = now();
                    break;
            }
            $order->save();
        }
        
        return redirect()->back()->with('success', 'Shipment status updated from ' . ucfirst($oldStatus) . ' to ' . ucfirst($request->status) . ' successfully!');
    }

    /**
     * Generate shipping label.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function printLabel($id)
    {
        $sellerId = Auth::id();
        
        $shipment = Shipment::whereHas('order', function($query) use ($sellerId) {
            $query->where('seller_id', $sellerId);
        })->findOrFail($id);
        
        return view('seller.shipments.label', compact('shipment'));
    }

    /**
     * Generate airway bill.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function airwayBill($id)
    {
        $sellerId = Auth::id();
        
        $shipment = Shipment::whereHas('order', function($query) use ($sellerId) {
            $query->where('seller_id', $sellerId);
        })->with(['order', 'order.customer'])
           ->findOrFail($id);
        
        return view('seller.shipments.airway-bill', compact('shipment'));
    }

    /**
     * Cancel shipment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel($id)
    {
        $sellerId = Auth::id();
        
        $shipment = Shipment::whereHas('order', function($query) use ($sellerId) {
            $query->where('seller_id', $sellerId);
        })->findOrFail($id);
        
        if (!in_array($shipment->status, ['pending', 'processing'])) {
            return redirect()->back()->with('error', 'This shipment cannot be cancelled. Current status: ' . ucfirst($shipment->status));
        }
        
        $shipment->status = 'cancelled';
        $shipment->cancelled_at = now();
        $shipment->save();
        
        // Update order status
        $order = Order::find($shipment->order_id);
        if ($order && $order->status !== 'completed') {
            $order->status = 'pending';
            $order->tracking_number = null;
            $order->save();
        }
        
        return redirect()->route('seller.shipments.index')
                         ->with('success', 'Shipment cancelled successfully!');
    }

    /**
     * Get shipment statistics via AJAX.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        $sellerId = Auth::id();
        
        $stats = [
            'total' => Shipment::where('seller_id', $sellerId)->count(),
            'pending' => Shipment::where('seller_id', $sellerId)->where('status', 'pending')->count(),
            'processing' => Shipment::where('seller_id', $sellerId)->where('status', 'processing')->count(),
            'shipped' => Shipment::where('seller_id', $sellerId)->where('status', 'shipped')->count(),
            'delivered' => Shipment::where('seller_id', $sellerId)->where('status', 'delivered')->count(),
            'cancelled' => Shipment::where('seller_id', $sellerId)->where('status', 'cancelled')->count(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Calculate shipping cost.
     *
     * @param  float  $weight
     * @param  string  $serviceType
     * @return float
     */
    private function calculateShippingCost($weight, $serviceType)
    {
        $rates = [
            'standard' => 50,
            'express' => 100,
            'priority' => 200,
        ];
        
        $baseRate = $rates[$serviceType] ?? 50;
        $weightCharge = $weight * 20; // Rs. 20 per kg
        
        return $baseRate + $weightCharge;
    }

    /**
     * Generate unique reference number.
     *
     * @return string
     */
    private function generateReferenceNumber()
    {
        $prefix = 'SHP';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        
        return $prefix . '-' . $date . '-' . $random;
    }

    /**
     * Track shipment with carrier API.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function track($id)
    {
        $shipment = Shipment::where('seller_id', Auth::id())->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'tracking_number' => $shipment->tracking_number,
                'carrier' => $shipment->carrier,
                'status' => $shipment->status,
                'estimated_delivery' => $shipment->estimated_delivery,
                'tracking_url' => $this->getTrackingUrl($shipment->carrier, $shipment->tracking_number),
            ]
        ]);
    }

    /**
     * Get tracking URL for carrier.
     *
     * @param  string  $carrier
     * @param  string  $trackingNumber
     * @return string|null
     */
    private function getTrackingUrl($carrier, $trackingNumber)
    {
        $urls = [
            'dhl' => 'https://www.dhl.com/en/express/tracking.html?AWB=' . $trackingNumber,
            'fedex' => 'https://www.fedex.com/fedextrack/?trknbr=' . $trackingNumber,
            'ups' => 'https://www.ups.com/track?tracknum=' . $trackingNumber,
            'usps' => 'https://tools.usps.com/go/TrackConfirmAction?tRef=qt&tLc=1&tLabels=' . $trackingNumber,
            'aramex' => 'https://www.aramex.com/track/results?ShipmentNumber=' . $trackingNumber,
            'dpd' => 'https://www.dpd.com/tracking/?reference=' . $trackingNumber,
        ];
        
        $carrierKey = strtolower($carrier);
        
        return $urls[$carrierKey] ?? null;
    }
}
