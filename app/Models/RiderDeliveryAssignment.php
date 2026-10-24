<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderDeliveryAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'rider_id',
        'domestic_shipment_id',
        'assigned_by',
        'assigned_at',
        'accepted_at',
        'picked_up_at',
        'delivered_at',
        'status',
        'notes',
        'failure_reason',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function shipment()
    {
        return $this->belongsTo(DomesticShipment::class, 'domestic_shipment_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByRider($query, $riderId)
    {
        return $query->where('rider_id', $riderId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['assigned', 'accepted', 'picked_up', 'in_transit']);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'assigned' => 'Assigned',
            'accepted' => 'Accepted',
            'picked_up' => 'Picked Up',
            'in_transit' => 'In Transit',
            'delivered' => 'Delivered',
            'failed' => 'Failed',
            'returned' => 'Returned',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'assigned' => 'blue',
            'accepted' => 'indigo',
            'picked_up' => 'purple',
            'in_transit' => 'orange',
            'delivered' => 'green',
            'failed' => 'red',
            'returned' => 'gray',
        ];
        return $colors[$this->status] ?? 'gray';
    }
}