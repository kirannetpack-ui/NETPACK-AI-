<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomesticPartnerAssignment extends Model
{
    protected $fillable = [
        'zone_id', 'partner_id', 'leg_type', 'service_type', 'priority',
        'is_default', 'is_active', 'daily_capacity', 'cutoff_time',
        'effective_from', 'effective_to', 'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function zone()
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', today());
            })
            ->where(function ($query) {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', today());
            });
    }
}
