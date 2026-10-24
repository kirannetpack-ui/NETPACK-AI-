<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'seller_id',
        'customer_id',
        'client_id',
        'rider_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'shipping_address',
        'billing_address',
        'delivery_latitude',
        'delivery_longitude',
        'total_amount',
        'subtotal',
        'tax',
        'shipping_cost',
        'discount',
        'status',
        'payment_method',
        'payment_status',
        'payment_id',
        'order_date',
        'delivery_date',
        'delivery_time_slot',
        'special_instructions',
        'rider_assigned_at',
        'rider_acceptance_time',
        'picked_up_at',
        'out_for_delivery_at',
        'delivered_at',
        'tracking_number',
        'admin_notes',
        'customer_notes',
        'metadata',
        'distance',
        'estimated_time',
'delivery_type', // 'single', 'multiple'
    'delivery_count',
    'delivery_data', // JSON for multiple deliveries

  'cod_amount',
    'cod_invoice_file',
    'cod_collected_amount',
    'cod_collected_at',
    'cod_verified_at',
    'cod_verified_by',
    'cod_status', // pending, collected, verified, settled
    'delivery_charge',
    'admin_margin',
    'seller_amount',
    'rider_amount',
    'margin_amount',
    'settlement_status', // pending, processing, completed
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'delivery_date' => 'datetime',
        'rider_assigned_at' => 'datetime',
        'rider_acceptance_time' => 'datetime',
        'picked_up_at' => 'datetime',
        'out_for_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'metadata' => 'array',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'distance' => 'decimal:2',
    'delivery_data' => 'array',

 'cod_amount' => 'decimal:2',
    'cod_collected_amount' => 'decimal:2',
    'cod_collected_at' => 'datetime',
    'cod_verified_at' => 'datetime',
    'delivery_charge' => 'decimal:2',
    'admin_margin' => 'decimal:2',
    'seller_amount' => 'decimal:2',
    'rider_amount' => 'decimal:2',
    'margin_amount' => 'decimal:2',

    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

/**
 * Get the delivery for this order.
 */
public function delivery()
{
    return $this->hasOne(Delivery::class);
}

/**
 * Get the deliveries for this order.
 */
public function deliveries()
{
    return $this->hasMany(Delivery::class);
}

/**
 * Get the shipment for this order.
 */
public function shipment()
{
    return $this->hasOne(Shipment::class, 'order_id');
}

/**
 * Get the shipments for this order.
 */
public function shipments()
{
    return $this->hasMany(Shipment::class, 'order_id');
}
    public function pickupRequest()
    {
        return $this->hasOne(PickupRequest::class);
    }

    public function trackingLocations()
    {
        return $this->hasMany(OrderTrackingLocation::class);
    }

/**
     * Get the customer for this order.
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

/**
     * Get the product for this order.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
/**
     * Get the transactions for this order.
     */
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    // =============================================
    // SCOPES
    // =============================================
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAssigned($query)
    {
        return $query->whereNotNull('rider_id')->where('status', 'assigned');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['delivered', 'cancelled', 'failed']);
    }

    // =============================================
    // ACCESSORS & MUTATORS
    // =============================================
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'assigned' => 'bg-blue-100 text-blue-800',
            'picked_up' => 'bg-purple-100 text-purple-800',
            'in_transit' => 'bg-indigo-100 text-indigo-800',
            'out_for_delivery' => 'bg-orange-100 text-orange-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'failed' => 'bg-gray-100 text-gray-800',
        ];
        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending Assignment',
            'assigned' => 'Rider Assigned',
            'picked_up' => 'Picked Up',
            'in_transit' => 'In Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'failed' => 'Delivery Failed',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    // =============================================
    // HELPER METHODS
    // =============================================
    
    /**
     * Generate a unique tracking number
     */
    public static function generateTrackingNumber()
    {
        return app(\App\Services\TrackingNumberService::class)->ecommerce();
    }

    /**
     * Generate a unique order number
     */
    public static function generateOrderNumber()
    {
        $prefix = 'ORD';
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $orderNumber = $prefix . '-' . $year . $month . $day . '-' . $random;
        
        while (self::where('order_number', $orderNumber)->exists()) {
            $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $orderNumber = $prefix . '-' . $year . $month . $day . '-' . $random;
        }
        
        return $orderNumber;
    }

    /**
     * Check if order is deliverable
     */
    public function isDeliverable()
    {
        return in_array($this->status, ['pending', 'assigned', 'picked_up', 'in_transit', 'out_for_delivery']);
    }

    /**
     * Check if order is completed
     */
    public function isCompleted()
    {
        return in_array($this->status, ['delivered', 'cancelled', 'failed']);
    }

    /**
     * Get progress percentage
     */
    public function getProgressAttribute()
    {
        $progress = [
            'pending' => 0,
            'assigned' => 20,
            'picked_up' => 40,
            'in_transit' => 60,
            'out_for_delivery' => 80,
            'delivered' => 100,
            'cancelled' => 0,
            'failed' => 0,
        ];
        return $progress[$this->status] ?? 0;
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (blank($order->tracking_number)) {
                $order->tracking_number = self::generateTrackingNumber();
            }
        });
    }
}
