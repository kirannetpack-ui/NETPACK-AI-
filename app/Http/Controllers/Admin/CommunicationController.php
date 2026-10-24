<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryReminder;
use App\Models\Notification;
use App\Models\PickupRequest;
use App\Models\ReminderLog;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommunicationController extends Controller
{
    /**
     * Delay reason codes and standardized descriptions
     */
    public const REASONS = [
        'weather' => 'Adverse Weather (Monsoon Landslide, High Snowfall, or Dense Fog)',
        'highway_blocked' => 'Highway Obstruction / Landslide Blockage (e.g., Mugling-Narayanghat Highway)',
        'customs_hold' => 'Customs Clearance & Documentation Hold (TIA Cargo / Birgunj Dry Port)',
        'recipient_unreachable' => 'Consignee Unreachable (Phone Off / Address Inaccessible)',
        'address_incomplete' => 'Incomplete Locality / Ward Verification Required',
        'customer_reschedule' => 'Consignee Requested Delivery Reschedule',
        'force_majeure' => 'Regional Public Restriction or Force Majeure',
    ];

    /**
     * Display the Communications & Delay Management Hub
     */
    public function index(Request $request)
    {
        $stats = [
            'total_reminders' => DeliveryReminder::count(),
            'pending_reminders' => DeliveryReminder::where('is_sent', false)->count(),
            'total_logs' => ReminderLog::count(),
            'delayed_shipments' => PickupRequest::where('is_delayed', true)->count(),
        ];

        $reasons = self::REASONS;

        $delayedPickups = PickupRequest::where('is_delayed', true)
            ->latest('delay_reported_at')
            ->take(15)
            ->get();

        $reminders = DeliveryReminder::with('pickupRequest')
            ->orderBy('scheduled_at', 'desc')
            ->paginate(15, ['*'], 'reminders_page');

        $logs = ReminderLog::with('pickupRequest')
            ->latest()
            ->paginate(15, ['*'], 'logs_page');

        return view('admin.communications.index', compact('stats', 'reasons', 'delayedPickups', 'reminders', 'logs'));
    }

    /**
     * Issue an urgent delay / exception broadcast for a shipment
     */
    public function sendDelayAlert(Request $request)
    {
        $validated = $request->validate([
            'tracking_number' => 'required|string|max:100',
            'reason_code' => 'required|string|max:100',
            'custom_note' => 'nullable|string|max:1000',
            'expected_resolution' => 'nullable|date|after:now',
            'channel' => 'required|string|in:email,sms,push,all',
        ]);

        $search = trim($validated['tracking_number']);

        // Look up by shipment tracking or pickup request reference
        $shipment = Shipment::where('tracking_number', $search)->first();
        $pickup = null;

        if ($shipment && $shipment->pickup_request_id) {
            $pickup = PickupRequest::find($shipment->pickup_request_id);
        }

        if (!$pickup) {
            $pickup = PickupRequest::where('tracking_number', $search)
                ->orWhere('id', is_numeric($search) ? (int) $search : 0)
                ->first();
        }

        $reasonText = self::REASONS[$validated['reason_code']] ?? $validated['reason_code'];
        $fullExplanation = $reasonText . ($validated['custom_note'] ? ' — ' . $validated['custom_note'] : '');

        if ($pickup) {
            $pickup->update([
                'is_delayed' => true,
                'delay_reason' => $fullExplanation,
                'delay_reported_at' => now(),
                'expected_resolution_time' => $validated['expected_resolution'] ?? now()->addDay(),
            ]);
        }

        if ($shipment) {
            $history = $shipment->tracking_history ?? [];
            $history[] = [
                'status' => 'EXCEPTION / DELAY',
                'location' => 'Transit Checkpoint',
                'description' => 'Delivery Delay Notice: ' . $fullExplanation,
                'timestamp' => now()->toIso8601String(),
                'updated_by' => auth()->user()->name ?? 'Logistics Operations',
            ];
            $shipment->update([
                'tracking_history' => $history,
            ]);
        }

        // Record into ReminderLog
        ReminderLog::create([
            'pickup_request_id' => $pickup ? $pickup->id : null,
            'reminder_id' => null,
            'reminder_type' => 'delay_alert',
            'sent_to' => $shipment ? ($shipment->receiver_email ?? $shipment->sender_email ?? 'Client') : ($pickup ? ($pickup->customer_email ?? 'Client') : $search),
            'message' => 'Shipment #' . $search . ' delayed: ' . $fullExplanation,
            'channel' => $validated['channel'],
            'status' => 'sent',
            'sent_at' => now(),
            'metadata' => [
                'reason_code' => $validated['reason_code'],
                'expected_resolution' => $validated['expected_resolution'] ?? null,
                'dispatched_by' => auth()->id(),
            ],
        ]);

        return redirect()->route('admin.communications')
            ->with('success', 'Urgent delay notification recorded and broadcasted successfully for #' . $search);
    }

    /**
     * Resend an existing reminder or alert
     */
    public function resendReminder($id)
    {
        $log = ReminderLog::find($id);

        if ($log) {
            $log->update([
                'sent_at' => now(),
                'status' => 'sent',
            ]);

            return redirect()->route('admin.communications')
                ->with('success', 'Alert reminder re-dispatched to ' . $log->sent_to);
        }

        $reminder = DeliveryReminder::findOrFail($id);
        $reminder->update([
            'sent_at' => now(),
            'is_sent' => true,
        ]);

        return redirect()->route('admin.communications')
            ->with('success', 'Reminder marked as re-dispatched successfully.');
    }
}
