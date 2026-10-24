<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTrackingLocation extends Model
{
    protected $fillable = [
        'order_id',
        'rider_id',
        'latitude',
        'longitude',
        'accuracy',
        'speed',
        'bearing',
        'altitude',
        'location_type',
        'status',
        'timestamp',
        'metadata',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }
}