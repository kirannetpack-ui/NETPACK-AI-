<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverseasMarginRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'overseas_partner_id',
        'rule_name',
        'margin_type',
        'margin_value',
        'weight_from',
        'weight_to',
        'applicable_countries',
        'applicable_services',
        'apply_to_sub_rates',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'margin_value' => 'decimal:2',
        'weight_from' => 'decimal:2',
        'weight_to' => 'decimal:2',
        'applicable_countries' => 'array',
        'applicable_services' => 'array',
        'apply_to_sub_rates' => 'boolean',
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

    public function scopeByCountry($query, $country)
    {
        return $query->where(function($q) use ($country) {
            $q->whereNull('applicable_countries')
              ->orWhereJsonContains('applicable_countries', 'ALL')
              ->orWhereJsonContains('applicable_countries', $country);
        });
    }

    public function scopeByService($query, $serviceType)
    {
        return $query->where(function($q) use ($serviceType) {
            $q->whereNull('applicable_services')
              ->orWhereJsonContains('applicable_services', $serviceType)
              ->orWhereJsonContains('applicable_services', 'ALL');
        });
    }

    public function scopeByWeight($query, $weight)
    {
        return $query->where('weight_from', '<=', $weight)
                     ->where(function($q) use ($weight) {
                         $q->whereNull('weight_to')
                           ->orWhere('weight_to', '>=', $weight);
                     });
    }

    public function calculateMargin($amount)
    {
        if ($this->margin_type === 'percentage') {
            return $amount * ($this->margin_value / 100);
        } else {
            return $this->margin_value;
        }
    }
}