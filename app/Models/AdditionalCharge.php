<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'overseas_partner_id',
        'charge_name',
        'charge_type', // percentage, fixed, per_kg
        'charge_value',
        'applicable_to', // all, specific_countries, specific_services
        'country_codes',
        'service_types',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'charge_value' => 'decimal:2',
        'is_active' => 'boolean',
        'country_codes' => 'array',
        'service_types' => 'array',
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

    public function scopeByCountry($query, $countryCode)
    {
        return $query->where('applicable_to', 'all')
                     ->orWhere(function($q) use ($countryCode) {
                         $q->where('applicable_to', 'specific_countries')
                           ->whereJsonContains('country_codes', $countryCode);
                     });
    }

    public function calculateCharge($baseAmount, $weight = null)
    {
        switch ($this->charge_type) {
            case 'percentage':
                return $baseAmount * ($this->charge_value / 100);
            case 'fixed':
                return $this->charge_value;
            case 'per_kg':
                return $this->charge_value * ($weight ?? 0);
            default:
                return 0;
        }
    }
}