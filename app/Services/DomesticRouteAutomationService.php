<?php

namespace App\Services;

use App\Models\DomesticShipment;
use App\Models\DomesticTrackingEvent;
use App\Models\Manifest;
use App\Models\ManifestBag;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DomesticRouteAutomationService
{
    public function __construct(
        private readonly ShipmentScanService $scanService,
        private readonly AutomatedTrackingService $automatedTracking
    ) {
    }

    /**
     * When a domestic manifest or bag is dispatched along an inter-district highway corridor
     */
    public function handleBagDispatched(
        ManifestBag $bag,
        Manifest $manifest,
        ?string $vehicleNumber = null,
        ?User $actor = null
    ): array {
        $actor = $this->automatedTracking->getSystemActor($actor);
        $originCity = $manifest->origin_city ?: 'Kathmandu Central Sorting Hub';
        $destCity = $manifest->destination_city ?: 'Destination District Hub';
        $vehicleInfo = $vehicleNumber ? " (Fleet: {$vehicleNumber})" : '';

        $desc = "Consolidated Bag {$bag->bag_number} dispatched from {$originCity} en route to {$destCity}{$vehicleInfo}. Highway Express Transit active.";

        $manifestShipments = $manifest->shipments()->with('shipment')->get();
        $updated = 0;

        foreach ($manifestShipments as $line) {
            $shipment = $line->shipment;
            if (!$shipment) {
                // Try finding in DomesticShipment
                $domShipment = DomesticShipment::find($line->shipment_id);
                if ($domShipment) {
                    $this->advanceDomesticShipment(
                        $domShipment,
                        'in_transit',
                        $originCity,
                        $desc,
                        $actor
                    );
                    $updated++;
                }
                continue;
            }

            if (in_array($shipment->status, ['delivered', 'cancelled'])) {
                continue;
            }

            try {
                $this->scanService->record(
                    $shipment,
                    'origin_facility_departure',
                    $originCity,
                    $desc,
                    $actor,
                    'domestic_bag_dispatch_automation'
                );

                $this->automatedTracking->notifySubscribers(
                    $shipment->tracking_number,
                    'Dispatched on Highway Transit',
                    $desc,
                    $originCity
                );
                $updated++;
            } catch (\Throwable $e) {
                Log::warning("Domestic bag dispatch cascade error: " . $e->getMessage());
            }
        }

        $bag->update(['status' => 'in_transit']);

        return [
            'bag_number' => $bag->bag_number,
            'updated_count' => $updated,
            'origin' => $originCity,
            'destination' => $destCity,
        ];
    }

    /**
     * When a domestic manifest bag arrives at a receiving Nepal province/district hub
     */
    public function handleBagReceived(
        ManifestBag $bag,
        Manifest $manifest,
        string $receivingHub,
        ?User $actor = null
    ): array {
        $actor = $this->automatedTracking->getSystemActor($actor);
        $desc = "Consolidated Bag {$bag->bag_number} received at {$receivingHub}. Verified and sorted for last-mile rider distribution.";

        $manifestShipments = $manifest->shipments()->with('shipment')->get();
        $updated = 0;

        foreach ($manifestShipments as $line) {
            $shipment = $line->shipment;
            if (!$shipment) {
                $domShipment = DomesticShipment::find($line->shipment_id);
                if ($domShipment) {
                    $this->advanceDomesticShipment(
                        $domShipment,
                        'in_transit',
                        $receivingHub,
                        $desc,
                        $actor
                    );
                    $updated++;
                }
                continue;
            }

            if (in_array($shipment->status, ['delivered', 'cancelled'])) {
                continue;
            }

            try {
                $this->scanService->record(
                    $shipment,
                    'destination_facility_arrival',
                    $receivingHub,
                    $desc,
                    $actor,
                    'domestic_hub_arrival_automation'
                );

                $this->automatedTracking->notifySubscribers(
                    $shipment->tracking_number,
                    'Arrived at Destination Hub',
                    $desc,
                    $receivingHub
                );
                $updated++;
            } catch (\Throwable $e) {
                Log::warning("Domestic bag receive cascade error: " . $e->getMessage());
            }
        }

        $bag->update(['status' => 'received', 'destination_city' => $receivingHub]);

        return [
            'bag_number' => $bag->bag_number,
            'updated_count' => $updated,
            'receiving_hub' => $receivingHub,
        ];
    }

    /**
     * When a domestic shipment is assigned to a rider for final last-mile doorstep delivery
     */
    public function handleRiderOutForDelivery(
        Model $shipment,
        User $rider,
        ?string $deliveryZone = null
    ): Model {
        $actor = $this->automatedTracking->getSystemActor();
        $riderName = $rider->name ?? 'Delivery Rider';
        $phone = $rider->phone ? " ({$rider->phone})" : '';
        $zone = $deliveryZone ?: ($shipment->receiver_zone ?? ($shipment->receiver_city ?? 'Destination Ward'));

        $desc = "Out for delivery with rider {$riderName}{$phone}. Delivery scheduled for today.";

        if ($shipment instanceof DomesticShipment) {
            $this->advanceDomesticShipment(
                $shipment,
                'out_for_delivery',
                $zone,
                $desc,
                $actor
            );
        } else {
            $this->scanService->record(
                $shipment,
                'out_for_delivery',
                $zone,
                $desc,
                $actor,
                'rider_dispatch_automation'
            );
        }

        $this->automatedTracking->notifySubscribers(
            $shipment->tracking_number,
            'Out for Delivery',
            $desc,
            $zone
        );

        return $shipment;
    }

    /**
     * When a domestic shipment is verified as delivered
     */
    public function handleDeliveryCompleted(
        Model $shipment,
        ?string $receiverName = null,
        ?string $proofPhotoUrl = null,
        ?User $actor = null
    ): Model {
        $actor = $this->automatedTracking->getSystemActor($actor);
        $name = $receiverName ?: ($shipment->receiver_name ?: 'Consignee');
        $location = $shipment->receiver_city ?? 'Delivery Address';
        $desc = "Consignment delivered successfully. Signed by {$name}. Verified proof of delivery recorded.";

        if ($shipment instanceof DomesticShipment) {
            $this->advanceDomesticShipment(
                $shipment,
                'delivered',
                $location,
                $desc,
                $actor
            );
            $shipment->update([
                'actual_delivery_at' => now(),
                'proof_of_delivery' => $proofPhotoUrl ?: $shipment->proof_of_delivery,
            ]);
        } else {
            $this->scanService->record(
                $shipment,
                'delivered',
                $location,
                $desc,
                $actor,
                'delivery_completion_automation'
            );
            $shipment->update([
                'delivered_at' => now(),
                'proof_of_delivery' => $proofPhotoUrl ?: $shipment->proof_of_delivery,
            ]);
        }

        $this->automatedTracking->notifySubscribers(
            $shipment->tracking_number,
            'Delivered',
            $desc,
            $location
        );

        return $shipment;
    }

    /**
     * Advance a DomesticShipment instance with trackingEvents and tracking_history
     */
    protected function advanceDomesticShipment(
        DomesticShipment $shipment,
        string $newStatus,
        string $location,
        string $description,
        User $actor
    ): DomesticShipment {
        $eventData = [
            'event_code' => $newStatus,
            'status' => $newStatus,
            'status_label' => DomesticShipment::STATUS_LABELS[$newStatus] ?? ucfirst($newStatus),
            'icon' => match ($newStatus) {
                'out_for_delivery' => 'fa-motorcycle',
                'delivered' => 'fa-circle-check',
                'failed_delivery' => 'fa-triangle-exclamation',
                default => 'fa-truck-fast',
            },
            'description' => $description,
            'location' => $location,
            'time' => now()->toIso8601String(),
            'scan_source' => 'domestic_route_automation',
            'scanned_by_user_id' => $actor->id ?? 1,
        ];

        // Create relational event
        $shipment->trackingEvents()->create([
            'status' => $newStatus,
            'location' => $location,
            'description' => $description,
            'event_time' => now(),
            'additional_data' => $eventData,
        ]);

        // Append to json history
        $history = $shipment->tracking_history ?? [];
        $history[] = $eventData;

        $shipment->update([
            'status' => $newStatus,
            'tracking_history' => $history,
        ]);

        return $shipment;
    }
}
