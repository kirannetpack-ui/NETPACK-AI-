<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderEarningsLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'rider_profile_id',
        'assignment_id',
        'type',
        'amount',
        'balance_after',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_after' => 'float',
    ];

    public function riderProfile(): BelongsTo
    {
        return $this->belongsTo(RiderProfile::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ShipmentAssignment::class);
    }
}
