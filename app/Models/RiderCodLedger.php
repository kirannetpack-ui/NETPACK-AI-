<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderCodLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'rider_profile_id',
        'assignment_id',
        'transaction_type',
        'amount',
        'balance_after',
        'deposit_method',
        'deposit_reference',
        'deposit_receipt_path',
        'approval_status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_after' => 'float',
        'approved_at' => 'datetime',
    ];

    public function riderProfile(): BelongsTo
    {
        return $this->belongsTo(RiderProfile::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ShipmentAssignment::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
