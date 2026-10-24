<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverseasRate extends Model
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
        'transit_time',
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

    public function scopeByCountry($query, $countryFrom, $countryTo)
    {
        return $query->where('country_from', $countryFrom)
                     ->where('country_to', $countryTo);
    }

    public function scopeByWeight($query, $weight)
    {
        return $query->where('weight_from', '<=', $weight)
                     ->where('weight_to', '>=', $weight);
    }
}