<?php
// app/Models/PartnerRate.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerRate extends Model
{
    protected $table = 'partner_rates';
    
    protected $fillable = [
        'partner_id', 'zone_id', 'service_tier', 'base_rate', 'per_kg_rate', 'per_km_rate',
        'logistical_charge', 'additional_charge', 'additional_charge_reason',
        'estimated_hours', 'estimated_days'
    ];
    
    protected $casts = [
        'base_rate' => 'decimal:2',
        'per_kg_rate' => 'decimal:2',
        'per_km_rate' => 'decimal:2',
        'logistical_charge' => 'decimal:2',
        'additional_charge' => 'decimal:2'
    ];
    
    public function partner()
    {
        return $this->belongsTo(DomesticPartner::class, 'partner_id');
    }
    
    public function zone()
    {
        return $this->belongsTo(PartnerZone::class, 'zone_id');
    }
}