<?php
// app/Models/Settlement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    protected $fillable = [
        'payment_intent_id', 'recipient_user_id', 'recipient_type', 'amount',
        'payout_method', 'payout_reference', 'status', 'retry_count',
        'failure_reason', 'initiated_at', 'completed_at'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime'
    ];
    
    public function paymentIntent()
    {
        return $this->belongsTo(PaymentIntent::class);
    }
    
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}