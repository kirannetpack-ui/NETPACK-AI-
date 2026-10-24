<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiderDeposit extends Model
{
    protected $table = 'rider_deposits';

    protected $fillable = [
        'rider_id',
        'amount',
        'balance',
        'type', // deposit, withdrawal, settlement, adjustment
        'reference_type', // cash, bank, wallet
        'reference_id',
        'description',
        'status', // pending, completed, failed
        'verified_at',
        'verified_by',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'deposit' => '💰 Deposit',
            'withdrawal' => '📤 Withdrawal',
            'settlement' => '📋 Settlement',
            'adjustment' => '⚖️ Adjustment',
        ];
        return $labels[$this->type] ?? $this->type;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
        ];
        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }
}