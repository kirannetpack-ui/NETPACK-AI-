<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderServiceArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'rider_profile_id',
        'province',
        'district',
        'municipality',
        'ward',
        'area_name',
        'service_radius_km',
        'is_active',
    ];

    protected $casts = [
        'service_radius_km' => 'float',
        'is_active' => 'boolean',
    ];

    public function riderProfile(): BelongsTo
    {
        return $this->belongsTo(RiderProfile::class);
    }
}
