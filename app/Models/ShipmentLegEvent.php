<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentLegEvent extends Model
{
    protected $fillable = [
        'shipment_leg_id', 'event_type', 'from_status', 'to_status',
        'from_partner_id', 'to_partner_id', 'performed_by', 'notes', 'metadata',
    ];

    protected $casts = ['metadata' => 'array'];

    public function leg()
    {
        return $this->belongsTo(ShipmentLeg::class, 'shipment_leg_id');
    }
}
