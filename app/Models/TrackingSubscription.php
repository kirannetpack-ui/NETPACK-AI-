<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingSubscription extends Model
{
    use HasFactory;

    protected $table = 'tracking_subscriptions';

    protected $fillable = [
        'tracking_number',
        'email',
        'phone',
        'notify_email',
        'notify_sms',
        'is_active',
    ];

    protected $casts = [
        'notify_email' => 'boolean',
        'notify_sms' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTracking($query, string $trackingNumber)
    {
        return $query->where('tracking_number', strtoupper(trim($trackingNumber)));
    }
}
