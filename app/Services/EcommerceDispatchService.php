<?php

namespace App\Services;

use App\Models\DomesticPartner;
use App\Models\RiderJobOffer;
use App\Models\RiderProfile;
use App\Models\RiderRateRule;
use App\Models\ShipmentAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EcommerceDispatchService
{
    /**
     * Get or create active rider rate rule
     */
    public function getActiveRateRule(string $vehicleType = 'motorcycle'): RiderRateRule
    {
        return RiderRateRule::firstOrCreate(
            ['vehicle_type' => $vehicleType, 'is_active' => true],
            [
                'zone_name' => 'Kathmandu Valley',
                'base_distance_km' => 3.00,
                'base_rate' => 80.00,
                'additional_km_rate' => 15.00,
                'weight_surcharge_per_kg' => 10.00,
                'cod_handling_fee' => 10.00,
                'return_fee' => 60.00,
                'is_active' => true,
            ]
        );
    }

    /**
     * Create Direct Rider Delivery (Seller -> Rider -> Customer)
     */
    public function createDirectDelivery(User $seller, array $data): ShipmentAssignment
    {
        return DB::transaction(function () use ($seller, $data) {
            $masterAwb = 'NPK-ECOM-'.date('ymd').'-'.strtoupper(Str::random(5));
            $distanceKm = (float) ($data['distance_km'] ?? 5.00);
            $weightKg = (float) ($data['parcel_weight'] ?? 1.00);
            $codAmount = (float) ($data['cod_amount'] ?? 0.00);
            $vehicleType = $data['vehicle_type'] ?? 'motorcycle';

            $rateRule = $this->getActiveRateRule($vehicleType);
            $fee = $rateRule->calculateFee($distanceKm, $weightKg, $codAmount);

            $pickupOtp = ShipmentAssignment::generateOtp();
            $deliveryOtp = ShipmentAssignment::generateOtp();

            $assignment = ShipmentAssignment::create([
                'seller_id' => $seller->id,
                'master_awb' => $masterAwb,
                'assignment_type' => 'local_direct',
                'provider_type' => 'rider',
                'sequence' => 1,
                'pickup_name' => $data['pickup_name'] ?? $seller->business_name ?? $seller->name,
                'pickup_phone' => $data['pickup_phone'] ?? $seller->phone ?? '9800000000',
                'pickup_address' => $data['pickup_address'] ?? $seller->address ?? 'Kathmandu Hub',
                'pickup_lat' => $data['pickup_lat'] ?? 27.7172,
                'pickup_lng' => $data['pickup_lng'] ?? 85.3240,
                'delivery_name' => $data['delivery_name'],
                'delivery_phone' => $data['delivery_phone'],
                'delivery_address' => $data['delivery_address'],
                'delivery_lat' => $data['delivery_lat'] ?? null,
                'delivery_lng' => $data['delivery_lng'] ?? null,
                'distance_km' => $distanceKm,
                'parcel_weight' => $weightKg,
                'provider_fee' => $fee,
                'cod_amount' => $codAmount,
                'status' => 'assigned',
                'current_custody' => 'Seller ('.($seller->business_name ?? $seller->name).')',
                'pickup_otp' => $pickupOtp,
                'delivery_otp' => $deliveryOtp,
                'assigned_at' => now(),
            ]);

            // Broadcast offer to verified nearby riders
            $this->broadcastOffers($assignment);

            return $assignment;
        }, 3);
    }

    /**
     * Create Multi-Leg Hybrid Delivery (Seller -> First-Mile Rider -> Origin Hub -> Domestic Partner Linehaul -> Dest Hub -> Last-Mile Rider -> Customer)
     */
    public function createMultiLegDelivery(User $seller, array $data, ?DomesticPartner $partner = null): array
    {
        return DB::transaction(function () use ($seller, $data, $partner) {
            $masterAwb = 'NPK-HYB-'.date('ymd').'-'.strtoupper(Str::random(5));
            $weightKg = (float) ($data['parcel_weight'] ?? 1.00);
            $codAmount = (float) ($data['cod_amount'] ?? 0.00);
            $rateRule = $this->getActiveRateRule('motorcycle');

            $originHub = $data['origin_hub'] ?? 'Kathmandu Central Hub, Sinamangal';
            $destHub = $data['destination_hub'] ?? ($data['destination_city'].' Regional Hub');

            // Leg 1: First-Mile Pickup (Seller -> Origin Hub)
            $leg1Fee = $rateRule->calculateFee(4.0, $weightKg, 0);
            $leg1 = ShipmentAssignment::create([
                'seller_id' => $seller->id,
                'master_awb' => $masterAwb,
                'assignment_type' => 'pickup_first_mile',
                'provider_type' => 'rider',
                'sequence' => 1,
                'pickup_name' => $seller->business_name ?? $seller->name,
                'pickup_phone' => $seller->phone ?? '9800000000',
                'pickup_address' => $seller->address ?? 'Kathmandu',
                'delivery_name' => 'NETPACK Origin Sorting Gateway',
                'delivery_phone' => '01-4112233',
                'delivery_address' => $originHub,
                'distance_km' => 4.0,
                'parcel_weight' => $weightKg,
                'provider_fee' => $leg1Fee,
                'cod_amount' => 0.00,
                'status' => 'assigned',
                'current_custody' => 'Seller ('.($seller->business_name ?? $seller->name).')',
                'pickup_otp' => ShipmentAssignment::generateOtp(),
                'delivery_otp' => ShipmentAssignment::generateOtp(),
                'assigned_at' => now(),
            ]);
            $this->broadcastOffers($leg1);

            // Leg 2: Linehaul Transport (Origin Hub -> Destination Hub via Domestic Partner)
            $partnerId = $partner ? $partner->id : DomesticPartner::where('is_active', true)->value('id');
            if (! $partnerId || ! DomesticPartner::whereKey($partnerId)->where('is_active', true)->exists()) {
                throw ValidationException::withMessages(['partner_id' => 'An active linehaul partner is required.']);
            }
            $leg2 = ShipmentAssignment::create([
                'seller_id' => $seller->id,
                'master_awb' => $masterAwb,
                'assignment_type' => 'line_haul',
                'provider_type' => 'domestic_partner',
                'partner_id' => $partnerId,
                'sequence' => 2,
                'pickup_name' => 'NETPACK Origin Sorting Gateway',
                'pickup_phone' => '01-4112233',
                'pickup_address' => $originHub,
                'delivery_name' => 'NETPACK Destination Regional Gateway',
                'delivery_phone' => '061-520000',
                'delivery_address' => $destHub,
                'distance_km' => (float) ($data['intercity_distance_km'] ?? 200.0),
                'parcel_weight' => $weightKg,
                'provider_fee' => 150.00, // Partner linehaul contract rate
                'cod_amount' => 0.00,
                'status' => 'assigned',
                'current_custody' => 'Awaiting First-Mile Arrival at Origin Hub',
                'pickup_otp' => ShipmentAssignment::generateOtp(),
                'delivery_otp' => ShipmentAssignment::generateOtp(),
                'assigned_at' => now(),
            ]);

            // Leg 3: Last-Mile Delivery (Destination Hub -> Customer with COD)
            $leg3Fee = $rateRule->calculateFee(5.0, $weightKg, $codAmount);
            $leg3 = ShipmentAssignment::create([
                'seller_id' => $seller->id,
                'master_awb' => $masterAwb,
                'assignment_type' => 'last_mile',
                'provider_type' => 'rider',
                'sequence' => 3,
                'pickup_name' => 'NETPACK Destination Regional Gateway',
                'pickup_phone' => '061-520000',
                'pickup_address' => $destHub,
                'delivery_name' => $data['delivery_name'],
                'delivery_phone' => $data['delivery_phone'],
                'delivery_address' => $data['delivery_address'],
                'distance_km' => 5.0,
                'parcel_weight' => $weightKg,
                'provider_fee' => $leg3Fee,
                'cod_amount' => $codAmount,
                'status' => 'assigned',
                'current_custody' => 'Awaiting Linehaul Arrival at Regional Hub',
                'pickup_otp' => ShipmentAssignment::generateOtp(),
                'delivery_otp' => ShipmentAssignment::generateOtp(),
                'assigned_at' => now(),
            ]);

            return [
                'master_awb' => $masterAwb,
                'leg_1' => $leg1,
                'leg_2' => $leg2,
                'leg_3' => $leg3,
            ];
        }, 3);
    }

    /**
     * Broadcast job offers to eligible verified riders with smart capacity & service-area checks
     */
    public function broadcastOffers(ShipmentAssignment $assignment): int
    {
        $parcelWeight = (float) $assignment->parcel_weight;
        $codAmount = (float) $assignment->cod_amount;

        // Find verified riders who can accept COD and carry parcel weight
        $riders = RiderProfile::where('verification_status', 'verified')
            ->get()
            ->filter(function (RiderProfile $rider) use ($parcelWeight, $codAmount, $assignment) {
                // Check general capacity and COD headroom
                if (! $rider->isAvailableForDelivery($parcelWeight, $codAmount)) {
                    return false;
                }

                // Check service area if defined
                return $rider->coversArea(null, null, $assignment->pickup_address);
            });

        $count = 0;
        foreach ($riders as $rider) {
            RiderJobOffer::create([
                'assignment_id' => $assignment->id,
                'rider_profile_id' => $rider->id,
                'offered_amount' => $assignment->provider_fee,
                'offered_at' => now(),
                'expires_at' => now()->addMinutes(30),
                'response' => 'pending',
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Method A: Dispatcher / Admin direct assignment of a specific qualified rider
     */
    public function directAssignRider(ShipmentAssignment $assignment, RiderProfile $rider): array
    {
        return $this->claimAssignment($rider, $assignment->id, true);
    }

    /**
     * Accept a job offer by a rider (Method B: Marketplace)
     */
    public function acceptJob(RiderProfile $rider, int $assignmentId): array
    {
        return $this->claimAssignment($rider, $assignmentId, false);
    }

    private function claimAssignment(RiderProfile $rider, int $assignmentId, bool $direct): array
    {
        return DB::transaction(function () use ($rider, $assignmentId, $direct) {
            $assignment = ShipmentAssignment::lockForUpdate()->findOrFail($assignmentId);
            $rider = RiderProfile::lockForUpdate()->findOrFail($rider->id);
            if ($assignment->rider_profile_id) {
                if ((int) $assignment->rider_profile_id === (int) $rider->id
                    && in_array($assignment->status, ['assigned', 'accepted', 'arrived_pickup', 'picked_up', 'in_transit'])) {
                    return ['success' => true, 'message' => 'Job already assigned to you.', 'assignment' => $assignment];
                }

                return ['success' => false, 'message' => 'This delivery job is already claimed or closed.'];
            }
            if ($assignment->status !== 'assigned' || $assignment->provider_type !== 'rider') {
                return ['success' => false, 'message' => 'This delivery leg is not available for rider assignment.'];
            }
            if ($assignment->sequence > 1 && $assignment->assignment_type !== 'return_rto'
                && ShipmentAssignment::where('master_awb', $assignment->master_awb)
                    ->where('sequence', '<', $assignment->sequence)->where('status', '!=', 'completed')->exists()) {
                return ['success' => false, 'message' => 'Previous delivery legs must be completed first.'];
            }
            $reservedCod = $rider->assignments()->whereIn('status', ['assigned', 'accepted', 'arrived_pickup', 'picked_up', 'in_transit'])->sum('cod_amount');
            if (! $rider->isVerified() || (! $direct && ! $rider->isOnline())
                || $rider->current_active_packages >= $rider->max_active_packages
                || $assignment->parcel_weight > $rider->max_carrying_weight
                || ! $rider->coversArea(null, null, $assignment->pickup_address)
                || ($assignment->cod_amount > 0 && $rider->current_outstanding_cod + $reservedCod + $assignment->cod_amount > $rider->cod_limit)) {
                return ['success' => false, 'message' => 'Rider is not eligible: check verification, availability, coverage, capacity and COD limit.'];
            }
            $assignment->update([
                'rider_profile_id' => $rider->id,
                'status' => $direct ? 'assigned' : 'accepted',
                'assigned_at' => now(), 'accepted_at' => $direct ? null : now(),
                'current_custody' => ($direct ? 'Directly Assigned: ' : 'Rider Assigned: ').$rider->full_name,
            ]);
            $rider->increment('current_active_packages');
            if ($rider->current_active_packages >= $rider->max_active_packages) {
                $rider->update(['availability_status' => 'busy']);
            }
            RiderJobOffer::where('assignment_id', $assignmentId)->where('rider_profile_id', $rider->id)
                ->update(['response' => 'accepted', 'responded_at' => now()]);
            RiderJobOffer::where('assignment_id', $assignmentId)->where('rider_profile_id', '!=', $rider->id)
                ->update(['response' => 'expired']);

            return ['success' => true, 'message' => 'Job successfully assigned. Please proceed to pickup.', 'assignment' => $assignment];
        }, 3);
    }

    /**
     * Rider arrives at pickup location
     */
    public function arriveAtPickup(ShipmentAssignment $assignment): bool
    {
        return DB::transaction(function () use ($assignment) {
            $assignment = ShipmentAssignment::lockForUpdate()->findOrFail($assignment->id);
            if (!in_array($assignment->status, ['assigned', 'accepted'], true)) { return false; }
            return $assignment->update(['status' => 'arrived_pickup', 'arrived_pickup_at' => now()]);
        }, 3);
    }

    /**
     * Verify pickup OTP and transfer custody to Rider
     */
    public function verifyPickupOtp(ShipmentAssignment $assignment, string $otp): array
    {
        if ($assignment->verifyPickupOtp($otp)) {
            return [
                'success' => true,
                'message' => 'Pickup OTP verified! Parcel is now in your custody. Proceed to delivery.',
                'assignment' => $assignment->fresh(),
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid Pickup OTP. Please ask the seller to provide the correct 6-digit OTP.',
        ];
    }

    /**
     * Complete delivery with Customer OTP & COD collection + Sequence Progression
     */
    public function completeDelivery(
        ShipmentAssignment $assignment,
        string $otp,
        ?string $recipientName = null,
        ?string $podPhoto = null
    ): array {
        return DB::transaction(function () use ($assignment, $otp, $recipientName, $podPhoto) {
            $assignment = ShipmentAssignment::lockForUpdate()->findOrFail($assignment->id);

            if ($assignment->completeDelivery($otp, $recipientName, $podPhoto)) {
                $freshAssignment = $assignment->fresh();
                $rider = $freshAssignment->riderProfile;

                if ($rider) {
                    // Capacity decrement & availability restoration
                    $rider->update(['current_active_packages' => max(0, $rider->current_active_packages - 1)]);
                    if ($rider->availability_status === 'busy' && $rider->current_active_packages < $rider->max_active_packages) {
                        $rider->update(['availability_status' => 'online']);
                    }
                    $rider->recalculateTrustScore();
                }

                // Multi-Leg Sequence Progression
                $this->advanceMultiLegProgression($freshAssignment);

                return [
                    'success' => true,
                    'message' => 'Delivery successfully verified and completed! Earnings credited.',
                    'assignment' => $freshAssignment,
                ];
            }

            return [
                'success' => false,
                'message' => 'Invalid Delivery OTP. Please verify the 6-digit OTP with the customer.',
            ];
        }, 3);
    }

    /**
     * Automatically advance subsequent legs in multi-leg hybrid delivery
     */
    protected function advanceMultiLegProgression(ShipmentAssignment $completedAssignment): void
    {
        $masterAwb = $completedAssignment->master_awb;

        // If Leg 1 (first mile pickup) is completed, advance Leg 2 (line-haul)
        if ($completedAssignment->assignment_type === 'pickup_first_mile') {
            $leg2 = ShipmentAssignment::where('master_awb', $masterAwb)
                ->where('sequence', 2)
                ->first();

            if ($leg2 && $leg2->status === 'assigned') {
                $leg2->update([
                    'current_custody' => 'NETPACK Origin Sorting Gateway (Handed over from First-Mile Rider)',
                    'status' => 'arrived_pickup',
                ]);
            }
        }

        // If Leg 2 (line-haul) is completed, activate Leg 3 (last-mile) and broadcast to destination riders
        if ($completedAssignment->assignment_type === 'line_haul') {
            $leg3 = ShipmentAssignment::where('master_awb', $masterAwb)
                ->where('sequence', 3)
                ->first();

            if ($leg3 && $leg3->status === 'assigned') {
                $leg3->update([
                    'current_custody' => 'NETPACK Destination Regional Gateway (Ready for Local Last-Mile Rider Dispatch)',
                ]);
                $this->broadcastOffers($leg3);
            }
        }
    }

    public function recordLinehaulHandover(int $id, string $event, User $actor, string $location, string $notes): void
    {
        abort_unless(in_array($actor->user_type, ['super_admin', 'admin', 'domestic_admin'], true), 403);
        DB::transaction(function () use ($id, $event, $actor, $location, $notes) {
            $leg = ShipmentAssignment::lockForUpdate()->findOrFail($id);
            abort_unless($leg->assignment_type === 'line_haul', 422);
            abort_unless(ShipmentAssignment::where('master_awb', $leg->master_awb)
                ->where('sequence', 1)->where('status', 'completed')->exists(), 422, 'Origin pickup must be completed first.');
            $expected = $event === 'dispatched' ? 'arrived_pickup' : 'in_transit';
            abort_unless(in_array($event, ['dispatched', 'received'], true) && $leg->status === $expected, 422, 'Invalid or repeated linehaul transition.');
            $leg->update([
                'status' => $event === 'dispatched' ? 'in_transit' : 'completed',
                'current_custody' => $location,
                'delivered_at' => $event === 'received' ? now() : null,
            ]);
            DB::table('assignment_handover_events')->insert([
                'assignment_id' => $leg->id, 'actor_id' => $actor->id, 'event' => $event,
                'location' => $location, 'notes' => $notes, 'created_at' => now(), 'updated_at' => now(),
            ]);
            if ($event === 'received') { $this->advanceMultiLegProgression($leg); }
        }, 3);
    }

    /**
     * Record delivery failure / exception & create automatic RTO Return Assignment
     */
    public function recordFailure(ShipmentAssignment $assignment, string $reason, ?string $notes = null, ?string $photo = null): bool
    {
        return DB::transaction(function () use ($assignment, $reason, $notes, $photo) {
            $assignment = ShipmentAssignment::lockForUpdate()->findOrFail($assignment->id);

            if (! in_array($assignment->status, ['picked_up', 'in_transit'], true)) {
                return false;
            }
            $updated = $assignment->update([
                'status' => 'failed',
                'failure_reason' => $reason,
                'failure_notes' => $notes,
                'failure_photo_path' => $photo,
                'failed_at' => now(),
                'current_custody' => "Delivery Failed: {$reason} - Scheduled for Return to Origin",
            ]);

            $rider = $assignment->rider_profile_id ? RiderProfile::lockForUpdate()->findOrFail($assignment->rider_profile_id) : null;
            if ($rider) {
                $rider->increment('total_failed_deliveries');
                $rider->update(['current_active_packages' => max(0, $rider->current_active_packages - 1)]);
                if ($rider->availability_status === 'busy' && $rider->current_active_packages < $rider->max_active_packages) {
                    $rider->update(['availability_status' => 'online']);
                }
                $rider->recalculateTrustScore();
            }

            // Section 58: Automatically create RTO Return Job
            $this->createRtoAssignment($assignment, $reason, $notes);
            if ($rider) {
                $rider->increment('current_active_packages');
            }

            return $updated;
        }, 3);
    }

    /**
     * Create dedicated RTO Return Assignment (Customer/Hub -> Seller)
     */
    public function createRtoAssignment(ShipmentAssignment $failedAssignment, string $reason, ?string $notes = null): ?ShipmentAssignment
    {
        $existingRto = ShipmentAssignment::where('master_awb', $failedAssignment->master_awb.'-RTO')->first();
        if ($existingRto) {
            return $existingRto;
        }

        $rateRule = $this->getActiveRateRule();
        $returnFee = (float) ($rateRule->return_fee ?? 60.00);

        return ShipmentAssignment::create([
            'seller_id' => $failedAssignment->seller_id,
            'master_awb' => $failedAssignment->master_awb.'-RTO',
            'assignment_type' => 'return_rto',
            'provider_type' => 'rider',
            'rider_profile_id' => $failedAssignment->rider_profile_id, // Assigned to same rider for return or broadcast
            'sequence' => ($failedAssignment->sequence ?? 1) + 10,
            'pickup_name' => $failedAssignment->delivery_name,
            'pickup_phone' => $failedAssignment->delivery_phone,
            'pickup_address' => $failedAssignment->delivery_address,
            'delivery_name' => $failedAssignment->pickup_name,
            'delivery_phone' => $failedAssignment->pickup_phone,
            'delivery_address' => $failedAssignment->pickup_address,
            'distance_km' => $failedAssignment->distance_km,
            'parcel_weight' => $failedAssignment->parcel_weight,
            'provider_fee' => $returnFee,
            'cod_amount' => 0.00, // No COD on returns
            'status' => 'picked_up',
            'picked_up_at' => now(),
            'pickup_otp_verified_at' => now(),
            'current_custody' => "Return to Seller in Progress ({$reason})",
            'pickup_otp' => ShipmentAssignment::generateOtp(),
            'delivery_otp' => ShipmentAssignment::generateOtp(),
            'failure_reason' => $reason,
            'failure_notes' => $notes,
            'assigned_at' => now(),
        ]);
    }
}
