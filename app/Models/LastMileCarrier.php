<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LastMileCarrier extends Model
{
    use HasFactory;

    protected $table = 'last_mile_carriers';

    protected $fillable = [
        'name',
        'code',
        'hub_id',
        'country',
        'service_mode',
        'tracking_url_template',
        'contact_email',
        'contact_phone',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function hub()
    {
        return $this->belongsTo(OverseasHub::class, 'hub_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByHub($query, $hubId)
    {
        return $query->where(function ($q) use ($hubId) {
            $q->where('hub_id', $hubId)->orWhereNull('hub_id');
        });
    }

    public function getTrackingUrl(?string $carrierTrackingNumber): ?string
    {
        if (empty($carrierTrackingNumber) || empty($this->tracking_url_template)) {
            return null;
        }

        return str_replace('{tracking}', urlencode(trim($carrierTrackingNumber)), $this->tracking_url_template);
    }
}
