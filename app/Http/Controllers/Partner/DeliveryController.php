<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use App\Models\Shipment;
use App\Models\User;
use App\Models\ReminderLog;
use App\Models\DeliveryReminder;
use App\Notifications\ShipmentMilestoneNotification;
use App\Services\ReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeliveryController extends Controller
{
    protected $reminderService;
    
    public function __construct(ReminderService $reminderService)
    {
        $this->middleware('auth');
        $this->reminderService = $reminderService;
    }

    /**
     * Get partner ID from authenticated user
     */
    private function getPartnerId()
    {
        $user = Auth::user();
        
        if ($user->user_type === 'partner') {
            return $user->id;
        }
        
        if ($user->user_type === 'partner_staff' && $user->partner_id) {
            return $user->partner_id;
        }
        
        abort(403, 'Unauthorized: You are not a partner.');
    }

    /**
     * Display all deliveries for the partner
     * URL: GET /partner/deliveries
     */
    public function index()
    {
        $partnerId = $this->getPartnerId();
        
        $deliveries = PickupRequest::where('partner_user_id', $partnerId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $stats = [
            'total' => PickupRequest::where('partner_user_id', $partnerId)->count(),
            'pending' => PickupRequest::where('partner_user_id', $partnerId)->where('status', 'pending')->count(),
            'in_transit' => PickupRequest::where('partner_user_id', $partnerId)->whereIn('status', ['picked_up', 'in_transit'])->count(),
            'out_for_delivery' => PickupRequest::where('partner_user_id', $partnerId)->where('status', 'out_for_delivery')->count(),
            'delivered' => PickupRequest::where('partner_user_id', $partnerId)->where('status', 'delivered')->count(),
            'delayed' => PickupRequest::where('partner_user_id', $partnerId)->where('is_delayed', true)->count(),
        ];
        
        return view('partner.deliveries.index', compact('deliveries', 'stats'));
    }

    /**
     * Show single delivery details
     * URL: GET /partner/deliveries/{id}
     */
    public function show($id)
    {
        $partnerId = $this->getPartnerId();
        
        $delivery = PickupRequest::where('partner_user_id', $partnerId)
            ->where('id', $id)
            ->firstOrFail();
        
        $shipment = null;
        if ($delivery->shipment_id) {
            $shipment = Shipment::find($delivery->shipment_id);
        }
        
        $reminderLogs = ReminderLog::where('pickup_request_id', $delivery->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $trackingHistory = $shipment ? ($shipment->tracking_history ?? []) : [];
        
        return view('partner.deliveries.show', compact('delivery', 'shipment', 'reminderLogs', 'trackingHistory'));
    }

    /**
     * Show delay reporting form
     * URL: GET /partner/deliveries/{id}/report-delay
     */
    public function showDelayForm($id)
    {
        $partnerId = $this->getPartnerId();
        
        $delivery = PickupRequest::where('partner_user_id', $partnerId)
            ->where('id', $id)
            ->firstOrFail();
        
        if ($delivery->is_delayed) {
            return redirect()->route('partner.deliveries.show', $delivery)
                ->with('warning', 'Delay already reported for this delivery.');
        }
        
        if ($delivery->status === 'delivered') {
            return redirect()->route('partner.deliveries.show', $delivery)
                ->with('error', 'Cannot report delay for completed delivery.');
        }
        
        return view('partner.deliveries.report-delay', compact('delivery'));
    }

    /**
     * Report delay for a delivery
     * URL: POST /partner/deliveries/{id}/report-delay
     */
    public function reportDelay(Request $request, $id)
    {
        $partnerId = $this->getPartnerId();
        
        $delivery = PickupRequest::where('partner_user_id', $partnerId)
            ->where('id', $id)
            ->firstOrFail();
        
        $request->validate([
            'delay_reason' => 'required|string|min:10',
            'expected_resolution_time' => 'nullable|date'
        ]);
        
        $delivery->update([
            'is_delayed' => true,
            'delay_reason' => $request->delay_reason,
            'delay_reported_at' => now(),
            'expected_resolution_time' => $request->expected_resolution_time
        ]);
        
        // Update associated shipment if exists
        if ($delivery->shipment_id) {
            $shipment = Shipment::find($delivery->shipment_id);
            if ($shipment) {
                $history = $shipment->tracking_history ?? [];
                $history[] = [
                    'status' => 'delayed',
                    'status_label' => 'Delayed',
                    'description' => "Delay reported: " . $request->delay_reason,
                    'location' => 'Partner Hub',
                    'time' => now()->toDateTimeString(),
                    'reported_by' => Auth::user()->name,
                    'reported_by_role' => Auth::user()->user_type,
                ];
                $shipment->tracking_history = $history;
                $shipment->status = 'delayed';
                $shipment->save();
            }
        }
        
        // Notify admin and customer
        $this->notifyDelay($delivery);
        
        // Log the delay report
        ReminderLog::create([
            'pickup_request_id' => $delivery->id,
            'reminder_id' => null,
            'reminder_type' => 'delay_report',
            'sent_to' => 'system',
            'message' => "Delay reported: " . $request->delay_reason,
            'channel' => 'web'
        ]);
        
        return redirect()->route('partner.deliveries.show', $delivery)
            ->with('success', 'Delay reported successfully. Customer and admin have been notified.');
    }

    /**
     * Update delivery status
     * URL: POST /partner/deliveries/{id}/update-status
     */
    public function updateStatus(Request $request, $id)
    {
        $partnerId = $this->getPartnerId();
        
        $delivery = PickupRequest::where('partner_id', $partnerId)
            ->where('id', $id)
            ->firstOrFail();
        
        $request->validate([
            'status' => 'required|in:pending,picked_up,in_transit,out_for_delivery,delivered,failed_delivery',
            'status_note' => 'nullable|string',
            'location' => 'nullable|string',
        ]);
        
        $oldStatus = $delivery->status;
        $newStatus = $request->status;

        $allowed = [
            'pending' => ['picked_up', 'cancelled'],
            'assigned' => ['picked_up', 'cancelled'],
            'picked_up' => ['in_transit'],
            'in_transit' => ['out_for_delivery'],
            'out_for_delivery' => ['delivered', 'failed_delivery'],
            'failed_delivery' => ['out_for_delivery', 'cancelled'],
        ];
        if (! in_array($newStatus, $allowed[$oldStatus] ?? [], true)) {
            return back()->withErrors(['status' => "{$oldStatus} cannot move directly to {$newStatus}."]);
        }
        
        $updateData = [
            'status' => $newStatus,
            'status_notes' => $request->status_note,
        ];
        
        if ($newStatus === 'picked_up') {
            $updateData['picked_up_at'] = now();
        } elseif ($newStatus === 'in_transit') {
            $updateData['departed_at'] = now();
        } elseif ($newStatus === 'out_for_delivery') {
            $updateData['arrived_at'] = now();
        } elseif ($newStatus === 'delivered') {
            $updateData['delivered_at'] = now();
            $updateData['is_delayed'] = false;
            $updateData['delay_reason'] = null;
        } elseif ($newStatus === 'failed_delivery') {
            $updateData['failed_at'] = now();
        }
        
        $delivery->update($updateData);
        
        // Update associated shipment tracking
        if ($delivery->shipment_id) {
            $shipment = Shipment::find($delivery->shipment_id);
            if ($shipment) {
                $this->updateShipmentTracking($shipment, $newStatus, $request->location, $request->status_note);
            }
        }
        
        // Add to status history
        $history = $delivery->status_history ?? [];
        $history[] = [
            'action' => $newStatus,
            'staff' => Auth::user()->name,
            'timestamp' => now()->toIso8601String(),
            'note' => $request->status_note,
            'location' => $request->location,
        ];
        $delivery->status_history = $history;
        $delivery->save();
        
        // If delivered, notify customer
        if ($newStatus === 'delivered') {
            $this->notifyDeliveryComplete($delivery);
        }
        
        return redirect()->route('partner.deliveries.show', $delivery)
            ->with('success', 'Delivery status updated successfully.');
    }

    /**
     * Update shipment tracking from partner action
     */
    private function updateShipmentTracking($shipment, $status, $location, $note)
    {
        $statusMap = [
            'pending' => 'pending',
            'picked_up' => 'picked_up',
            'in_transit' => 'in_transit',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            'failed_delivery' => 'failed_delivery',
        ];
        
        $newStatus = $statusMap[$status] ?? $shipment->status;
        $oldStatus = $shipment->status;
        
        $shipment->status = $newStatus;
        $shipment->save();
        
        $history = $shipment->tracking_history ?? [];
        $history[] = [
            'status' => $newStatus,
            'status_label' => ucfirst(str_replace('_', ' ', $newStatus)),
            'description' => $note ?? "Status updated by partner: " . Auth::user()->name,
            'location' => $location ?? 'Partner Hub',
            'time' => now()->toDateTimeString(),
            'updated_by' => Auth::user()->name,
            'updated_by_role' => Auth::user()->user_type,
        ];
        $shipment->tracking_history = $history;
        $shipment->save();
        
        $shipment->customer?->notify(new ShipmentMilestoneNotification([
            'tracking_number' => $shipment->tracking_number,
            'status' => $newStatus,
            'message' => $note ?? 'Shipment '.str_replace('_', ' ', $newStatus).'.',
            'location' => $location ?? 'Partner Hub',
        ]));
    }

    /**
     * Update delivery status via QR Scan (Partner/Staff)
     * URL: POST /partner/deliveries/scan-update
     */
    public function scanUpdate(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'status' => 'required|in:picked_up,in_transit,out_for_delivery,delivered',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        $partnerId = $this->getPartnerId();
        
        // Find delivery by tracking number
        $delivery = PickupRequest::where('partner_user_id', $partnerId)
            ->where('tracking_number', $request->tracking_number)
            ->first();
        
        if (!$delivery) {
            // Try to find by shipment tracking number
            $shipment = Shipment::where('tracking_number', $request->tracking_number)->first();
            if ($shipment && $shipment->pickup_request_id) {
                $delivery = PickupRequest::find($shipment->pickup_request_id);
            }
        }
        
        if (!$delivery) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery not found or not assigned to you.'
            ], 404);
        }
        
        // Update delivery status
        $oldStatus = $delivery->status;
        $newStatus = $request->status;
        $allowed = ['assigned' => ['picked_up'], 'pending' => ['picked_up'], 'picked_up' => ['in_transit'], 'in_transit' => ['out_for_delivery'], 'out_for_delivery' => ['delivered']];
        if (! in_array($newStatus, $allowed[$oldStatus] ?? [], true)) {
            return response()->json(['success' => false, 'message' => "{$oldStatus} cannot move directly to {$newStatus}."], 422);
        }
        
        $updateData = ['status' => $newStatus];
        if ($newStatus === 'picked_up') {
            $updateData['picked_up_at'] = now();
        } elseif ($newStatus === 'in_transit') {
            $updateData['departed_at'] = now();
        } elseif ($newStatus === 'out_for_delivery') {
            $updateData['arrived_at'] = now();
        } elseif ($newStatus === 'delivered') {
            $updateData['delivered_at'] = now();
        }
        $delivery->update($updateData);
        
        // Add scan event to history
        $history = $delivery->status_history ?? [];
        $history[] = [
            'action' => $newStatus,
            'staff' => Auth::user()->name,
            'timestamp' => now()->toIso8601String(),
            'note' => $request->notes ?? 'Status updated via QR scan',
            'location' => $request->location ?? 'Scan Point',
            'scan_method' => 'QR Code',
        ];
        $delivery->status_history = $history;
        $delivery->save();
        
        // Update associated shipment
        if ($delivery->shipment_id) {
            $shipment = Shipment::find($delivery->shipment_id);
            if ($shipment) {
                $this->updateShipmentTracking($shipment, $newStatus, $request->location, $request->notes);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Delivery status updated successfully via QR scan',
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Get deliveries that need attention (delayed or approaching deadline)
     * URL: GET /partner/deliveries/attention
     */
    public function attentionNeeded()
    {
        $partnerId = $this->getPartnerId();
        
        // Get delayed deliveries
        $delayedDeliveries = PickupRequest::where('partner_user_id', $partnerId)
            ->where('is_delayed', true)
            ->where('status', '!=', 'delivered')
            ->where('status', '!=', 'cancelled')
            ->orderBy('delay_reported_at', 'desc')
            ->get();
        
        // Get deliveries approaching deadline
        $deadlineApproaching = PickupRequest::where('partner_user_id', $partnerId)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->where('is_delayed', false)
            ->get()
            ->filter(function ($pickup) {
                return $pickup->is_approaching_deadline || $pickup->hours_remaining <= 6;
            });
        
        // Get recent reminder logs for this partner (including manifest reminders)
        $recentReminders = ReminderLog::where(function($query) use ($partnerId) {
                $query->whereHas('pickupRequest', function($q) use ($partnerId) {
                    $q->where('partner_user_id', $partnerId);
                })
                ->orWhere(function($q) use ($partnerId) {
                    $q->where('reminder_type', 'partner')
                      ->where('sent_to', Auth::user()->email);
                })
                ->orWhere('metadata->partner_id', $partnerId)
                ->orWhere('reminder_type', 'admin');
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        // Get pending reminders count (pickups & manifests)
        $pendingReminders = DeliveryReminder::where('is_sent', false)
            ->where('scheduled_at', '<=', now())
            ->where(function($query) use ($partnerId) {
                $query->whereHas('pickupRequest', function($q) use ($partnerId) {
                    $q->where('partner_user_id', $partnerId);
                })
                ->orWhereHas('manifest', function($q) use ($partnerId) {
                    $q->where('partner_id', $partnerId);
                });
            })
            ->count();

        // Active assigned domestic manifests for this partner
        $activeManifests = \App\Models\Manifest::where('partner_id', $partnerId)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->with(['bags', 'shipments'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('partner.deliveries.attention', compact(
            'delayedDeliveries', 
            'deadlineApproaching', 
            'recentReminders',
            'pendingReminders',
            'activeManifests'
        ));
    }

    /**
     * Export deliveries report as CSV
     * URL: GET /partner/deliveries/export
     */
    public function export(Request $request)
{
    // If no filters are applied, show the export page
    if (!$request->has('status') && !$request->has('from_date') && !$request->has('to_date') && !$request->has('service_tier')) {
        return view('partner.deliveries.export');
    }
    
    $partnerId = $this->getPartnerId();
    
    $query = PickupRequest::where('partner_user_id', $partnerId);
    
    // Apply filters if provided
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }
    
    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }
    
    if ($request->filled('service_tier')) {
        $query->where('service_tier', $request->service_tier);
    }
    
    $deliveries = $query->orderBy('created_at', 'desc')->get();
    
    if ($deliveries->count() === 0) {
        return redirect()->route('partner.deliveries.export')
            ->with('error', 'No deliveries found to export with the selected filters.');
    }
    
    // Generate CSV
    $filename = "deliveries_report_" . date('Y-m-d_H-i-s') . ".csv";
    $handle = fopen('php://temp', 'w+');
    
    // Add UTF-8 BOM for Excel compatibility
    fwrite($handle, "\xEF\xBB\xBF");
    
    // Add headers
    fputcsv($handle, [
        'ID',
        'Order Reference',
        'Customer Name',
        'Customer Phone',
        'Service Tier',
        'Status',
        'Pickup Address',
        'Delivery Address',
        'Created At',
        'Picked Up At',
        'Departed At',
        'Arrived At',
        'Delivered At',
        'Is Delayed',
        'Delay Reason',
        'Tracking Number',
    ]);
    
    // Add data rows
    foreach ($deliveries as $delivery) {
        fputcsv($handle, [
            $delivery->id,
            $delivery->order_reference ?? 'N/A',
            $delivery->customer_name ?? 'N/A',
            $delivery->customer_phone ?? 'N/A',
            $delivery->service_tier ?? 'Standard',
            $delivery->status ?? 'N/A',
            $delivery->pickup_address ?? 'N/A',
            $delivery->delivery_address ?? 'N/A',
            $delivery->created_at ? $delivery->created_at->format('Y-m-d H:i:s') : 'N/A',
            $delivery->picked_up_at ? $delivery->picked_up_at->format('Y-m-d H:i:s') : 'N/A',
            $delivery->departed_at ? $delivery->departed_at->format('Y-m-d H:i:s') : 'N/A',
            $delivery->arrived_at ? $delivery->arrived_at->format('Y-m-d H:i:s') : 'N/A',
            $delivery->delivered_at ? $delivery->delivered_at->format('Y-m-d H:i:s') : 'N/A',
            $delivery->is_delayed ? 'Yes' : 'No',
            $delivery->delay_reason ?? 'N/A',
            $delivery->tracking_number ?? 'N/A',
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
     * Notify admin and customer about delay
     */
    private function notifyDelay($delivery)
    {
        $partner = $delivery->partner;
        $customer = User::find($delivery->seller_id);
        
        // Notify Admin
        $admins = User::where('user_type', 'admin')->orWhere('user_type', 'super_admin')->get();
        $adminMessage = "⚠️ DELAY REPORTED\n\n";
        $adminMessage .= "Order ID: #{$delivery->id}\n";
        $adminMessage .= "Partner: {$partner->name}\n";
        $adminMessage .= "Contact: {$partner->phone}\n";
        $adminMessage .= "Customer: {$delivery->customer_name}\n";
        $adminMessage .= "Reason: {$delivery->delay_reason}\n";
        $adminMessage .= "Reported at: " . now()->format('Y-m-d H:i:s');
        
        foreach ($admins as $admin) {
            ReminderLog::create([
                'pickup_request_id' => $delivery->id,
                'reminder_id' => null,
                'reminder_type' => 'delay_alert',
                'sent_to' => $admin->email,
                'message' => $adminMessage,
                'channel' => 'database'
            ]);
        }
        
        // Notify Customer
        if ($customer) {
            $customerMessage = "Dear {$delivery->customer_name},\n\n";
            $customerMessage .= "We regret to inform you that your order #{$delivery->id} is delayed.\n\n";
            $customerMessage .= "Reason for delay: {$delivery->delay_reason}\n\n";
            $customerMessage .= "For updates, please contact:\n";
            $customerMessage .= "Partner: {$partner->name}\n";
            $customerMessage .= "Contact Person: {$partner->contact_person}\n";
            $customerMessage .= "Phone: {$partner->phone}\n\n";
            $customerMessage .= "Tracking Number: {$delivery->tracking_number}\n";
            $customerMessage .= "We apologize for any inconvenience caused.\n";
            $customerMessage .= "Thank you for your patience.";
            
            ReminderLog::create([
                'pickup_request_id' => $delivery->id,
                'reminder_id' => null,
                'reminder_type' => 'customer_delay',
                'sent_to' => $customer->email,
                'message' => $customerMessage,
                'channel' => 'email'
            ]);
            
            $delivery->update([
                'customer_notified' => true,
                'customer_notification_message' => $customerMessage,
                'contact_person_name' => $partner->contact_person,
                'contact_person_phone' => $partner->phone
            ]);
        }
    }

    /**
     * Notify customer about successful delivery
     */
    private function notifyDeliveryComplete($delivery)
    {
        $customer = User::find($delivery->seller_id);
        
        if ($customer) {
            $message = "🎉 DELIVERY COMPLETED!\n\n";
            $message .= "Your order #{$delivery->id} has been delivered successfully.\n\n";
            $message .= "Tracking Number: {$delivery->tracking_number}\n";
            $message .= "Delivered at: " . now()->format('Y-m-d H:i:s');
            
            ReminderLog::create([
                'pickup_request_id' => $delivery->id,
                'reminder_id' => null,
                'reminder_type' => 'delivery_complete',
                'sent_to' => $customer->email,
                'message' => $message,
                'channel' => 'email'
            ]);
        }
    }
}
