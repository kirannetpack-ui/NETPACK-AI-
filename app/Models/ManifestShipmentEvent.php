<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManifestShipmentEvent extends Model
{
    protected $fillable = [
        'manifest_shipment_id', 'event_type', 'from_status', 'to_status',
        'from_partner_id', 'to_partner_id', 'performed_by', 'notes', 'metadata',
    ];

    protected $casts = ['metadata' => 'array'];

    public function manifestShipment()
    {
        return $this->belongsTo(ManifestShipment::class);
    }
}
