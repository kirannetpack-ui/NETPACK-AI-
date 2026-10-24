<?php
// app/Models/PaymentIntent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentIntent extends Model
{
    protected $fillable = [
        'intent_id', 'shipment_id', 'customer_id', 'seller_id', 'rider_id',
        'total_amount', 'split_breakdown', 'split_percentages', 'status',
        'payment_gateway', 'gateway_transaction_id', 'gateway_response', 'paid_at'
    ];
    
    protected $casts = [
        'split_breakdown' => 'array',
        'split_percentages' => 'array',
        'gateway_response' => 'array',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime'
    ];
    
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
    
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
    
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    
    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }
    
    public function settlements()
    {
        return $this->hasMany(Settlement::class);
    }
}