<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManifestTrackingLog extends Model
{
    protected $fillable = [
        'manifest_id', 'bag_id', 'shipment_id', 'event_type', 'location',
        'description', 'performed_by', 'metadata',
    ];

    protected $casts = ['metadata' => 'array'];

    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
