<?php

namespace App\Services;

use App\Models\PickupRequest;
use App\Models\DeliveryReminder;
use App\Models\ReminderLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\LogisticsService;
use App\Models\Manifest;

class ReminderService
{
    /**
     * Fallback service timeframes in hours (when database service not found)
     */
    protected $serviceTimeframes = [
        'ecommerce' => 1,
        'flash' => 4,
        'same_day' => 12,
        'standard' => 72,
        'himalayan' => 168,
    ];

    /**
     * Fallback service labels
     */
    protected $serviceLabels = [
        'ecommerce' => 'E-commerce (1 hour)',
        'flash' => 'Flash (2-4 hours)',
        'same_day' => 'Same Day (by 8 PM)',
        'standard' => 'Standard (1-3 days)',
        'himalayan' => 'Himalayan (3-7 days)',
    ];

    /**
     * Schedule reminders for a pickup request dynamically based on service transit time
     */
    public function scheduleReminders(PickupRequest $pickup)
    {
        // Clear unsent reminders for this pickup
        DeliveryReminder::where('pickup_request_id', $pickup->id)
            ->where('is_sent', false)
            ->delete();
        
        $serviceTier = $pickup->service_tier ?? 'standard';
        $service = LogisticsService::where('code', $serviceTier)->first();
        $startTime = Carbon::parse($pickup->scheduled_pickup_time ?? $pickup->created_at);

        if ($service) {
            $checkpoints = $service->getReminderCheckpoints($startTime);
            $serviceName = $service->name;
            $transitHours = (float) $service->transit_time_hours;
        } else {
            $transitHours = (float) ($this->serviceTimeframes[$serviceTier] ?? 72);
            $serviceName = $this->serviceLabels[$serviceTier] ?? ucfirst(str_replace('_', ' ', $serviceTier));
            $checkpoints = [
                [
                    'reminder_number' => 1,
                    'milestone_percent' => 50,
                    'scheduled_at' => $startTime->copy()->addMinutes(round($transitHours * 30)),
                    'remaining_hours' => round($transitHours * 0.5, 1),
                    'is_urgent' => false,
                ],
                [
                    'reminder_number' => 2,
                    'milestone_percent' => 75,
                    'scheduled_at' => $startTime->copy()->addMinutes(round($transitHours * 45)),
                    'remaining_hours' => round($transitHours * 0.25, 1),
                    'is_urgent' => true,
                ],
            ];
        }

        $scheduledCount = 0;
        foreach ($checkpoints as $cp) {
            $scheduledAt = $cp['scheduled_at'];
            if ($scheduledAt->isPast()) {
                continue;
            }

            $num = $cp['reminder_number'];
            $pct = $cp['milestone_percent'] ?? null;
            $rem = $cp['remaining_hours'] ?? null;
            $urgent = $cp['is_urgent'] ?? false;

            // Schedule for partner
            $this->createDynamicReminder($pickup->id, $serviceTier, 'partner', $num, $scheduledAt, $serviceName, $transitHours, $pct, $rem, $urgent);

            // Schedule for admin
            $this->createDynamicReminder($pickup->id, $serviceTier, 'admin', $num, $scheduledAt, $serviceName, $transitHours, $pct, $rem, $urgent);

            $scheduledCount++;
        }
        
        Log::info('Reminders dynamically scheduled for pickup', [
            'pickup_id' => $pickup->id,
            'service_tier' => $serviceTier,
            'service_name' => $serviceName,
            'transit_hours' => $transitHours,
            'checkpoints_count' => $scheduledCount,
            'tracking_number' => $pickup->tracking_number
        ]);
    }

    /**
     * Schedule reminders for a domestic manifest dispatched to a partner
     */
    public function scheduleManifestReminders(Manifest $manifest): int
    {
        if (!$manifest->partner_id) {
            return 0;
        }

        // Clear existing unsent reminders for this manifest
        DeliveryReminder::where('manifest_id', $manifest->id)
            ->where('is_sent', false)
            ->delete();

        $dest = strtolower($manifest->destination_city ?? '');
        $transitHours = match(true) {
            str_contains($dest, 'kathmandu') || str_contains($dest, 'lalitpur') || str_contains($dest, 'bhaktapur') => 6.0,
            str_contains($dest, 'pokhara') || str_contains($dest, 'narayangarh') || str_contains($dest, 'chitwan') || str_contains($dest, 'butwal') || str_contains($dest, 'birgunj') => 24.0,
            str_contains($dest, 'biratnagar') || str_contains($dest, 'nepalgunj') || str_contains($dest, 'itahari') || str_contains($dest, 'dharan') => 36.0,
            str_contains($dest, 'dhangadhi') || str_contains($dest, 'surkhet') || str_contains($dest, 'mahendranagar') => 48.0,
            default => 24.0,
        };

        $startTime = Carbon::parse($manifest->dispatched_at ?? $manifest->created_at ?? now());
        $manifestNumber = $manifest->manifest_number;
        $destCity = $manifest->destination_city ?? 'Destination Hub';
        $totalShipments = $manifest->total_shipments ?? 1;

        $checkpoints = [
            [
                'reminder_number' => 1,
                'milestone_percent' => 50,
                'scheduled_at' => $startTime->copy()->addMinutes(round($transitHours * 30)),
                'remaining_hours' => round($transitHours * 0.5, 1),
                'is_urgent' => false,
            ],
            [
                'reminder_number' => 2,
                'milestone_percent' => 75,
                'scheduled_at' => $startTime->copy()->addMinutes(round($transitHours * 45)),
                'remaining_hours' => round($transitHours * 0.25, 1),
                'is_urgent' => true,
            ],
        ];

        $scheduled = 0;
        foreach ($checkpoints as $cp) {
            $scheduledAt = $cp['scheduled_at'];
            if ($scheduledAt->isPast()) {
                continue;
            }

            $num = $cp['reminder_number'];
            $pct = $cp['milestone_percent'];
            $rem = $cp['remaining_hours'];
            $urgent = $cp['is_urgent'];

            $prefix = $urgent ? '⚠️ URGENT: ' : '⏱️ ';
            $partnerMsg = "{$prefix}REMINDER #{$num} ({$pct}% SLA elapsed): Domestic Manifest {$manifestNumber} ({$totalShipments} packages destined for {$destCity}) delivery deadline is approaching. Approx {$rem}h remaining. Please ensure timely runsheet delivery.";
            $adminMsg = "{$prefix}ALERT #{$num} ({$pct}% SLA elapsed): Domestic Manifest {$manifestNumber} to {$destCity} is at {$pct}% of transit SLA ({$rem}h remaining). Assigned to partner {$manifest->partner?->name}.";

            DeliveryReminder::create([
                'manifest_id' => $manifest->id,
                'service_tier' => 'domestic_manifest',
                'reminder_type' => 'partner',
                'reminder_number' => $num,
                'scheduled_at' => $scheduledAt,
                'is_sent' => false,
                'message' => $partnerMsg,
                'metadata' => [
                    'manifest_id' => $manifest->id,
                    'manifest_number' => $manifestNumber,
                    'destination_city' => $destCity,
                    'total_shipments' => $totalShipments,
                    'transit_hours' => $transitHours,
                    'milestone_percent' => $pct,
                    'remaining_hours' => $rem,
                    'is_urgent' => $urgent,
                ],
            ]);

            DeliveryReminder::create([
                'manifest_id' => $manifest->id,
                'service_tier' => 'domestic_manifest',
                'reminder_type' => 'admin',
                'reminder_number' => $num,
                'scheduled_at' => $scheduledAt,
                'is_sent' => false,
                'message' => $adminMsg,
                'metadata' => [
                    'manifest_id' => $manifest->id,
                    'manifest_number' => $manifestNumber,
                    'destination_city' => $destCity,
                    'total_shipments' => $totalShipments,
                    'transit_hours' => $transitHours,
                    'milestone_percent' => $pct,
                    'remaining_hours' => $rem,
                    'is_urgent' => $urgent,
                ],
            ]);

            $scheduled++;
        }

        return $scheduled;
    }

    /**
     * E-commerce: 1 hour delivery - Reminder 30 minutes before
     */
    private function scheduleEcommerceReminders($pickup)
    {
        $pickupTime = Carbon::parse($pickup->scheduled_pickup_time ?? $pickup->created_at);
        $deadline = $pickupTime->copy()->addHours(1);
        
        $reminderTime = $deadline->copy()->subMinutes(30);
        
        if ($reminderTime->isFuture()) {
            $this->createReminder($pickup->id, 'ecommerce', 'partner', 1, $reminderTime);
            $this->createReminder($pickup->id, 'ecommerce', 'admin', 1, $reminderTime);
        }
    }

    /**
     * Flash: 2-4 hours delivery - Reminder 1 hour before max time
     */
    private function scheduleFlashReminders($pickup)
    {
        $maxHours = 4;
        $deadline = Carbon::parse($pickup->scheduled_pickup_time ?? $pickup->created_at)->addHours($maxHours);
        
        $reminderTime = $deadline->copy()->subHours(1);
        
        if ($reminderTime->isFuture()) {
            $this->createReminder($pickup->id, 'flash', 'partner', 1, $reminderTime);
            $this->createReminder($pickup->id, 'flash', 'admin', 1, $reminderTime);
        }
    }

    /**
     * Same Day: By 8 PM - Reminders 2 hours and 1 hour before
     */
    private function scheduleSameDayReminders($pickup)
    {
        $deadline = Carbon::parse($pickup->scheduled_pickup_time ?? $pickup->created_at)->setTime(20, 0);
        
        $reminder1Time = $deadline->copy()->subHours(2);
        if ($reminder1Time->isFuture()) {
            $this->createReminder($pickup->id, 'same_day', 'partner', 1, $reminder1Time);
            $this->createReminder($pickup->id, 'same_day', 'admin', 1, $reminder1Time);
        }
        
        $reminder2Time = $deadline->copy()->subHours(1);
        if ($reminder2Time->isFuture()) {
            $this->createReminder($pickup->id, 'same_day', 'partner', 2, $reminder2Time);
            $this->createReminder($pickup->id, 'same_day', 'admin', 2, $reminder2Time);
        }
    }

    /**
     * Standard: 1-3 days - Reminders: 1 day before & on last day at 2PM
     */
    private function scheduleStandardReminders($pickup)
    {
        $maxDays = 3;
        $deadline = Carbon::parse($pickup->scheduled_pickup_time ?? $pickup->created_at)->addDays($maxDays);
        
        $reminder1Time = $deadline->copy()->subDays(1)->setTime(10, 0);
        if ($reminder1Time->isFuture()) {
            $this->createReminder($pickup->id, 'standard', 'partner', 1, $reminder1Time);
            $this->createReminder($pickup->id, 'standard', 'admin', 1, $reminder1Time);
        }
        
        $reminder2Time = $deadline->copy()->setTime(14, 0);
        if ($reminder2Time->isFuture()) {
            $this->createReminder($pickup->id, 'standard', 'partner', 2, $reminder2Time);
            $this->createReminder($pickup->id, 'standard', 'admin', 2, $reminder2Time);
        }
    }

    /**
     * Himalayan: 3-7 days - Reminders: 2 days, 1 day before, last day 2PM
     */
    private function scheduleHimalayanReminders($pickup)
    {
        $maxDays = 7;
        $deadline = Carbon::parse($pickup->scheduled_pickup_time ?? $pickup->created_at)->addDays($maxDays);
        
        $reminder1Time = $deadline->copy()->subDays(2)->setTime(10, 0);
        if ($reminder1Time->isFuture()) {
            $this->createReminder($pickup->id, 'himalayan', 'partner', 1, $reminder1Time);
            $this->createReminder($pickup->id, 'himalayan', 'admin', 1, $reminder1Time);
        }
        
        $reminder2Time = $deadline->copy()->subDays(1)->setTime(10, 0);
        if ($reminder2Time->isFuture()) {
            $this->createReminder($pickup->id, 'himalayan', 'partner', 2, $reminder2Time);
            $this->createReminder($pickup->id, 'himalayan', 'admin', 2, $reminder2Time);
        }
        
        $reminder3Time = $deadline->copy()->setTime(14, 0);
        if ($reminder3Time->isFuture()) {
            $this->createReminder($pickup->id, 'himalayan', 'partner', 3, $reminder3Time);
            $this->createReminder($pickup->id, 'himalayan', 'admin', 3, $reminder3Time);
        }
    }

    /**
     * Create dynamic reminder record with SLA details and checkpoint metadata
     */
    public function createDynamicReminder($pickupId, $serviceTier, $reminderType, $number, $scheduledAt, $serviceName, $transitHours, $percent = null, $remainingHours = null, $isUrgent = false)
    {
        if ($scheduledAt->isPast()) {
            return;
        }

        $message = $this->getDynamicReminderMessage($serviceTier, $reminderType, $number, $serviceName, $transitHours, $percent, $remainingHours, $isUrgent);

        DeliveryReminder::create([
            'pickup_request_id' => $pickupId,
            'service_tier' => $serviceTier,
            'reminder_type' => $reminderType,
            'reminder_number' => $number,
            'scheduled_at' => $scheduledAt,
            'is_sent' => false,
            'message' => $message,
            'metadata' => [
                'service_name' => $serviceName,
                'transit_hours' => $transitHours,
                'milestone_percent' => $percent,
                'remaining_hours' => $remainingHours,
                'is_urgent' => $isUrgent,
            ],
        ]);
    }

    /**
     * Build rich dynamic reminder message
     */
    public function getDynamicReminderMessage($serviceTier, $reminderType, $number, $serviceName, $transitHours, $percent = null, $remainingHours = null, $isUrgent = false)
    {
        $urgencyPrefix = $isUrgent ? '⚠️ URGENT: ' : '⏱️ ';
        $pctText = $percent ? " ({$percent}% SLA elapsed)" : "";
        $remText = ($remainingHours !== null && $remainingHours > 0) ? " Approx {$remainingHours}h remaining before deadline." : "";

        if ($reminderType === 'partner') {
            return "{$urgencyPrefix}REMINDER #{$number}{$pctText}: Your {$serviceName} delivery deadline is approaching (Transit SLA: {$transitHours}h).{$remText} Please ensure timely dispatch and update telemetry.";
        } elseif ($reminderType === 'admin') {
            return "{$urgencyPrefix}ALERT #{$number}{$pctText}: {$serviceName} consignment deadline is approaching (Transit SLA: {$transitHours}h).{$remText} Please follow up with the delivery partner.";
        } else {
            return "{$urgencyPrefix}REMINDER #{$number}: Your {$serviceName} delivery is approaching the scheduled arrival time.";
        }
    }

    /**
     * Create a reminder record (Legacy fallback)
     */
    private function createReminder($pickupId, $serviceTier, $reminderType, $number, $scheduledAt)
    {
        if ($scheduledAt->isPast()) {
            return;
        }
        
        $message = $this->getReminderMessage($serviceTier, $reminderType, $number);
        
        DeliveryReminder::create([
            'pickup_request_id' => $pickupId,
            'service_tier' => $serviceTier,
            'reminder_type' => $reminderType,
            'reminder_number' => $number,
            'scheduled_at' => $scheduledAt,
            'is_sent' => false,
            'message' => $message
        ]);
    }

    /**
     * Get reminder message based on service and type
     */
    private function getReminderMessage($serviceTier, $reminderType, $number)
    {
        $serviceLabel = $this->serviceLabels[$serviceTier] ?? 'Standard';
        $urgency = $number >= 2 ? '⚠️ URGENT: ' : '';
        
        if ($reminderType === 'partner') {
            return "{$urgency}REMINDER #{$number}: Your {$serviceLabel} delivery is approaching the deadline. Please ensure timely delivery and update status.";
        } elseif ($reminderType === 'admin') {
            return "{$urgency}ALERT #{$number}: A {$serviceLabel} delivery is at risk of delay. Please follow up with the partner.";
        } else {
            return "{$urgency}REMINDER #{$number}: Your {$serviceLabel} delivery is approaching the deadline.";
        }
    }

    /**
     * Process pending reminders (run by cron job)
     */
    public function processPendingReminders()
    {
        $reminders = DeliveryReminder::where('is_sent', false)
            ->where('scheduled_at', '<=', now())
            ->get();
        
        $processed = 0;
        
        foreach ($reminders as $reminder) {
            // Check if this is a domestic manifest reminder
            if ($reminder->manifest_id) {
                $manifest = Manifest::with('partner')->find($reminder->manifest_id);
                if (!$manifest || in_array($manifest->status, ['delivered', 'cancelled'], true)) {
                    $reminder->delete();
                    continue;
                }

                $this->sendManifestReminder($reminder, $manifest);
                $reminder->update([
                    'is_sent' => true,
                    'sent_at' => now(),
                ]);
                $processed++;
                continue;
            }

            $pickup = PickupRequest::find($reminder->pickup_request_id);
            
            if (!$pickup || in_array($pickup->status, ['delivered', 'cancelled'])) {
                $reminder->delete();
                continue;
            }
            
            if ($pickup->status === 'out_for_delivery') {
                $reminder->delete();
                continue;
            }
            
            $this->sendReminder($reminder, $pickup);
            
            $reminder->update([
                'is_sent' => true,
                'sent_at' => now()
            ]);
            
            $processed++;
        }
        
        Log::info('Processed pending reminders', ['count' => $processed]);
        return $processed;
    }

    /**
     * Send reminder notification for a domestic manifest
     */
    private function sendManifestReminder($reminder, Manifest $manifest)
    {
        $partner = $manifest->partner;
        $sentTo = $partner ? ($partner->email ?? 'partner@netpack.local') : 'operations@netpack.local';
        $message = $reminder->message . "\n\n📦 Manifest: {$manifest->manifest_number}\n📍 Route: {$manifest->origin_city} ➔ {$manifest->destination_city}\n📊 Total Packets: {$manifest->total_shipments} PKG\n⚖️ Total Weight: {$manifest->total_weight} kg";

        ReminderLog::create([
            'reminder_id' => $reminder->id,
            'reminder_type' => $reminder->reminder_type,
            'sent_to' => $sentTo,
            'message' => $message,
            'channel' => 'database',
            'status' => 'sent',
            'sent_at' => now(),
            'metadata' => array_merge($reminder->metadata ?? [], [
                'manifest_id' => $manifest->id,
                'manifest_number' => $manifest->manifest_number,
                'partner_id' => $manifest->partner_id,
                'partner_name' => $partner ? $partner->name : 'Unassigned',
                'reminder_number' => $reminder->reminder_number,
            ]),
        ]);

        if ($reminder->reminder_type === 'partner' && $partner) {
            $partner->notify(new \App\Notifications\ManifestAssignedNotification($manifest));
        }
    }

    /**
     * Send immediate manual reminder to partner for a domestic manifest
     */
    public function sendManualManifestReminder(Manifest $manifest, ?string $customNote = null): ReminderLog
    {
        $partner = $manifest->partner;
        $sentTo = $partner ? ($partner->email ?? 'partner@netpack.local') : 'operations@netpack.local';
        $note = $customNote ?: "Operational SLA reminder for Manifest {$manifest->manifest_number}.";
        $message = "⏱️ OPERATIONAL REMINDER: {$note}\n\n📦 Manifest: {$manifest->manifest_number}\n📍 Destination: {$manifest->destination_city}\n📊 Packets: {$manifest->total_shipments} PKG\nStatus: " . ucfirst($manifest->status);

        $log = ReminderLog::create([
            'reminder_type' => 'partner',
            'sent_to' => $sentTo,
            'message' => $message,
            'channel' => 'database',
            'status' => 'sent',
            'sent_at' => now(),
            'metadata' => [
                'manifest_id' => $manifest->id,
                'manifest_number' => $manifest->manifest_number,
                'partner_id' => $manifest->partner_id,
                'is_manual' => true,
            ],
        ]);

        if ($partner) {
            $partner->notify(new \App\Notifications\ManifestAssignedNotification($manifest));
        }

        return $log;
    }

    /**
     * Send a reminder notification
     */
    private function sendReminder($reminder, $pickup)
    {
        if ($reminder->reminder_type === 'partner') {
            $this->notifyPartner($pickup, $reminder);
        } elseif ($reminder->reminder_type === 'admin') {
            $this->notifyAdmin($pickup, $reminder);
        } elseif ($reminder->reminder_type === 'customer') {
            $this->notifyCustomer($pickup, $reminder);
        }
    }

    /**
     * Notify partner about the reminder
     */
    private function notifyPartner($pickup, $reminder)
    {
        $partner = $pickup->partner;
        $sentTo = $partner ? ($partner->email ?? 'partner@netpack.local') : 'operations@netpack.local';
        
        $phone = $pickup->customer_phone ?? 'N/A';
        $message = $reminder->message . "\n\n📦 Order ID: #{$pickup->id}\nTracking: " . ($pickup->tracking_number ?? 'Pending') . "\n👤 Client: {$pickup->customer_name}\n📍 Delivery Address: {$pickup->delivery_address}\n📱 Phone: {$phone}";
        
        ReminderLog::create([
            'pickup_request_id' => $pickup->id,
            'reminder_id' => $reminder->id,
            'reminder_type' => 'partner',
            'sent_to' => $sentTo,
            'message' => $message,
            'channel' => 'email',
            'status' => 'sent',
            'sent_at' => now(),
            'metadata' => array_merge($reminder->metadata ?? [], [
                'reminder_number' => $reminder->reminder_number,
                'service_tier' => $reminder->service_tier,
                'partner_name' => $partner ? $partner->name : 'Unassigned',
                'tracking_number' => $pickup->tracking_number,
            ])
        ]);
        
        if ($reminder->reminder_number === 1 && !$pickup->customer_notified) {
            $this->notifyCustomerWithContact($pickup);
        }
    }

    /**
     * Notify admin about the reminder
     */
    private function notifyAdmin($pickup, $reminder)
    {
        $admins = User::whereIn('user_type', ['admin', 'super_admin'])->get();
        
        $message = $reminder->message . "\n\n📦 Order ID: #{$pickup->id}\n🤝 Partner: " . ($pickup->partner->name ?? 'Not assigned') . "\n👤 Customer: {$pickup->customer_name}\n📍 Delivery Address: {$pickup->delivery_address}";
        
        foreach ($admins as $admin) {
            ReminderLog::create([
                'pickup_request_id' => $pickup->id,
                'reminder_id' => $reminder->id,
                'reminder_type' => 'admin',
                'sent_to' => $admin->email,
                'message' => $message,
                'channel' => 'email',
                'status' => 'sent',
                'sent_at' => now(),
                'metadata' => [
                    'reminder_number' => $reminder->reminder_number,
                    'service_tier' => $reminder->service_tier,
                    'admin_id' => $admin->id,
                ]
            ]);
        }
    }

    /**
     * Notify customer with partner contact information
     */
    private function notifyCustomerWithContact($pickup)
    {
        $partner = $pickup->partner;
        if (!$partner) return;
        
        $customer = User::find($pickup->seller_id);
        if (!$customer) return;
        
        $partnerPhone = $partner->phone ?? 'N/A';
        $partnerContact = $partner->contact_person ?? $partner->name ?? 'Partner';
        
        $message = "Dear Customer,\n\nYour order #{$pickup->id} is being processed. For any questions about your delivery, please contact:\n\n";
        $message .= "🤝 Partner: {$partner->name}\n";
        $message .= "👤 Contact Person: {$partnerContact}\n";
        $message .= "📱 Phone: {$partnerPhone}\n\n";
        $message .= "📦 Tracking Number: {$pickup->tracking_number}\n";
        $message .= "⏰ Expected Delivery: " . $this->getExpectedDeliveryText($pickup) . "\n\n";
        $message .= "Thank you for choosing NetPack!";
        
        ReminderLog::create([
            'pickup_request_id' => $pickup->id,
            'reminder_id' => null,
            'reminder_type' => 'customer',
            'sent_to' => $customer->email,
            'message' => $message,
            'channel' => 'email',
            'status' => 'sent',
            'sent_at' => now(),
            'metadata' => [
                'partner_id' => $partner->id,
                'partner_name' => $partner->name,
            ]
        ]);
        
        $pickup->update([
            'customer_notified' => true,
            'customer_notification_message' => $message,
            'contact_person_name' => $partnerContact,
            'contact_person_phone' => $partnerPhone
        ]);
    }

    /**
     * Notify customer about delay
     */
    private function notifyCustomer($pickup, $reminder)
    {
        $customer = User::find($pickup->seller_id);
        if (!$customer) return;
        
        $partnerName = $pickup->partner->name ?? 'Partner';
        $partnerPhone = $pickup->partner->phone ?? 'N/A';
        
        $message = "Dear Customer,\n\nWe regret to inform you that your order #{$pickup->id} is experiencing a delay. Our team is working to resolve this as quickly as possible.\n\n";
        $message .= "For updates, please contact:\n";
        $message .= "🤝 Partner: {$partnerName}\n";
        $message .= "📱 Phone: {$partnerPhone}\n\n";
        $message .= "We apologize for any inconvenience caused.\n";
        $message .= "Thank you for your patience.";
        
        ReminderLog::create([
            'pickup_request_id' => $pickup->id,
            'reminder_id' => $reminder->id,
            'reminder_type' => 'customer',
            'sent_to' => $customer->email,
            'message' => $message,
            'channel' => 'email',
            'status' => 'sent',
            'sent_at' => now(),
            'metadata' => [
                'delay_reported' => true,
            ]
        ]);
    }

    /**
     * Get expected delivery text based on service tier
     */
    private function getExpectedDeliveryText($pickup)
    {
        $serviceTier = $pickup->service_tier ?? 'standard';
        
        $texts = [
            'ecommerce' => 'Within 1 hour',
            'flash' => '2-4 hours',
            'same_day' => 'Today by 8 PM',
            'standard' => '1-3 days',
            'himalayan' => '3-7 days',
        ];
        
        return $texts[$serviceTier] ?? 'As scheduled';
    }

    /**
     * Schedule reminders for all pending pickups
     */
    public function scheduleAllReminders()
    {
        $pickups = PickupRequest::whereIn('status', ['pending', 'confirmed', 'picked_up', 'in_transit'])
            ->where('is_delayed', false)
            ->get();
        
        $count = 0;
        foreach ($pickups as $pickup) {
            if ($pickup->deliveryReminders()->count() === 0) {
                $this->scheduleReminders($pickup);
                $count++;
            }
        }
        
        Log::info('Scheduled reminders for all pending pickups', ['count' => $count]);
        return $count;
    }
}