<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomesticShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_number',
        'client_id',
        'partner_id',
        'partner_user_id',
        'domestic_rate_id',
        'sender_name',
        'sender_email',
        'sender_phone',
        'sender_address',
        'sender_city',
        'sender_zone',
        'sender_lat',
        'sender_lng',
        'receiver_name',
        'receiver_email',
        'receiver_phone',
        'receiver_address',
        'receiver_city',
        'receiver_zone',
        'receiver_ward',
        'receiver_lat',
        'receiver_lng',
        'weight',
        'length',
        'width',
        'height',
        'package_type',
        'package_description',
        'special_instructions',
        'service_type',
        'service_name',
        'base_rate',
        'per_kg_rate',
        'logistical_charge',
        'additional_charge',
        'total_amount',
        'currency',
        'estimated_hours',
        'estimated_days',
        'estimated_delivery_at',
        'actual_delivery_at',
        'status',
        'tracking_history',
        'notes',
        'delivery_notes',
        'invoice_file',
        'label_file',
        'proof_of_delivery',
        'requires_signature',
        'is_insured',
        'insurance_amount',
        'is_cod',
        'cod_amount',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'base_rate' => 'decimal:2',
        'per_kg_rate' => 'decimal:2',
        'logistical_charge' => 'decimal:2',
        'additional_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'insurance_amount' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'estimated_hours' => 'integer',
        'estimated_days' => 'integer',
        'estimated_delivery_at' => 'datetime',
        'actual_delivery_at' => 'datetime',
        'tracking_history' => 'array',
        'requires_signature' => 'boolean',
        'is_insured' => 'boolean',
        'is_cod' => 'boolean',
        'sender_lat' => 'decimal:8',
        'sender_lng' => 'decimal:8',
        'receiver_lat' => 'decimal:8',
        'receiver_lng' => 'decimal:8',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED_DELIVERY = 'failed_delivery';
    const STATUS_RETURNED = 'returned';
    const STATUS_CANCELLED = 'cancelled';

    const STATUS_LABELS = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'picked_up' => 'Picked Up',
        'in_transit' => 'In Transit',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'failed_delivery' => 'Failed Delivery',
        'returned' => 'Returned',
        'cancelled' => 'Cancelled',
    ];

    const STATUS_COLORS = [
        'pending' => 'yellow',
        'confirmed' => 'blue',
        'picked_up' => 'indigo',
        'in_transit' => 'purple',
        'out_for_delivery' => 'orange',
        'delivered' => 'green',
        'failed_delivery' => 'red',
        'returned' => 'gray',
        'cancelled' => 'red',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_user_id');
    }

    public function legacyPartner()
    {
        return $this->belongsTo(DomesticPartner::class, 'partner_id');
    }

    public function domesticRate()
    {
        return $this->belongsTo(DomesticRate::class);
    }

    public function trackingEvents()
    {
        return $this->hasMany(DomesticTrackingEvent::class);
    }

    public function riderAssignments()
    {
        return $this->hasMany(RiderDeliveryAssignment::class);
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByService($query, $serviceType)
    {
        return $query->where('service_type', $serviceType);
    }

    public function scopeByPartner($query, $partnerId)
    {
        return $query->where('partner_id', $partnerId);
    }

    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function generateTrackingNumber()
    {
        return app(\App\Services\TrackingNumberService::class)->domestic();
    }

    public function addTrackingEvent($status, $location = null, $description = null, $additionalData = null)
    {
        $event = $this->trackingEvents()->create([
            'status' => $status,
            'location' => $location,
            'description' => $description,
            'additional_data' => $additionalData,
            'event_time' => now(),
        ]);

        $history = $this->tracking_history ?? [];
        $history[] = [
            'status' => $status,
            'status_label' => self::STATUS_LABELS[$status] ?? $status,
            'location' => $location,
            'description' => $description,
            'time' => now()->toDateTimeString(),
        ];
        $this->update(['tracking_history' => $history]);

        return $event;
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getStatusBadgeAttribute()
    {
        return "<span class='px-2 py-1 rounded-full text-xs font-medium bg-{$this->status_color}-100 text-{$this->status_color}-800'>{$this->status_label}</span>";
    }

    public function getServiceBadgeAttribute()
    {
        $colors = [
            'flash' => 'red',
            'same_day' => 'orange',
            'standard' => 'blue',
            'himalayan' => 'purple',
        ];
        $color = $colors[$this->service_type] ?? 'gray';
        $icon = DomesticRate::SERVICE_ICONS[$this->service_type] ?? '📦';
        
        return "<span class='px-2 py-1 rounded-full text-xs font-medium bg-{$color}-100 text-{$color}-800'>{$icon} {$this->service_name}</span>";
    }
}
