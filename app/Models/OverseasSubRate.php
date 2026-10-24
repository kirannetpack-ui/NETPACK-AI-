<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverseasSubRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'overseas_partner_id',
        'charge_name',
        'charge_code',
        'charge_type',
        'charge_value',
        'minimum_charge',
        'maximum_charge',
        'applicable_countries',
        'applicable_services',
        'applicable_weight_from',
        'applicable_weight_to',
        'applicable_value_from',
        'applicable_value_to',
        'description',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'applicable_countries' => 'array',
        'applicable_services' => 'array',
        'charge_value' => 'decimal:2',
        'minimum_charge' => 'decimal:2',
        'maximum_charge' => 'decimal:2',
        'applicable_weight_from' => 'decimal:2',
        'applicable_weight_to' => 'decimal:2',
        'applicable_value_from' => 'decimal:2',
        'applicable_value_to' => 'decimal:2',
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
            $q->whereJsonContains('applicable_countries', 'ALL')
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
        return $query->where(function($q) use ($weight) {
            $q->whereNull('applicable_weight_from')
              ->orWhere('applicable_weight_from', '<=', $weight);
        })->where(function($q) use ($weight) {
            $q->whereNull('applicable_weight_to')
              ->orWhere('applicable_weight_to', '>=', $weight);
        });
    }

    public function calculateCharge($baseAmount, $weight)
    {
        $charge = 0;
        
        switch ($this->charge_type) {
            case 'percentage':
                $charge = $baseAmount * ($this->charge_value / 100);
                break;
            case 'fixed':
                $charge = $this->charge_value;
                break;
            case 'per_kg':
                $charge = $this->charge_value * $weight;
                break;
            case 'per_shipment':
                $charge = $this->charge_value;
                break;
        }

        // Apply min/max constraints
        if ($charge < $this->minimum_charge) {
            $charge = $this->minimum_charge;
        }
        if ($this->maximum_charge && $charge > $this->maximum_charge) {
            $charge = $this->maximum_charge;
        }

        return $charge;
    }
}