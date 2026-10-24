<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'pending_balance',
        'total_earned',
        'total_withdrawn',
        'currency',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function paymentMethods()
    {
        return $this->hasMany(SellerPaymentMethod::class, 'user_id', 'user_id');
    }

    public function defaultPaymentMethod()
    {
        return $this->hasOne(SellerPaymentMethod::class, 'user_id', 'user_id')->where('is_default', true);
    }

    public function getDefaultPaymentMethodAttribute()
    {
        return $this->paymentMethods()->where('is_default', true)->where('is_verified', true)->first();
    }

    public function hasPaymentMethod()
    {
        return $this->paymentMethods()->where('is_verified', true)->exists();
    }

    // Add to balance
    public function addBalance($amount, $description = null, $source = null)
    {
        $balanceBefore = $this->balance;
        $this->balance += $amount;
        $this->total_earned += $amount;
        $this->save();

        $this->transactions()->create([
            'amount' => $amount,
            'type' => 'credit',
            'description' => $description,
            'source' => $source,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'status' => 'completed',
        ]);

        return $this;
    }

    // Deduct from balance
    public function deductBalance($amount, $description = null, $source = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }

        $balanceBefore = $this->balance;
        $this->balance -= $amount;
        $this->total_withdrawn += $amount;
        $this->save();

        $this->transactions()->create([
            'amount' => $amount,
            'type' => 'debit',
            'description' => $description,
            'source' => $source,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'status' => 'completed',
        ]);

        return $this;
    }
}
