<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryReminder extends Model
{
    protected $table = 'delivery_reminders';
    
    protected $fillable = [
        'pickup_request_id',
        'manifest_id',
        'service_tier',
        'reminder_type',
        'reminder_number',
        'scheduled_at',
        'sent_at',
        'is_sent',
        'message',
        'metadata'
    ];
    
    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'is_sent' => 'boolean',
        'metadata' => 'array'
    ];
    
    /**
     * Get the pickup request that owns this reminder
     */
    public function pickupRequest()
    {
        return $this->belongsTo(PickupRequest::class, 'pickup_request_id');
    }

    /**
     * Get the manifest that owns this reminder
     */
    public function manifest()
    {
        return $this->belongsTo(Manifest::class, 'manifest_id');
    }
    
    /**
     * Get the reminder logs for this reminder
     */
    public function logs()
    {
        return $this->hasMany(ReminderLog::class, 'reminder_id');
    }
    
    /**
     * Scope for pending reminders
     */
    public function scopePending($query)
    {
        return $query->where('is_sent', false)
                     ->where('scheduled_at', '<=', now());
    }
    
    /**
     * Scope for unsent reminders
     */
    public function scopeUnsent($query)
    {
        return $query->where('is_sent', false);
    }
    
    /**
     * Mark reminder as sent
     */
    public function markAsSent()
    {
        $this->update([
            'is_sent' => true,
            'sent_at' => now()
        ]);
    }
}