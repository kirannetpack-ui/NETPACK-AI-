<?php
// app/Models/PartnerZone.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerZone extends Model
{
    protected $table = 'partner_zones';
    
    protected $fillable = [
        'partner_id', 'zone_name', 'districts', 'municipalities', 'wards', 'zone_type'
    ];
    
    protected $casts = [
        'districts' => 'array',
        'municipalities' => 'array',
        'wards' => 'array'
    ];
    
    public function partner()
    {
        return $this->belongsTo(DomesticPartner::class, 'partner_id');
    }
    
    public function rates()
    {
        return $this->hasMany(PartnerRate::class);
    }
}