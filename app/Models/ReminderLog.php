<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderLog extends Model
{
    protected $table = 'reminder_logs';
    
    protected $fillable = [
        'pickup_request_id',
        'reminder_id',
        'reminder_type', // partner, admin, customer, system, delay_alert
        'sent_to',
        'message',
        'channel', // email, sms, push, database
        'status', // pending, sent, failed
        'sent_at',
        'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the pickup request for this log
     */
    public function pickupRequest()
    {
        return $this->belongsTo(PickupRequest::class);
    }

    /**
     * Get the reminder for this log
     */
    public function reminder()
    {
        return $this->belongsTo(DeliveryReminder::class, 'reminder_id');
    }

    /**
     * Get the partner for this log
     */
    public function partner()
    {
        return $this->belongsTo(User::class, 'sent_to', 'email');
    }

    /**
     * Scope for partner reminders
     */
    public function scopePartner($query)
    {
        return $query->where('reminder_type', 'partner');
    }

    /**
     * Scope for admin reminders
     */
    public function scopeAdmin($query)
    {
        return $query->where('reminder_type', 'admin');
    }

    /**
     * Scope for customer reminders
     */
    public function scopeCustomer($query)
    {
        return $query->where('reminder_type', 'customer');
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}