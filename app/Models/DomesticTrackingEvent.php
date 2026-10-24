<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomesticTrackingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'domestic_shipment_id',
        'status',
        'location',
        'city',
        'zone',
        'description',
        'additional_data',
        'event_time',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'event_time' => 'datetime',
    ];

    public function shipment()
    {
        return $this->belongsTo(DomesticShipment::class, 'domestic_shipment_id');
    }

    public function getStatusLabelAttribute()
    {
        return DomesticShipment::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        return DomesticShipment::STATUS_COLORS[$this->status] ?? 'gray';
    }
}