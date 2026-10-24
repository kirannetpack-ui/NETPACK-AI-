<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcommerceOrder extends Model
{
    protected $table = 'pickup_requests';
    
    protected $fillable = [
        'seller_id', 'order_id', 'assigned_rider_id', 'is_ecommerce', 'platform', 'order_reference',
        'cod_amount', 'platform_fee', 'seller_earnings', 'customer_name', 'customer_phone',
        'customer_email', 'product_items', 'payment_status', 'pickup_address', 'pickup_ward_no',
        'pickup_municipality', 'pickup_district', 'delivery_address', 'delivery_ward_no',
        'delivery_municipality', 'delivery_district', 'service_tier', 'status',
        'estimated_weight_kg', 'actual_weight_kg', 'scheduled_pickup_time', 'tracking_number', 'qr_code',
        'picked_up_at', 'delivered_at', 'otp_code', 'delivery_proof_image'
    ];
    
    protected $casts = [
        'product_items' => 'array',
        'is_ecommerce' => 'boolean',
        'cod_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'seller_earnings' => 'decimal:2',
        'estimated_weight_kg' => 'decimal:2',
        'actual_weight_kg' => 'decimal:2',
        'scheduled_pickup_time' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime'
    ];
    
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    
    public function rider()
    {
        return $this->belongsTo(User::class, 'assigned_rider_id');
    }
    
    public function getPlatformBadgeAttribute()
    {
        $badges = [
            'daraz' => 'bg-orange-100 text-orange-700',
            'hamrobazar' => 'bg-blue-100 text-blue-700',
            'sastodeal' => 'bg-green-100 text-green-700',
            'facebook' => 'bg-indigo-100 text-indigo-700',
            'custom' => 'bg-gray-100 text-gray-700'
        ];
        
        return $badges[$this->platform] ?? $badges['custom'];
    }
    
    public function getPlatformIconAttribute()
    {
        $icons = [
            'daraz' => 'fab fa-daraz',
            'hamrobazar' => 'fas fa-exchange-alt',
            'sastodeal' => 'fas fa-tag',
            'facebook' => 'fab fa-facebook',
            'custom' => 'fas fa-store'
        ];
        
        return $icons[$this->platform] ?? $icons['custom'];
    }
}
