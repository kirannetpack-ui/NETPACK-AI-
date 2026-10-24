<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManifestShipment extends Model
{
    protected $fillable = [
        'manifest_id',
        'bag_id',
        'shipment_id',
        'partner_id',
        'status',
        'arrival_status', // pending, arrived, non_arrival
        'arrived_at',
        'arrived_location',
        'non_arrival_remarks',
        'scanned_by_staff_id',
        'staff_name',
        'delivery_type',
        'is_collected',
        'collected_at',
        'delivery_fee',
        'payment_status',
        'received_at',
        'delivered_at',
        'dispatched_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'is_collected' => 'boolean',
        'collected_at' => 'datetime',
        'received_at' => 'datetime',
        'delivered_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'arrived_at' => 'datetime',
        'delivery_fee' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }

    public function bag()
    {
        return $this->belongsTo(ManifestBag::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function scannedByStaff()
    {
        return $this->belongsTo(AgencyStaff::class, 'scanned_by_staff_id');
    }

    public function events()
    {
        return $this->hasMany(ManifestShipmentEvent::class);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'received' => 'bg-blue-100 text-blue-800',
            'delivered' => 'bg-green-100 text-green-800',
            'dispatched' => 'bg-purple-100 text-purple-800',
        ];
        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getArrivalStatusBadgeAttribute()
    {
        return match ($this->arrival_status) {
            'arrived' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'non_arrival', 'not_arrived' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-amber-100 text-amber-800 border-amber-200',
        };
    }

    public function getDeliveryTypeLabelAttribute()
    {
        $labels = [
            'door_delivery' => '🏠 Door Delivery',
            'collection' => '🏢 Collection from Office',
        ];
        return $labels[$this->delivery_type] ?? $this->delivery_type;
    }

    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'paid' => 'bg-green-100 text-green-800',
            'pre_defined' => 'bg-blue-100 text-blue-800',
            'nil' => 'bg-gray-100 text-gray-800',
        ];
        return $badges[$this->payment_status] ?? 'bg-gray-100 text-gray-800';
    }
}
