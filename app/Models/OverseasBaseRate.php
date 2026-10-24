<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverseasBaseRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'overseas_partner_id',
        'country_from',
        'country_to',
        'city_from',
        'city_to',
        'weight_from',
        'weight_to',
        'rate_per_kg',
        'minimum_rate',
        'service_type',
        'transit_days',
        'file_name',
        'file_path',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'weight_from' => 'decimal:2',
        'weight_to' => 'decimal:2',
        'rate_per_kg' => 'decimal:2',
        'minimum_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function overseasPartner()
    {
        return $this->belongsTo(User::class, 'overseas_partner_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->whereDate('effective_from', '<=', now())
                     ->where(function($q) {
                         $q->whereDate('effective_to', '>=', now())
                           ->orWhereNull('effective_to');
                     });
    }

    public function scopeByCountry($query, $countryTo)
    {
        return $query->where('country_to', $countryTo);
    }

    public function scopeByWeight($query, $weight)
    {
        return $query->where('weight_from', '<=', $weight)
                     ->where('weight_to', '>=', $weight);
    }

    public function scopeByService($query, $serviceType)
    {
        return $query->where('service_type', $serviceType);
    }

    public function calculateBaseRate($weight)
    {
        return max($this->rate_per_kg * $weight, $this->minimum_rate);
    }

/**
 * Calculate final rate with margin
 */
public function calculateFinalRate($weight, $marginRules = null)
{
    $baseRate = $this->calculateBaseRate($weight);
    
    if (!$marginRules) {
        $marginRules = MarginRule::active()
            ->where('overseas_partner_id', $this->overseas_partner_id)
            ->byCountry($this->country_to)
            ->byService($this->service_type)
            ->byWeight($weight)
            ->get();
    }
    
    $totalMargin = 0;
    foreach ($marginRules as $rule) {
        $totalMargin += $rule->calculateMargin($baseRate);
    }
    
    return [
        'base_rate' => $baseRate,
        'total_margin' => $totalMargin,
        'final_rate' => $baseRate + $totalMargin,
        'margin_breakdown' => $marginRules->map(function($rule) {
            return [
                'rule_name' => $rule->rule_name,
                'type' => $rule->margin_type,
                'value' => $rule->margin_value,
                'description' => $rule->formatted_rule,
            ];
        }),
    ];
}

}