<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RiderProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rider_code',
        'full_name',
        'mobile',
        'email',
        'dob',
        'gender',
        'emergency_contact',
        'address',
        'province',
        'district',
        'municipality',
        'ward',
        'citizenship_number',
        'citizenship_front_path',
        'citizenship_back_path',
        'profile_photo_path',
        'selfie_photo_path',
        'verification_status',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'vehicle_type',
        'vehicle_number',
        'vehicle_registration_doc_path',
        'driving_license_number',
        'driving_license_doc_path',
        'license_expiry_date',
        'has_other_platform_affiliation',
        'affiliation',
        'affiliation_reference_id',
        'affiliation_notes',
        'availability_status',
        'service_radius_km',
        'max_carrying_weight',
        'max_active_packages',
        'current_active_packages',
        'current_latitude',
        'current_longitude',
        'last_location_updated_at',
        'cod_level',
        'cod_limit',
        'current_outstanding_cod',
        'trust_score',
        'badge_status',
        'rating',
        'total_ratings_count',
        'total_completed_deliveries',
        'total_failed_deliveries',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'banking_qr_path',
        'esewa_id',
        'khalti_id',
        'agreement_accepted',
        'agreement_accepted_at',
    ];

    protected static function booted()
    {
        static::creating(function ($profile) {
            if (empty($profile->rider_code)) {
                $profile->rider_code = 'RDR-'.date('Y').'-'.strtoupper(substr(uniqid(), -6));
            }
            if (empty($profile->full_name) && $profile->user_id) {
                $user = User::find($profile->user_id);
                if ($user) {
                    $profile->full_name = $user->name ?? 'Rider Fleet';
                    $profile->mobile = $profile->mobile ?: ($user->phone ?: '9800000000');
                    $profile->email = $profile->email ?: $user->email;
                }
            }
            if (empty($profile->full_name)) {
                $profile->full_name = 'Rider '.($profile->rider_code ?? 'Fleet');
            }
            if (empty($profile->mobile)) {
                $profile->mobile = '9800000000';
            }
            if (empty($profile->cod_level)) {
                $profile->cod_level = 'level_1';
            }
            if (! isset($profile->cod_limit)) {
                $profile->cod_limit = 0.00;
            }
            if (! isset($profile->current_outstanding_cod)) {
                $profile->current_outstanding_cod = 0.00;
            }
            if (empty($profile->verification_status)) {
                $profile->verification_status = 'pending';
            }
        });
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function setIsVerifiedAttribute($value): void
    {
        $this->attributes['verification_status'] = $value ? 'verified' : 'pending';
    }

    public function getOtherPlatformAffiliationAttribute(): string
    {
        return $this->affiliation ?? 'none';
    }

    public function setOtherPlatformAffiliationAttribute($value): void
    {
        $this->attributes['affiliation'] = $value ?: 'none';
        $this->attributes['has_other_platform_affiliation'] = ! in_array($value, ['none', null, '']);
    }

    public function getPayoutMethodAttribute(): string
    {
        if (! empty($this->esewa_id)) {
            return 'esewa';
        }
        if (! empty($this->khalti_id)) {
            return 'khalti';
        }

        return 'bank';
    }

    protected $casts = [
        'dob' => 'date',
        'license_expiry_date' => 'date',
        'verified_at' => 'datetime',
        'last_location_updated_at' => 'datetime',
        'agreement_accepted_at' => 'datetime',
        'has_other_platform_affiliation' => 'boolean',
        'agreement_accepted' => 'boolean',
        'service_radius_km' => 'float',
        'max_carrying_weight' => 'float',
        'cod_limit' => 'float',
        'current_outstanding_cod' => 'float',
        'rating' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function serviceAreas(): HasMany
    {
        return $this->hasMany(RiderServiceArea::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ShipmentAssignment::class, 'rider_profile_id');
    }

    public function jobOffers(): HasMany
    {
        return $this->hasMany(RiderJobOffer::class, 'rider_profile_id');
    }

    public function codLedgers(): HasMany
    {
        return $this->hasMany(RiderCodLedger::class, 'rider_profile_id');
    }

    public function earningsLedgers(): HasMany
    {
        return $this->hasMany(RiderEarningsLedger::class, 'rider_profile_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(RiderRating::class, 'rider_profile_id');
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function isOnline(): bool
    {
        return $this->availability_status === 'online';
    }

    /**
     * Check if the rider has sufficient COD headroom to take on a COD shipment
     */
    public function canAcceptCod(float $codAmount): bool
    {
        if ($codAmount <= 0) {
            return true;
        }

        if (! $this->isVerified()) {
            return false;
        }

        return ($this->current_outstanding_cod + $codAmount) <= $this->cod_limit;
    }

    /**
     * Record cash collected from a COD delivery
     */
    public function recordCodCollection(float $amount, ?int $assignmentId = null, string $notes = ''): RiderCodLedger
    {
        $newBalance = $this->current_outstanding_cod + $amount;
        $this->update(['current_outstanding_cod' => $newBalance]);

        return $this->codLedgers()->create([
            'assignment_id' => $assignmentId,
            'transaction_type' => 'collected',
            'amount' => $amount,
            'balance_after' => $newBalance,
            'approval_status' => 'approved',
            'notes' => $notes ?: 'COD cash collected from customer',
        ]);
    }

    /**
     * Record a deposit made by rider to settle outstanding COD
     */
    public function recordCodDeposit(
        float $amount,
        string $method,
        string $reference,
        ?string $receiptPath = null,
        string $notes = '',
        bool $autoApprove = true,
        ?int $approvedBy = null
    ): RiderCodLedger {
        return DB::transaction(function () use ($amount, $method, $reference, $receiptPath, $notes, $autoApprove, $approvedBy) {
            $locked = self::lockForUpdate()->findOrFail($this->id);
            $this->setRawAttributes($locked->getAttributes(), true);
            $existing = $this->codLedgers()->where('transaction_type', 'deposited')->where('deposit_reference', $reference)->first();
            if ($existing) {
                if (abs($existing->amount) != $amount || $existing->deposit_method !== $method) {
                    throw ValidationException::withMessages(['deposit_reference' => 'Reference already used for a different deposit.']);
                }

                return $existing;
            }
            $pending = abs((float) $this->codLedgers()->where('transaction_type', 'deposited')->where('approval_status', 'pending')->sum('amount'));
            if ($amount <= 0 || round($amount, 2) != $amount || $amount > $this->current_outstanding_cod - $pending) {
                throw ValidationException::withMessages(['amount' => 'Deposit must fit the outstanding COD balance after pending deposits.']);
            }

            $newBalance = max(0, $this->current_outstanding_cod - $amount);

            if ($autoApprove) {
                $this->update(['current_outstanding_cod' => $newBalance]);
            }

            return $this->codLedgers()->create([
                'transaction_type' => 'deposited',
                'amount' => -$amount,
                'balance_after' => $newBalance,
                'deposit_method' => $method,
                'deposit_reference' => $reference,
                'deposit_receipt_path' => $receiptPath,
                'approval_status' => $autoApprove ? 'approved' : 'pending',
                'approved_by' => $approvedBy,
                'approved_at' => $autoApprove ? now() : null,
                'notes' => $notes ?: 'Rider COD deposit remittance',
            ]);
        }, 3);
    }

    /**
     * Record rider earnings (delivery fee, distance bonus, etc.)
     */
    public function recordEarnings(float $amount, string $type = 'delivery_fee', ?int $assignmentId = null, string $notes = ''): RiderEarningsLedger
    {
        $lastBalance = (float) ($this->earningsLedgers()->latest('id')->value('balance_after') ?? 0.00);
        $newBalance = $lastBalance + $amount;

        return $this->earningsLedgers()->create([
            'assignment_id' => $assignmentId,
            'type' => $type,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'status' => 'approved',
            'notes' => $notes,
        ]);
    }

    /**
     * Get current total accumulated earnings balance
     */
    public function getEarningsBalanceAttribute(): float
    {
        return (float) ($this->earningsLedgers()->latest('id')->value('balance_after') ?? 0.00);
    }

    /**
     * Get total lifetime approved earnings
     */
    public function getTotalEarningsAttribute(): float
    {
        return (float) ($this->earningsLedgers()->where('status', 'approved')->sum('amount'));
    }

    /**
     * Comprehensive capacity and qualification check
     */
    public function isAvailableForDelivery(float $weight = 1.0, float $cod = 0.0): bool
    {
        if (! $this->isVerified()) {
            return false;
        }

        if ($this->availability_status !== 'online') {
            return false;
        }

        if ($this->current_active_packages >= $this->max_active_packages) {
            return false;
        }

        if ($weight > $this->max_carrying_weight) {
            return false;
        }

        return $this->canAcceptCod($cod);
    }

    /**
     * Check if a destination falls within the rider's service coverage
     */
    public function coversArea(?string $district = null, ?string $province = null, ?string $area = null): bool
    {
        $areas = $this->serviceAreas()->where('is_active', true)->get();

        // If rider hasn't specified custom granular areas, fallback to primary operating district/province
        if ($areas->isEmpty()) {
            if ($district && $this->district && strcasecmp(trim($district), trim($this->district)) === 0) {
                return true;
            }
            if ($province && $this->province && strcasecmp(trim($province), trim($this->province)) === 0) {
                return true;
            }

            return true;
        }

        foreach ($areas as $serviceArea) {
            if ($district && $serviceArea->district && strcasecmp(trim($district), trim($serviceArea->district)) === 0) {
                return true;
            }
            if ($area && $serviceArea->area_name && stripos($area, $serviceArea->area_name) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Dynamically recalculate performance trust score and update badges
     */
    public function recalculateTrustScore(): void
    {
        $avgRating = (float) ($this->ratings()->avg('overall_rating') ?? 5.00);
        $ratingsCount = $this->ratings()->count();

        $completed = $this->total_completed_deliveries;
        $failed = $this->total_failed_deliveries;
        $totalJobs = $completed + $failed;

        $completionRate = $totalJobs > 0 ? ($completed / $totalJobs) * 100 : 100;
        $penaltiesCount = $this->earningsLedgers()->where('type', 'penalty')->count();

        // Formula: 50% fulfillment rate + 50% customer rating - penalty deductions
        $rawScore = ($completionRate * 0.5) + (($avgRating / 5.0) * 50) - ($penaltiesCount * 5);
        $trustScore = (int) max(10, min(100, round($rawScore)));

        $badge = $this->badge_status;
        if ($this->verification_status === 'verified') {
            if ($completed >= 200 && $avgRating >= 4.8 && $trustScore >= 95) {
                $badge = 'preferred';
            } elseif ($completed >= 50 && $avgRating >= 4.5 && $trustScore >= 85) {
                $badge = 'trusted';
            } else {
                $badge = 'verified';
            }
        }

        $this->update([
            'rating' => $avgRating,
            'total_ratings_count' => $ratingsCount,
            'trust_score' => $trustScore,
            'badge_status' => $badge,
        ]);
    }
}
