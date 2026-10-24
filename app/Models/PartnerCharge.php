<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerCharge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipment_id',
        'partner_id',
        'shipment_reference',
        'base_charge',
        'weight_charge',
        'distance_charge',
        'additional_charges',
        'fuel_surcharge',
        'handling_fee',
        'insurance_charge',
        'customs_charge',
        'total_charge',
        'system_base_charge',
        'system_total_charge',
        'charge_difference',
        'charge_percentage_difference',
        'service_type',
        'service_tier',
        'weight_kg',
        'distance_km',
        'transit_days',
        'invoice_file',
        'supporting_document',
        'additional_files',
        'charge_breakdown',
        'system_breakdown',
        'status',
        'verification_status',
        'notes',
        'dispute_reason',
        'admin_notes',
        'adjustment_notes',
        'submitted_at',
        'verified_at',
        'disputed_at',
        'adjusted_at',
        'approved_at',
        'submitted_by',
        'verified_by',
        'approved_by',
        'disputed_by',
    ];

    protected $casts = [
        'base_charge' => 'decimal:2',
        'weight_charge' => 'decimal:2',
        'distance_charge' => 'decimal:2',
        'additional_charges' => 'decimal:2',
        'fuel_surcharge' => 'decimal:2',
        'handling_fee' => 'decimal:2',
        'insurance_charge' => 'decimal:2',
        'customs_charge' => 'decimal:2',
        'total_charge' => 'decimal:2',
        'system_base_charge' => 'decimal:2',
        'system_total_charge' => 'decimal:2',
        'charge_difference' => 'decimal:2',
        'charge_percentage_difference' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'distance_km' => 'integer',
        'transit_days' => 'integer',
        'additional_files' => 'array',
        'charge_breakdown' => 'array',
        'system_breakdown' => 'array',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'disputed_at' => 'datetime',
        'adjusted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_VERIFIED = 'verified';
    const STATUS_DISPUTED = 'disputed';
    const STATUS_ADJUSTED = 'adjusted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_APPROVED = 'approved';

    const STATUS_LABELS = [
        self::STATUS_PENDING => 'Pending Review',
        self::STATUS_UNDER_REVIEW => 'Under Review',
        self::STATUS_VERIFIED => 'Verified',
        self::STATUS_DISPUTED => 'Disputed',
        self::STATUS_ADJUSTED => 'Adjusted',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_APPROVED => 'Approved',
    ];

    const STATUS_COLORS = [
        self::STATUS_PENDING => 'yellow',
        self::STATUS_UNDER_REVIEW => 'blue',
        self::STATUS_VERIFIED => 'green',
        self::STATUS_DISPUTED => 'red',
        self::STATUS_ADJUSTED => 'orange',
        self::STATUS_REJECTED => 'dark-red',
        self::STATUS_APPROVED => 'teal',
    ];

    // Verification Status Constants
    const VERIFICATION_PENDING = 'pending';
    const VERIFICATION_VERIFIED = 'verified';
    const VERIFICATION_DISPUTED = 'disputed';
    const VERIFICATION_ADJUSTED = 'adjusted';

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function disputedBy()
    {
        return $this->belongsTo(User::class, 'disputed_by');
    }

    public function history()
    {
        return $this->hasMany(PartnerChargeHistory::class);
    }

    public function verificationLogs()
    {
        return $this->hasMany(PartnerRateVerificationLog::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', self::STATUS_UNDER_REVIEW);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', self::STATUS_DISPUTED);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getStatusBadgeAttribute()
    {
        return "<span class='px-2 py-1 rounded-full text-xs font-medium bg-{$this->status_color}-100 text-{$this->status_color}-800'>{$this->status_label}</span>";
    }

    public function calculateDifference()
    {
        if ($this->system_total_charge && $this->total_charge) {
            $this->charge_difference = $this->total_charge - $this->system_total_charge;
            $this->charge_percentage_difference = $this->system_total_charge > 0 
                ? ($this->charge_difference / $this->system_total_charge) * 100 
                : 0;
            $this->save();
        }
        return $this;
    }

    public function markAsUnderReview($adminId, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_UNDER_REVIEW,
            'admin_notes' => $notes,
            'verified_by' => $adminId,
        ]);

        $this->addHistory('under_review', $notes);
        return $this;
    }

    public function markAsVerified($adminId, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_VERIFIED,
            'verification_status' => self::VERIFICATION_VERIFIED,
            'verified_at' => now(),
            'verified_by' => $adminId,
            'admin_notes' => $notes,
        ]);

        $this->addHistory('verified', $notes);
        return $this;
    }

    public function markAsDisputed($adminId, $reason, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_DISPUTED,
            'verification_status' => self::VERIFICATION_DISPUTED,
            'dispute_reason' => $reason,
            'disputed_at' => now(),
            'disputed_by' => $adminId,
            'admin_notes' => $notes,
        ]);

        $this->addHistory('disputed', "Reason: $reason" . ($notes ? " | Notes: $notes" : ""));
        return $this;
    }

    public function markAsAdjusted($adminId, $adjustedAmount, $notes = null)
    {
        $oldTotal = $this->total_charge;
        $this->update([
            'status' => self::STATUS_ADJUSTED,
            'verification_status' => self::VERIFICATION_ADJUSTED,
            'total_charge' => $adjustedAmount,
            'adjusted_at' => now(),
            'verified_by' => $adminId,
            'admin_notes' => $notes,
        ]);

        $this->addHistory('adjusted', "Adjusted from $oldTotal to $adjustedAmount" . ($notes ? " | Notes: $notes" : ""));
        return $this;
    }

    public function markAsApproved($adminId, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $adminId,
            'admin_notes' => $notes,
        ]);

        $this->addHistory('approved', $notes);
        return $this;
    }

    public function addHistory($action, $notes = null)
    {
        return $this->history()->create([
            'action' => $action,
            'notes' => $notes,
            'performed_by' => auth()->id(),
            'old_values' => $this->getOriginal(),
            'new_values' => $this->getAttributes(),
        ]);
    }
}