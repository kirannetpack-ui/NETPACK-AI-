<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CODSettlement extends Model
{
    protected $table = 'cod_settlements';

    protected $fillable = [
        'order_id',
        'delivery_id',
        'seller_id',
        'rider_id',
        'cod_amount',
        'delivery_charge',
        'admin_margin',
        'seller_amount',
        'rider_amount',
        'margin_amount',
        'settlement_status', // pending, processing, completed, failed
        'settlement_date',
        'settlement_reference',
        'invoice_file',
        'collected_at',
        'verified_at',
        'verified_by',
        'remarks',
        'metadata',
    ];

    protected $casts = [
        'cod_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'admin_margin' => 'decimal:2',
        'seller_amount' => 'decimal:2',
        'rider_amount' => 'decimal:2',
        'margin_amount' => 'decimal:2',
        'settlement_date' => 'datetime',
        'collected_at' => 'datetime',
        'verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
        ];
        return $badges[$this->settlement_status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending Collection',
            'processing' => 'Processing',
            'completed' => 'Settled',
            'failed' => 'Failed',
        ];
        return $labels[$this->settlement_status] ?? $this->settlement_status;
    }
}