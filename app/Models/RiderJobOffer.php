<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderJobOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'rider_profile_id',
        'offered_amount',
        'offered_at',
        'expires_at',
        'response',
        'responded_at',
    ];

    protected $casts = [
        'offered_amount' => 'float',
        'offered_at' => 'datetime',
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ShipmentAssignment::class);
    }

    public function riderProfile(): BelongsTo
    {
        return $this->belongsTo(RiderProfile::class);
    }
}
