<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentTracking extends Model
{
    protected $fillable = [
        'shipment_id',
        'status',
        'location',
        'description',
        'latitude',
        'longitude',
        'tracked_at',
        'metadata',
    ];

    protected $casts = [
        'tracked_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'metadata' => 'array',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}