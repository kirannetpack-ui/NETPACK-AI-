<?php

namespace App\Services;

use App\Mail\AgencyManifestDispatchMail;
use App\Models\Agency;
use App\Models\MAWB;
use App\Models\Manifest;
use App\Models\ManifestBag;
use App\Models\ManifestShipment;
use App\Models\OverseasHub;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class InternationalManifestService
{
    /**
     * Get international shipments that are available for manifesting
     */
    public function getEligibleShipments(?int $hubId = null, ?int $agencyId = null, ?string $serviceType = null)
    {
        $query = Shipment::query()
            ->where('shipment_type', 'international')
            ->whereDoesntHave('manifestShipment')
            ->whereNotIn('status', ['delivered', 'cancelled', 'returned']);

        if ($hubId) {
            $query->where(function ($q) use ($hubId) {
                $q->where('current_hub_id', $hubId)
                  ->orWhereNull('current_hub_id');
            });
        }

        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get all unused MAWBs available for assignment
     */
    public function getUnusedMawbs(?int $hubId = null)
    {
        $query = MAWB::unused();

        if ($hubId) {
            $query->where(function ($q) use ($hubId) {
                $q->where('hub_id', $hubId)->orWhereNull('hub_id');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Automatically create an international manifest and bind to an unused MAWB
     */
    public function createManifest(array $data, array $shipmentIds, User $creator): Manifest
    {
        if (empty($shipmentIds)) {
            throw ValidationException::withMessages(['shipment_ids' => 'Please select at least one international shipment for the manifest.']);
        }

        return DB::transaction(function () use ($data, $shipmentIds, $creator) {
            $mawb = null;
            if (!empty($data['mawb_id'])) {
                $mawb = MAWB::lockForUpdate()->findOrFail($data['mawb_id']);
                if ($mawb->status !== 'unused' && $mawb->assigned_manifest_id) {
                    throw ValidationException::withMessages(['mawb_id' => 'The selected MAWB has already been assigned to another manifest.']);
                }
            }

            $hub = !empty($data['hub_id']) ? OverseasHub::find($data['hub_id']) : null;
            $agency = !empty($data['agency_id']) ? Agency::find($data['agency_id']) : null;

            // Prevent double manifesting
            $alreadyManifested = ManifestShipment::whereIn('shipment_id', $shipmentIds)->exists();
            if ($alreadyManifested) {
                throw ValidationException::withMessages(['shipment_ids' => 'One or more selected shipments are already assigned to an active manifest.']);
            }

            $shipments = Shipment::whereIn('id', $shipmentIds)->get();
            $totalWeight = $shipments->sum(fn ($s) => $s->actual_weight ?: $s->chargeable_weight);
            $totalPieces = $shipments->count();

            $manifestNumber = Manifest::generateManifestNumber('international');

            $manifest = Manifest::create([
                'manifest_number' => $manifestNumber,
                'created_by' => $creator->id,
                'partner_id' => $hub?->partner_id,
                'mawb_id' => $mawb?->id,
                'mawb_number' => $mawb?->mawb_number,
                'hub_id' => $hub?->id,
                'agency_id' => $agency?->id,
                'service_type' => $data['service_type'] ?? 'economy',
                'manifest_type' => 'international',
                'flight_number' => $data['flight_number'] ?? $mawb?->flight_number,
                'flight_date' => $data['flight_date'] ?? $mawb?->flight_date,
                'load_type' => 'consolidated',
                'status' => 'pending',
                'origin_city' => 'Kathmandu (KTM Gateway)',
                'destination_city' => $hub?->location ?? ($agency?->city ?? 'International Hub'),
                'total_bags' => 1,
                'total_shipments' => $totalPieces,
                'total_weight' => $totalWeight,
                'metadata' => [
                    'hub_code' => $hub?->hub_code,
                    'agency_code' => $agency?->code,
                    'airline_name' => $mawb?->airline_name,
                    'airline_code' => $mawb?->airline_code,
                    'service_route' => $hub?->service_routes,
                    'notes' => $data['notes'] ?? null,
                ],
            ]);

            // Create default consolidated bag
            $bag = ManifestBag::create([
                'manifest_id' => $manifest->id,
                'bag_number' => ManifestBag::generateBagNumber(),
                'qr_code' => ManifestBag::generateQRCode(),
                'bag_type' => 'consolidated',
                'shipment_count' => $totalPieces,
                'weight' => $totalWeight,
                'seal_number' => 'SEAL-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'status' => 'sealed',
                'origin_city' => 'Kathmandu',
                'destination_city' => $manifest->destination_city,
            ]);

            // Attach shipments and update shipment tracking
            foreach ($shipments as $shipment) {
                ManifestShipment::create([
                    'manifest_id' => $manifest->id,
                    'bag_id' => $bag->id,
                    'shipment_id' => $shipment->id,
                    'partner_id' => $hub?->partner_id,
                    'status' => 'pending',
                    'arrival_status' => 'pending',
                    'delivery_type' => 'door_delivery',
                ]);

                // Update shipment linkage
                $shipmentUpdates = [
                    'mawb_id' => $mawb?->id,
                    'mawb_number' => $mawb?->mawb_number,
                    'current_hub_id' => $hub?->id ?: $shipment->current_hub_id,
                    'current_agency_id' => $agency?->id ?: $shipment->current_agency_id,
                    'status' => 'in_transit',
                    'agency_milestone' => 'airline_departed',
                ];

                if (!empty($data['last_mile_carrier_name'])) {
                    $shipmentUpdates['last_mile_carrier_name'] = $data['last_mile_carrier_name'];
                }

                $shipment->update($shipmentUpdates);

                // Add timeline record
                $shipment->addTimeline(
                    'manifested',
                    "Assigned to Manifest {$manifestNumber} under Master Air Waybill {$mawb?->mawb_number}.",
                    'TIA International Cargo Terminal, Kathmandu'
                );
            }

            // Update MAWB status to assigned
            if ($mawb) {
                $mawb->update([
                    'status' => 'assigned',
                    'assigned_manifest_id' => $manifest->id,
                    'total_pieces' => $totalPieces,
                    'total_weight' => $totalWeight,
                    'flight_number' => $data['flight_number'] ?? $mawb->flight_number,
                    'flight_date' => $data['flight_date'] ?? $mawb->flight_date,
                ]);
            }

            $manifest->addTrackingLog(
                'created',
                "International Manifest {$manifestNumber} created with {$totalPieces} consignments bound to MAWB {$mawb?->mawb_number}.",
                'TIA Cargo Terminal, Kathmandu'
            );

            return $manifest->load(['mawb', 'hub', 'agency', 'shipments.shipment']);
        });
    }

    /**
     * Dispatch Manifest & Data Sheet to Agency Pre-defined Emails
     */
    public function dispatchToAgencyEmails(Manifest $manifest, ?string $customNotes = null): array
    {
        $agency = $manifest->agency;
        if (!$agency) {
            throw ValidationException::withMessages(['agency' => 'No agency is linked to this manifest.']);
        }

        $emails = $agency->getAllNotificationEmails();
        if (empty($emails)) {
            throw ValidationException::withMessages(['agency' => "Agency '{$agency->name}' has no pre-defined email addresses configured."]);
        }

        try {
            Mail::to($emails)->send(new AgencyManifestDispatchMail(
                $manifest,
                $agency,
                $manifest->flight_number,
                $manifest->flight_date?->format('Y-m-d'),
                $customNotes
            ));

            $manifest->update([
                'agency_emails_sent_at' => now(),
                'agency_emails_sent_to' => $emails,
            ]);

            return [
                'success' => true,
                'sent_to' => $emails,
                'count' => count($emails),
                'sent_at' => now()->toDateTimeString(),
            ];
        } catch (\Throwable $e) {
            Log::error('Failed sending agency manifest email: ' . $e->getMessage());
            throw ValidationException::withMessages(['mail' => 'Unable to transmit dispatch email: ' . $e->getMessage()]);
        }
    }

    /**
     * Process Inbound Manifest Arrival Notice by Agency Staff
     * Supports:
     * 1. Whole Manifest Arrival
     * 2. Partial Arrival Selection
     * 3. Non-Arrival Packet Remarks
     */
    public function processArrivalNotice(
        Manifest $manifest,
        bool $isWholeManifest,
        array $arrivedShipmentIds,
        array $nonArrivalRemarks,
        ?string $facilityLocation,
        ?string $timestamp,
        ?User $staffUser = null
    ): array {
        return DB::transaction(function () use (
            $manifest,
            $isWholeManifest,
            $arrivedShipmentIds,
            $nonArrivalRemarks,
            $facilityLocation,
            $timestamp,
            $staffUser
        ) {
            $arrivedAt = $timestamp ? \Carbon\Carbon::parse($timestamp) : now();
            $location = $facilityLocation ?: ($manifest->destination_city ?: 'Destination Airport Hub');
            $staffName = $staffUser?->name ?? 'Hub Operations Staff';
            $staffId = $staffUser?->id;

            $manifestLines = $manifest->shipments()->with('shipment')->get();
            $arrivedCount = 0;
            $missingCount = 0;

            foreach ($manifestLines as $line) {
                $shipment = $line->shipment;
                $shipmentId = $line->shipment_id;

                $hasArrived = $isWholeManifest || in_array($shipmentId, $arrivedShipmentIds, false) || in_array((string)$shipmentId, $arrivedShipmentIds, true);

                if ($hasArrived) {
                    $line->update([
                        'arrival_status' => 'arrived',
                        'arrived_at' => $arrivedAt,
                        'arrived_location' => $location,
                        'non_arrival_remarks' => null,
                        'scanned_by_staff_id' => $staffId,
                        'staff_name' => $staffName,
                        'status' => 'received',
                    ]);

                    if ($shipment) {
                        $shipment->update([
                            'arrived_at_agency' => $arrivedAt,
                            'current_location' => $location,
                            'agency_milestone' => 'arrival_notice',
                            'status' => 'in_transit',
                        ]);

                        $shipment->addTimeline(
                            'arrival_notice',
                            "Flight arrived at hub. Consignment received and arrival notice verified by {$staffName}.",
                            $location
                        );
                    }
                    $arrivedCount++;
                } else {
                    // Non-arrival / short-landed packet
                    $remark = $nonArrivalRemarks[$shipmentId] ?? 'Package not found in physical flight breakdown (Short-landed exception).';

                    $line->update([
                        'arrival_status' => 'not_arrived',
                        'arrived_at' => null,
                        'arrived_location' => $location,
                        'non_arrival_remarks' => $remark,
                        'scanned_by_staff_id' => $staffId,
                        'staff_name' => $staffName,
                        'status' => 'pending',
                    ]);

                    if ($shipment) {
                        $shipment->update([
                            'agency_milestone' => 'non_arrival_exception',
                            'status_notes' => 'Non-arrival notice: ' . $remark,
                        ]);

                        $shipment->addTimeline(
                            'non_arrival_exception',
                            "Non-arrival notice reported by {$staffName}: {$remark}",
                            $location
                        );
                    }
                    $missingCount++;
                }
            }

            // Update manifest overall status
            $manifest->update([
                'received_at' => $arrivedAt,
                'status' => $missingCount === 0 ? 'received' : 'in_transit',
                'current_location' => $location,
            ]);

            // Update MAWB if present
            if ($manifest->mawb) {
                $manifest->mawb->update(['status' => 'cleared']);
            }

            $manifest->addTrackingLog(
                'arrival_notice',
                "Arrival notice confirmed by {$staffName}. Arrived: {$arrivedCount} packages, Non-arrived exceptions: {$missingCount}.",
                $location
            );

            return [
                'arrived_count' => $arrivedCount,
                'missing_count' => $missingCount,
                'location' => $location,
                'timestamp' => $arrivedAt->toDateTimeString(),
            ];
        });
    }

    /**
     * Generate Comprehensive Data Sheet Rows containing all entered fields
     */
    public function generateDataSheet(Manifest $manifest): array
    {
        $rows = [];
        $shipments = $manifest->shipments()->with('shipment')->get();

        foreach ($shipments as $index => $line) {
            $s = $line->shipment;
            if (!$s) continue;

            $rows[] = [
                'seq' => $index + 1,
                'hawb_number' => $s->hawb_number ?: $s->tracking_number,
                'tracking_number' => $s->tracking_number,
                'mawb_number' => $manifest->mawb_number ?: ($manifest->mawb?->mawb_number ?: 'N/A'),
                'flight_number' => $manifest->flight_number ?: ($manifest->mawb?->flight_number ?: 'N/A'),
                'flight_date' => $manifest->flight_date?->format('Y-m-d') ?: ($manifest->mawb?->flight_date?->format('Y-m-d') ?: 'N/A'),
                'service_type' => strtoupper($s->service_type ?? 'Economy'),
                'package_type' => ucfirst($s->package_type ?? 'Parcel'),
                'customs_mode' => $s->customs_mode ?: 'DDP',
                
                // Shipper details
                'sender_name' => $s->sender_name,
                'sender_phone' => $s->sender_phone,
                'sender_address' => $s->sender_address,
                'sender_city' => $s->sender_city ?: 'Kathmandu',
                'sender_country' => $s->sender_country ?: 'Nepal',

                // Consignee details
                'receiver_name' => $s->receiver_name,
                'receiver_phone' => $s->receiver_phone,
                'receiver_address' => $s->receiver_address,
                'receiver_city' => $s->receiver_city,
                'receiver_state' => $s->receiver_state,
                'receiver_postal_code' => $s->receiver_postal_code,
                'receiver_country' => $s->receiver_country,
                'receiver_tax_id' => $s->receiver_tax_id ?: 'N/A',

                // Dimensions & Weights
                'actual_weight' => number_format($s->actual_weight ?? 0, 2),
                'chargeable_weight' => number_format($s->chargeable_weight ?? $s->actual_weight ?? 0, 2),
                'length' => $s->length ?: '-',
                'width' => $s->width ?: '-',
                'height' => $s->height ?: '-',
                'dimensions' => ($s->length && $s->width && $s->height) ? "{$s->length}x{$s->width}x{$s->height} cm" : 'Standard',
                
                // Contents & Destination Agent
                'description' => $s->description ?: 'Export Commercial Goods',
                'last_mile_carrier_name' => $s->last_mile_carrier_name ?: ($manifest->hub?->hub_code === 'DXB' ? 'Canpar / Obibox / UPS' : 'Local Courier'),
                'last_mile_tracking_number' => $s->last_mile_tracking_number ?: 'Pending Dispatch',
                'booking_date' => $s->created_at->format('Y-m-d H:i'),
                'arrival_status' => ucfirst($line->arrival_status ?? 'Pending'),
                'non_arrival_remarks' => $line->non_arrival_remarks ?: '',
            ];
        }

        return $rows;
    }
}
