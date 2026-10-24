<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentLeg extends Model
{
    protected $fillable = [
        'shipment_id', 'sequence', 'leg_type', 'partner_id', 'domestic_rate_id',
        'origin_zone_id', 'destination_zone_id', 'origin_name', 'destination_name',
        'status', 'assignment_source', 'selected_by', 'partner_cost',
        'markup_amount', 'customer_price', 'currency', 'accepted_at',
        'dispatched_at', 'received_at', 'completed_at', 'metadata',
    ];

    protected $casts = [
        'partner_cost' => 'decimal:2',
        'markup_amount' => 'decimal:2',
        'customer_price' => 'decimal:2',
        'accepted_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class);
    }

    public function rate()
    {
        return $this->belongsTo(DomesticRate::class, 'domestic_rate_id');
    }

    public function originZone()
    {
        return $this->belongsTo(DeliveryZone::class, 'origin_zone_id');
    }

    public function destinationZone()
    {
        return $this->belongsTo(DeliveryZone::class, 'destination_zone_id');
    }

    public function events()
    {
        return $this->hasMany(ShipmentLegEvent::class);
    }

    public function canTransitionTo(string $status): bool
    {
        $allowed = [
            'pending' => ['assigned', 'cancelled'],
            'assigned' => ['accepted', 'exception', 'cancelled'],
            'accepted' => ['pickup_en_route', 'picked_up', 'exception'],
            'pickup_en_route' => ['picked_up', 'exception'],
            'picked_up' => ['in_transit', 'received', 'exception'],
            'received' => ['processed', 'in_transit', 'exception'],
            'processed' => ['dispatched', 'in_transit', 'exception'],
            'dispatched' => ['in_transit', 'received', 'exception'],
            'in_transit' => ['received', 'out_for_delivery', 'completed', 'exception'],
            'out_for_delivery' => ['completed', 'delivery_attempted', 'exception'],
            'delivery_attempted' => ['out_for_delivery', 'completed', 'exception'],
            'exception' => ['assigned', 'accepted', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];

        return in_array($status, $allowed[$this->status] ?? [], true);
    }
}
