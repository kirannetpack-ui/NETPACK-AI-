<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ShipmentAssignment extends Model
{
    use HasFactory;

    protected $hidden = ['pickup_otp', 'delivery_otp'];

    protected $fillable = [
        'master_awb',
        'seller_id',
        'shipment_id',
        'domestic_shipment_id',
        'order_id',
        'assignment_type',
        'provider_type',
        'rider_profile_id',
        'partner_id',
        'sequence',
        'pickup_name',
        'pickup_phone',
        'pickup_address',
        'pickup_lat',
        'pickup_lng',
        'delivery_name',
        'delivery_phone',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'distance_km',
        'parcel_weight',
        'provider_fee',
        'cod_amount',
        'status',
        'current_custody',
        'pickup_otp',
        'pickup_otp_verified_at',
        'delivery_otp',
        'delivery_otp_verified_at',
        'pod_recipient_name',
        'pod_photo_path',
        'pod_signature_path',
        'pod_lat',
        'pod_lng',
        'failure_reason',
        'failure_notes',
        'failure_photo_path',
        'assigned_at',
        'accepted_at',
        'arrived_pickup_at',
        'picked_up_at',
        'delivered_at',
        'failed_at',
    ];

    protected $casts = [
        'distance_km' => 'float',
        'parcel_weight' => 'float',
        'provider_fee' => 'float',
        'cod_amount' => 'float',
        'pickup_otp_verified_at' => 'datetime',
        'delivery_otp_verified_at' => 'datetime',
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'arrived_pickup_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function riderProfile(): BelongsTo
    {
        return $this->belongsTo(RiderProfile::class, 'rider_profile_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(DomesticPartner::class, 'partner_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function domesticShipment(): BelongsTo
    {
        return $this->belongsTo(DomesticShipment::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(RiderJobOffer::class, 'assignment_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(RiderRating::class, 'assignment_id');
    }

    /**
     * Generate secure 6-digit OTPs for pickup and delivery
     */
    public static function generateOtp(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if assignment is direct from seller to customer
     */
    public function isDirect(): bool
    {
        return $this->assignment_type === 'local_direct';
    }

    /**
     * Verify pickup OTP and update custody
     */
    public function verifyPickupOtp(string $otp): bool
    {
        return DB::transaction(function () use ($otp) {
            $locked = self::lockForUpdate()->findOrFail($this->id);
            $this->setRawAttributes($locked->getAttributes(), true);
            if (! in_array($this->status, ['assigned', 'accepted', 'arrived_pickup'], true) || ! $this->rider_profile_id) {
                return false;
            }
            if (hash_equals((string) $this->pickup_otp, trim($otp))) {
                $this->update([
                    'pickup_otp_verified_at' => now(),
                    'status' => 'picked_up',
                    'picked_up_at' => now(),
                    'current_custody' => $this->riderProfile
                        ? "Rider: {$this->riderProfile->full_name} ({$this->riderProfile->rider_code})"
                        : 'In Transit',
                ]);

                return true;
            }

            return false;
        }, 3);
    }

    /**
     * Verify delivery OTP, record COD, record Rider Earnings, and complete assignment
     */
    public function completeDelivery(string $otp, ?string $recipientName = null, ?string $podPhoto = null): bool
    {
        return DB::transaction(function () use ($otp, $recipientName, $podPhoto) {
            $locked = self::lockForUpdate()->findOrFail($this->id);
            $this->setRawAttributes($locked->getAttributes(), true);
            if (! in_array($this->status, ['picked_up', 'in_transit'], true) || ! $this->pickup_otp_verified_at || ! $this->rider_profile_id) {
                return false;
            }
            $this->setRelation('riderProfile', RiderProfile::lockForUpdate()->findOrFail($this->rider_profile_id));
            if (hash_equals((string) $this->delivery_otp, trim($otp))) {
                $this->update([
                    'delivery_otp_verified_at' => now(),
                    'status' => 'completed',
                    'delivered_at' => now(),
                    'current_custody' => 'Delivered to Recipient',
                    'pod_recipient_name' => $recipientName ?: $this->delivery_name,
                    'pod_photo_path' => $podPhoto,
                ]);

                // If COD collection, record into rider's COD ledger
                if ($this->cod_amount > 0 && $this->riderProfile) {
                    $this->riderProfile->recordCodCollection(
                        $this->cod_amount,
                        $this->id,
                        "COD collected for Master AWB {$this->master_awb}"
                    );
                }

                // Record rider payout earnings
                if ($this->provider_fee > 0 && $this->riderProfile) {
                    $this->riderProfile->recordEarnings(
                        $this->provider_fee,
                        'delivery_fee',
                        $this->id,
                        "Earnings for delivering {$this->master_awb}"
                    );
                    $this->riderProfile->increment('total_completed_deliveries');
                }

                return true;
            }

            return false;
        }, 3);
    }
}
