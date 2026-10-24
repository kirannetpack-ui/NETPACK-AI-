<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerPaymentMethod extends Model
{
    protected $fillable = [
        'user_id',
        'method_type', // bank, esewa, khalti, connectips
        'is_default',
        'is_verified',
        'account_name',
        'account_number',
        'bank_name',
        'branch',
        'account_type', // savings, current
        'mobile_number', // for eSewa/Khalti
        'esewa_id',
        'khalti_id',
        'connectips_id',
        'verification_document',
        'verified_at',
        'metadata',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getMethodLabelAttribute()
    {
        $labels = [
            'bank' => '🏦 Bank Account',
            'esewa' => '📱 eSewa',
            'khalti' => '📱 Khalti',
            'connectips' => '📱 ConnectIPS',
        ];
        return $labels[$this->method_type] ?? $this->method_type;
    }

    public function getDisplayNameAttribute()
    {
        if ($this->method_type === 'bank') {
            return "{$this->bank_name} - {$this->account_number}";
        } elseif ($this->method_type === 'esewa') {
            return "eSewa: {$this->esewa_id}";
        } elseif ($this->method_type === 'khalti') {
            return "Khalti: {$this->khalti_id}";
        } else {
            return $this->account_name;
        }
    }
}