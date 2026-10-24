<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupRequest extends Model
{
    protected $table = 'pickup_requests';
    
    protected $fillable = [
        'seller_id', 'order_id', 'shipment_id', 'assigned_rider_id', 'partner_id', 'partner_user_id', 'partner_staff_id',
        'pickup_address', 'pickup_ward_no', 'pickup_municipality', 'pickup_district', 'pickup_province',
        'pickup_city', 'pickup_latitude', 'pickup_longitude',
        'delivery_address', 'delivery_ward_no', 'delivery_municipality', 'delivery_district', 'delivery_province',
        'delivery_city', 'delivery_latitude', 'delivery_longitude',
        'scheduled_pickup_time', 'picked_up_at', 'departed_at', 'delivered_at', 'arrived_at',
        'items_description', 'estimated_weight_kg', 'actual_weight_kg',
        'service_tier', 'status', 'calculated_price', 'calculated_price_final', 'distance_km',
        'otp_code', 'delivery_proof_image', 'status_notes', 'status_history',
        // E-commerce fields
        'is_ecommerce', 'platform', 'order_reference', 'cod_amount',
        'platform_fee', 'seller_earnings', 'customer_name', 'customer_phone',
        'customer_email', 'product_items', 'payment_status', 'tracking_number', 'qr_code', 'delivery_label', 'total_amount',
        // Delay fields
        'is_delayed', 'delay_reason', 'delay_reported_at', 'customer_notified',
        'customer_notification_message', 'contact_person_name', 'contact_person_phone',
        'expected_resolution_time'
    ];
    
    protected $casts = [
        'scheduled_pickup_time' => 'datetime',
        'picked_up_at' => 'datetime',
        'departed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'arrived_at' => 'datetime',
        'delay_reported_at' => 'datetime',
        'expected_resolution_time' => 'datetime',
        'is_ecommerce' => 'boolean',
        'is_delayed' => 'boolean',
        'customer_notified' => 'boolean',
        'product_items' => 'array',
        'status_history' => 'array',
        'calculated_price' => 'decimal:2',
        'calculated_price_final' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'seller_earnings' => 'decimal:2',
        'estimated_weight_kg' => 'decimal:2',
        'actual_weight_kg' => 'decimal:2'
    ];

    protected static function booted()
    {
        static::creating(function ($pickup) {
            if (empty($pickup->tracking_number)) {
                try {
                    $pickup->tracking_number = app(\App\Services\TrackingNumberService::class)->tracking('domestic');
                } catch (\Throwable $e) {
                    $pickup->tracking_number = 'NPD-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT) . '-0';
                }
            }
        });

        static::created(function ($pickup) {
            if (empty($pickup->status_history)) {
                app(\App\Services\AutomatedTrackingService::class)->recordPickupScheduled($pickup);
            }
        });
    }
    
    /**
     * Get the seller who created this pickup request
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    
    /**
     * Get the assigned rider
     */
    public function rider()
    {
        return $this->belongsTo(User::class, 'assigned_rider_id');
    }

    /**
     * Alias for rider
     */
    public function assignedRider()
    {
        return $this->belongsTo(User::class, 'assigned_rider_id');
    }
    
    /**
     * Get the domestic partner handling this delivery
     */
    public function partner()
    {
        return $this->belongsTo(DomesticPartner::class, 'partner_id');
    }

    public function partnerUser()
    {
        return $this->belongsTo(User::class, 'partner_user_id');
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
    
    /**
     * Get the partner staff who processed this
     */
    public function partnerStaff()
    {
        return $this->belongsTo(PartnerStaff::class, 'partner_staff_id');
    }
    
    /**
     * Get reminders for this pickup request
     */
    public function reminders()
    {
        return $this->hasMany(DeliveryReminder::class, 'pickup_request_id');
    }

public function deliveryReminders()
{
    return $this->hasMany(DeliveryReminder::class, 'pickup_request_id');
}
    
    /**
     * Get reminder logs for this pickup request
     */
    public function reminderLogs()
    {
        return $this->hasMany(ReminderLog::class, 'pickup_request_id');
    }
    
/**
 * Get pending reminders for this pickup
 */
public function pendingReminders()
{
    return $this->deliveryReminders()->where('is_sent', false);
}

/**
 * Check if pickup has pending reminders
 */
public function hasPendingReminders()
{
    return $this->pendingReminders()->exists();
}

public function getServiceTimeframeAttribute()
{
    $service = \App\Models\LogisticsService::where('code', $this->service_tier)->first();
    if ($service) {
        return [
            'hours' => (float) $service->transit_time_hours,
            'label' => $service->name,
            'service' => $service,
        ];
    }

    $fallbackTimeframes = [
        'ecommerce' => ['hours' => 1, 'label' => '1 hour'],
        'flash' => ['hours' => 4, 'label' => '2-4 hours'],
        'same_day' => ['hours' => 12, 'label' => 'Today by 8 PM'],
        'standard' => ['hours' => 72, 'label' => '1-3 days'],
        'himalayan' => ['hours' => 168, 'label' => '3-7 days'],
    ];
    
    return $fallbackTimeframes[$this->service_tier] ?? ['hours' => 48, 'label' => 'Standard'];
}

public function getDeadlineAttribute()
{
    $timeframe = $this->service_timeframe;
    $baseTime = $this->scheduled_pickup_time ? \Carbon\Carbon::parse($this->scheduled_pickup_time) : ($this->created_at ? $this->created_at->copy() : now());
    return $baseTime->copy()->addMinutes(round($timeframe['hours'] * 60));
}

public function getHoursRemainingAttribute()
{
    $deadline = $this->deadline;
    return round(now()->diffInMinutes($deadline, false) / 60, 1);
}

public function getIsApproachingDeadlineAttribute()
{
    $hoursRemaining = $this->hours_remaining;
    return $hoursRemaining <= 6 && $hoursRemaining > 0 && $this->status !== 'delivered';
}

    /**
     * Check if delivery is delayed
     */
    public function isDelayed()
    {
        return $this->is_delayed;
    }
    
    /**
     * Get status badge color
     */
    public function getStatusBadgeColor()
    {
        switch ($this->status) {
            case 'delivered':
                return 'green';
            case 'out_for_delivery':
                return 'blue';
            case 'arrived_at_partner':
                return 'purple';
            case 'picked_up':
                return 'indigo';
            case 'cancelled':
                return 'red';
            default:
                return $this->is_delayed ? 'red' : 'yellow';
        }
    }
    
    /**
     * Get human readable status
     */
    public function getReadableStatus()
    {
        $statusMap = [
            'pending' => 'Pending',
            'assigned' => 'Assigned to Partner',
            'arrived_at_partner' => 'Arrived at Partner Hub',
            'picked_up' => 'Picked Up',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled'
        ];
        
        $status = $statusMap[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
        
        if ($this->is_delayed && $this->status !== 'delivered') {
            $status .= ' (Delayed)';
        }
        
        return $status;
    }

/**
 * Check if delivery is delayed
 */
public function getIsDelayedAttribute($value)
{
    if ($value) return true;
    
    // Auto-detect delay if deadline passed and not delivered
    if ($this->status !== 'delivered' && $this->status !== 'cancelled') {
        $hoursRemaining = $this->hours_remaining;
        if ($hoursRemaining < 0) {
            return true;
        }
    }
    
    return false;
}

}
