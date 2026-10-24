<?php

namespace App\Services;

use App\Models\MAWB;
use App\Models\Manifest;
use App\Models\OverseasHub;
use App\Models\PickupRequest;
use App\Models\Shipment;
use App\Models\DomesticShipment;
use App\Models\Order;
use App\Models\TrackingSubscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AutomatedTrackingService
{
    public function __construct(private readonly ShipmentScanService $scanService)
    {
    }

    /**
     * Get or resolve a system actor for automated events when no user is logged in
     */
    public function getSystemActor(?User $preferred = null): User
    {
        if ($preferred) {
            return $preferred;
        }

        if (auth()->check()) {
            return auth()->user();
        }

        $systemUser = User::whereIn('user_type', ['super_admin', 'admin'])->first();
        if ($systemUser) {
            return $systemUser;
        }

        // Fallback dummy user model in memory
        $fallback = new User();
        $fallback->id = 1;
        $fallback->name = 'NETPACK Automated Engine';
        $fallback->user_type = 'super_admin';
        return $fallback;
    }

    /**
     * 1. Automatically record structured booking milestone
     */
    public function recordBookingPlaced(Model $shipment, ?string $originCity = null, ?User $actor = null): Model
    {
        $actor = $this->getSystemActor($actor);
        $location = $originCity ?: ($shipment->sender_city ?? 'Kathmandu Gateway');
        $serviceLabel = strtoupper($shipment->service_type ?? 'STANDARD');

        $notes = "Electronic consignment booking received. Verified for {$serviceLabel} logistics corridor.";

        $history = $shipment->tracking_history ?? [];
        $hasBooking = collect($history)->contains(fn ($e) => ($e['event_code'] ?? '') === 'booking_confirmed' || ($e['status'] ?? '') === 'pending');

        if (!$hasBooking) {
            $initialEvent = [
                'event_code' => 'booking_confirmed',
                'status' => 'confirmed',
                'status_label' => 'Booking Confirmed',
                'icon' => 'fa-circle-check',
                'description' => $notes,
                'location' => $location,
                'time' => now()->toIso8601String(),
                'scan_source' => 'system_booking_automation',
                'scanned_by_user_id' => $actor->id ?? 1,
                'scanned_by_role' => 'system',
                'meta' => [
                    'weight' => $shipment->chargeable_weight ?? ($shipment->actual_weight ?? ($shipment->weight ?? 1)),
                    'service' => $shipment->service_type ?? 'standard',
                ],
            ];

            $history = array_merge([$initialEvent], $history);
            $shipment->update([
                'status' => $shipment->status ?: 'confirmed',
                'tracking_history' => $history,
            ]);
        }

        return $shipment;
    }

    /**
     * 2. Automatically record pickup request lifecycle milestones
     */
    public function recordPickupScheduled(PickupRequest $pickup, array $details = []): PickupRequest
    {
        $actor = $this->getSystemActor();
        $pickupAddress = $pickup->pickup_address ?: 'Sender address';
        $window = $pickup->scheduled_pickup_time ? $pickup->scheduled_pickup_time->format('d M Y, h:i A') : 'Scheduled Window';

        $event = [
            'event_code' => 'pickup_scheduled',
            'status' => 'pending',
            'status_label' => 'Pickup Scheduled',
            'icon' => 'fa-calendar-check',
            'description' => "Pickup scheduled for {$window} at {$pickupAddress}. Courier dispatch queued.",
            'location' => $pickup->pickup_city ?? ($pickup->pickup_district ?? 'Kathmandu'),
            'time' => now()->toIso8601String(),
            'scan_source' => 'pickup_automation',
            'scanned_by_user_id' => $actor->id ?? 1,
        ];

        $history = $pickup->status_history ?? [];
        $history[] = $event;

        $pickup->update([
            'status' => 'pending',
            'status_history' => $history,
        ]);

        $this->notifySubscribers($pickup->tracking_number, 'Pickup Scheduled', $event['description'], $event['location']);

        return $pickup;
    }

    /**
     * Courier assigned to collect parcel from sender
     */
    public function recordPickupAssigned(PickupRequest $pickup, User $courier, ?string $eta = null): PickupRequest
    {
        $actor = $this->getSystemActor();
        $riderName = $courier->name ?? 'Assigned Courier';
        $phone = $courier->phone ? " ({$courier->phone})" : '';
        $etaText = $eta ? " Estimated collection ETA: {$eta}." : '';

        $event = [
            'event_code' => 'pickup_assigned',
            'status' => 'assigned',
            'status_label' => 'Courier Dispatched for Pickup',
            'icon' => 'fa-motorcycle',
            'description' => "Courier {$riderName}{$phone} dispatched for collection.{$etaText}",
            'location' => $pickup->pickup_city ?? ($pickup->pickup_district ?? 'Local Pickup Route'),
            'time' => now()->toIso8601String(),
            'scan_source' => 'pickup_automation',
            'scanned_by_user_id' => $actor->id ?? 1,
        ];

        $history = $pickup->status_history ?? [];
        $history[] = $event;

        $pickup->update([
            'status' => 'assigned',
            'assigned_rider_id' => $courier->id,
            'status_history' => $history,
        ]);

        $this->notifySubscribers($pickup->tracking_number, 'Courier Dispatched', $event['description'], $event['location']);

        return $pickup;
    }

    /**
     * Physical parcel collected from sender
     */
    public function recordPhysicalPickup(PickupRequest $pickup, ?string $location = null, ?User $actor = null): PickupRequest
    {
        $actor = $this->getSystemActor($actor);
        $pickupLoc = $location ?: ($pickup->pickup_address ?: 'Sender premises');

        $event = [
            'event_code' => 'shipment_picked_up',
            'status' => 'picked_up',
            'status_label' => 'Consignment Collected',
            'icon' => 'fa-box-check',
            'description' => "Package successfully collected from {$pickupLoc}. Custody transferred to NETPACK courier network.",
            'location' => $pickupLoc,
            'time' => now()->toIso8601String(),
            'scan_source' => 'pickup_collection_scan',
            'scanned_by_user_id' => $actor->id ?? 1,
        ];

        $history = $pickup->status_history ?? [];
        $history[] = $event;

        $pickup->update([
            'status' => 'picked_up',
            'picked_up_at' => now(),
            'status_history' => $history,
        ]);

        // If this pickup is linked to an order or shipment, advance that shipment as well
        if ($pickup->order_id) {
            $order = Order::find($pickup->order_id);
            if ($order && $order->status !== 'delivered') {
                $order->update(['status' => 'picked_up']);
            }
        }

        if ($pickup->tracking_number) {
            $shipment = Shipment::where('tracking_number', $pickup->tracking_number)->first();
            if ($shipment && in_array($shipment->status, ['pending', 'confirmed'])) {
                try {
                    $this->scanService->record(
                        $shipment,
                        'shipment_picked_up',
                        $pickupLoc,
                        'Package collected from sender by courier',
                        $actor,
                        'pickup_sync'
                    );
                } catch (\Throwable $e) {
                    Log::warning('Pickup sync to shipment failed: ' . $e->getMessage());
                }
            }
            $this->notifySubscribers($pickup->tracking_number, 'Package Collected', $event['description'], $pickupLoc);
        }

        return $pickup;
    }

    /**
     * 3. Origin Hub / Depot Intake & Security Screening
     */
    public function recordOriginHubIntake(Model $shipment, string $hubName, ?float $verifiedWeight = null, ?User $actor = null): Model
    {
        $actor = $this->getSystemActor($actor);

        $weightNote = $verifiedWeight ? " (Verified weight: {$verifiedWeight} kg)" : '';
        $desc = "Consignment received at {$hubName}. Security clearance, X-ray scanning, and weight verification completed{$weightNote}.";

        return DB::transaction(function () use ($shipment, $hubName, $desc, $verifiedWeight, $actor) {
            $updated = $this->scanService->record(
                $shipment,
                'origin_facility_arrival',
                $hubName,
                $desc,
                $actor,
                'hub_intake_automation'
            );

            if ($verifiedWeight && $updated instanceof Shipment) {
                $updated->update(['actual_weight' => $verifiedWeight]);
            }

            $this->notifySubscribers($shipment->tracking_number, 'Received at Origin Hub', $desc, $hubName);

            return $updated;
        });
    }

    /**
     * 4. Cascade MAWB and International Airline Milestones to all attached shipments
     */
    public function cascadeMawbMilestone(
        MAWB $mawb,
        string $mawbStatus,
        ?string $location = null,
        ?string $customNotes = null,
        ?User $actor = null
    ): array {
        $actor = $this->getSystemActor($actor);

        // Fetch all shipments assigned to this MAWB (via direct mawb_id or manifest)
        $shipments = Shipment::where('mawb_id', $mawb->id)
            ->orWhere('mawb_number', $mawb->mawb_number)
            ->get();

        if ($shipments->isEmpty() && $mawb->assigned_manifest_id) {
            $manifest = Manifest::with('shipments.shipment')->find($mawb->assigned_manifest_id);
            if ($manifest) {
                $shipments = $manifest->shipments->map(fn ($ms) => $ms->shipment)->filter();
            }
        }

        $hub = $mawb->hub ?: ($mawb->assignedManifest?->hub);
        $destinationHubName = $hub?->hub_name ?? ($mawb->destination_airport ?: 'Destination Gateway Hub');
        $originAirport = $mawb->origin_airport ?: 'KTM';
        $flightDesc = "Flight {$mawb->airline_code} {$mawb->flight_number}";

        $eventCode = match ($mawbStatus) {
            'in_transit' => 'in_transit_airline',
            'cleared' => 'customs_cleared',
            'completed' => 'import_cleared',
            default => 'in_transit_airline',
        };

        $statusLabel = match ($mawbStatus) {
            'in_transit' => 'In Transit to Hub by Airlines',
            'cleared' => 'Customs Cleared at Destination Hub',
            'completed' => 'Import Clearance Completed',
            default => 'Air Cargo Transit',
        };

        $defaultLocation = match ($mawbStatus) {
            'in_transit' => "{$originAirport} International Cargo Terminal, Tribhuvan Gateway",
            'cleared', 'completed' => "{$destinationHubName} Air Cargo Customs Depot",
            default => $location ?: 'Transit Air Corridor',
        };

        $eventLocation = $location ?: $defaultLocation;

        $eventDescription = match ($mawbStatus) {
            'in_transit' => $customNotes ?: "Consolidated under Master Air Waybill {$mawb->mawb_number}. {$flightDesc} departed {$originAirport} en route to {$destinationHubName}.",
            'cleared' => $customNotes ?: "MAWB {$mawb->mawb_number} cleared through destination customs at {$destinationHubName}. Consignments released for regional agency distribution.",
            'completed' => $customNotes ?: "Flight breakdown and port agency import processing complete at {$destinationHubName}.",
            default => $customNotes ?: "Airway cargo milestone updated for MAWB {$mawb->mawb_number}.",
        };

        $updatedCount = 0;
        $failedCount = 0;

        foreach ($shipments as $shipment) {
            if (!$shipment) continue;

            // Skip already delivered or cancelled shipments
            if (in_array($shipment->status, ['delivered', 'cancelled', 'returned'])) {
                continue;
            }

            try {
                DB::transaction(function () use ($shipment, $mawb, $eventCode, $eventLocation, $eventDescription, $actor) {
                    $this->scanService->record(
                        $shipment,
                        $eventCode,
                        $eventLocation,
                        $eventDescription,
                        $actor,
                        'mawb_cascade_automation',
                        null,
                        [
                            'mawb_number' => $mawb->mawb_number,
                            'flight_number' => $mawb->flight_number,
                            'airline' => $mawb->airline_name,
                            'airline_code' => $mawb->airline_code,
                        ]
                    );

                    $shipment->update([
                        'mawb_id' => $mawb->id,
                        'mawb_number' => $mawb->mawb_number,
                        'current_location' => $eventLocation,
                        'agency_milestone' => $eventCode,
                    ]);
                });

                $this->notifySubscribers($shipment->tracking_number, $statusLabel, $eventDescription, $eventLocation);
                $updatedCount++;
            } catch (\Throwable $e) {
                Log::warning("MAWB cascade skipped shipment {$shipment->id}: " . $e->getMessage());
                $failedCount++;
            }
        }

        return [
            'total_shipments' => $shipments->count(),
            'updated_count' => $updatedCount,
            'failed_count' => $failedCount,
            'event_code' => $eventCode,
            'status_label' => $statusLabel,
            'location' => $eventLocation,
        ];
    }

    /**
     * 5. Record Tracking Subscription for Customer / Consignee
     */
    public function subscribe(string $trackingNumber, ?string $email = null, ?string $phone = null): TrackingSubscription
    {
        $trackingNumber = strtoupper(trim($trackingNumber));

        return TrackingSubscription::updateOrCreate(
            [
                'tracking_number' => $trackingNumber,
                'email' => $email ? strtolower(trim($email)) : null,
                'phone' => $phone ? trim($phone) : null,
            ],
            [
                'notify_email' => !empty($email),
                'notify_sms' => !empty($phone),
                'is_active' => true,
            ]
        );
    }

    /**
     * 6. Safely notify all active tracking subscribers for a tracking code
     */
    public function notifySubscribers(string $trackingNumber, string $milestoneLabel, string $description, ?string $location = null): int
    {
        if (empty($trackingNumber)) {
            return 0;
        }

        $trackingNumber = strtoupper(trim($trackingNumber));
        $subscriptions = TrackingSubscription::forTracking($trackingNumber)->active()->get();
        $notified = 0;

        foreach ($subscriptions as $sub) {
            if ($sub->notify_email && !empty($sub->email) && filter_var($sub->email, FILTER_VALIDATE_EMAIL)) {
                try {
                    // Send notification if mail is configured
                    Mail::raw(
                        "COURIER with NETPACK Tracking Update\n\nTracking #: {$trackingNumber}\nStatus: {$milestoneLabel}\nLocation: " . ($location ?: 'In Transit') . "\nDetails: {$description}\nTime: " . now()->toDayDateTimeString() . "\n\nTrack online at: " . route('tracking.show', $trackingNumber),
                        function ($message) use ($sub, $trackingNumber, $milestoneLabel) {
                            $message->to($sub->email)
                                    ->subject("Tracking Update: {$trackingNumber} - {$milestoneLabel}");
                        }
                    );
                    $notified++;
                } catch (\Throwable $e) {
                    Log::info("Subscriber notification email skipped: " . $e->getMessage());
                }
            }
        }

        return $notified;
    }
}
