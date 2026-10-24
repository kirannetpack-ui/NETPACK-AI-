<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderRateRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_name',
        'vehicle_type',
        'base_distance_km',
        'base_rate',
        'additional_km_rate',
        'weight_surcharge_per_kg',
        'cod_handling_fee',
        'return_fee',
        'is_active',
    ];

    protected $casts = [
        'base_distance_km' => 'float',
        'base_rate' => 'float',
        'additional_km_rate' => 'float',
        'weight_surcharge_per_kg' => 'float',
        'cod_handling_fee' => 'float',
        'return_fee' => 'float',
        'is_active' => 'boolean',
    ];

    /**
     * Compute rider delivery fee using standard formula
     */
    public function calculateFee(float $distanceKm, float $weightKg = 1.0, float $codAmount = 0.0): float
    {
        $fee = $this->base_rate;

        // Distance surcharge
        if ($distanceKm > $this->base_distance_km) {
            $extraDistance = $distanceKm - $this->base_distance_km;
            $fee += ($extraDistance * $this->additional_km_rate);
        }

        // Weight surcharge (> 1kg)
        if ($weightKg > 1.0) {
            $extraWeight = $weightKg - 1.0;
            $fee += ($extraWeight * $this->weight_surcharge_per_kg);
        }

        // COD handling fee
        if ($codAmount > 0) {
            $fee += $this->cod_handling_fee;
        }

        return round($fee, 2);
    }
}
