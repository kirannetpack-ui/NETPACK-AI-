<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentTransitPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'transit_point_id',
        'arrived_at',
        'departed_at',
        'status',
        'notes',
        'additional_data',
        'sequence',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
        'departed_at' => 'datetime',
        'additional_data' => 'array',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ARRIVED = 'arrived';
    const STATUS_DEPARTED = 'departed';
    const STATUS_DELAYED = 'delayed';
    const STATUS_CANCELLED = 'cancelled';

    const STATUS_LABELS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_ARRIVED => 'Arrived',
        self::STATUS_DEPARTED => 'Departed',
        self::STATUS_DELAYED => 'Delayed',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    const STATUS_COLORS = [
        self::STATUS_PENDING => 'yellow',
        self::STATUS_ARRIVED => 'green',
        self::STATUS_DEPARTED => 'blue',
        self::STATUS_DELAYED => 'red',
        self::STATUS_CANCELLED => 'gray',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function transitPoint()
    {
        return $this->belongsTo(OverseasTransitPoint::class);
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function markArrived($notes = null)
    {
        $this->update([
            'status' => self::STATUS_ARRIVED,
            'arrived_at' => now(),
            'notes' => $notes,
        ]);
    }

    public function markDeparted($notes = null)
    {
        $this->update([
            'status' => self::STATUS_DEPARTED,
            'departed_at' => now(),
            'notes' => $notes,
        ]);
    }

    public function markDelayed($reason)
    {
        $this->update([
            'status' => self::STATUS_DELAYED,
            'notes' => $reason,
        ]);
    }
}