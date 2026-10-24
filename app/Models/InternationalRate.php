<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternationalRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'hub_id',
        'agency_id',
        'service_type',
        'rate_type',
        'country',
        'country_code',
        'zone_id',
        'weight_tiers',
        'per_kg_tiers',
        'customs_clearance_charge',
        'godown_charge',
        'fuel_surcharge_percent',
        'doc_fee',
        'transit_days_min',
        'transit_days_max',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'weight_tiers' => 'array',
        'per_kg_tiers' => 'array',
        'customs_clearance_charge' => 'decimal:2',
        'godown_charge' => 'decimal:2',
        'fuel_surcharge_percent' => 'decimal:2',
        'doc_fee' => 'decimal:2',
        'transit_days_min' => 'integer',
        'transit_days_max' => 'integer',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hub()
    {
        return $this->belongsTo(OverseasHub::class, 'hub_id');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function zone()
    {
        return $this->belongsTo(InternationalZone::class, 'zone_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCountry($query, string $country)
    {
        return $query->where(function ($q) use ($country) {
            $q->where('rate_type', 'country')
              ->where(function ($cq) use ($country) {
                  $cq->where('country', $country)
                     ->orWhere('country_code', strtoupper($country));
              });
        })->orWhere(function ($q) use ($country) {
            $q->where('rate_type', 'zone')
              ->whereHas('zone', function ($zq) use ($country) {
                  $zq->whereJsonContains('countries', $country);
              });
        })->orWhere(function ($q) use ($country) {
            $q->whereNotNull('hub_id')
              ->whereHas('hub', function ($hq) use ($country) {
                  $hq->whereJsonContains('coverage_countries', $country);
              });
        });
    }

    /**
     * Calculate base freight rate for a given chargeable weight.
     */
    public function getFreightForWeight(float $chargeableWeight): ?float
    {
        // 1. Under or equal to 10.0 kg: Slab lookup
        if ($chargeableWeight <= 10.0) {
            $tiers = $this->weight_tiers ?? [];
            $key = number_format($chargeableWeight, 1, '.', '');
            
            if (isset($tiers[$key]) && is_numeric($tiers[$key])) {
                return (float) $tiers[$key];
            }

            // Fallback to highest available slab under 10kg or formula
            if (!empty($tiers)) {
                // Find closest matching slab >= chargeableWeight
                ksort($tiers, SORT_NUMERIC);
                foreach ($tiers as $tierWeight => $tierRate) {
                    if ((float)$tierWeight >= $chargeableWeight && is_numeric($tierRate)) {
                        return (float) $tierRate;
                    }
                }
                // Return max tier if exceeding
                return (float) end($tiers);
            }
        }

        // 2. Above 10.0 kg: Dynamic per-kg range lookup
        $ranges = $this->per_kg_tiers ?? [];
        if (is_array($ranges) && !empty($ranges)) {
            // Sort ranges ascending by min_weight
            usort($ranges, function ($a, $b) {
                return ($a['min_weight'] ?? 0) <=> ($b['min_weight'] ?? 0);
            });

            foreach ($ranges as $range) {
                $min = (float) ($range['min_weight'] ?? 0);
                $max = (float) ($range['max_weight'] ?? 999999);
                $ratePerKg = (float) ($range['rate_per_kg'] ?? 0);

                if ($chargeableWeight >= $min && $chargeableWeight <= $max && $ratePerKg > 0) {
                    return round($chargeableWeight * $ratePerKg, 2);
                }
            }

            // If chargeable weight exceeds highest range max, use the highest tier's per-kg rate
            $lastRange = end($ranges);
            if (!empty($lastRange['rate_per_kg']) && (float)$lastRange['rate_per_kg'] > 0) {
                return round($chargeableWeight * (float)$lastRange['rate_per_kg'], 2);
            }
        }

        // Fallback calculation if tiers are not fully populated
        $lastTenKgRate = (float) ($this->weight_tiers['10.0'] ?? 12000);
        $perKgOver = $lastTenKgRate / 10.0;
        return round($chargeableWeight * $perKgOver, 2);
    }
}
