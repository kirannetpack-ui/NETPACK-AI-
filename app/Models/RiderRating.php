<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'rider_profile_id',
        'assignment_id',
        'rated_by_user_id',
        'overall_rating',
        'professionalism',
        'timeliness',
        'parcel_handling',
        'communication',
        'feedback',
    ];

    public function riderProfile(): BelongsTo
    {
        return $this->belongsTo(RiderProfile::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ShipmentAssignment::class);
    }

    public function rater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_by_user_id');
    }
}
