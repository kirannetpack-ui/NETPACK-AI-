<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'partner_user_id',
        'admin_id',
        'zone_name',
        'zone_code',
        'zone_type',
        'province',
        'district',
        'districts',
        'municipalities',
        'wards',
        'postal_codes',
        'description',
        'is_active',
        'approval_status',
        'approved_at',
        'rejection_reason',
        'approval_status',
        'approved_at',
        'rejection_reason',
        // Rate fields for each service
        'flash_base_rate',
        'flash_per_kg_rate',
        'flash_estimated_hours',
        'same_day_base_rate',
        'same_day_per_kg_rate',
        'same_day_estimated_hours',
        'standard_base_rate',
        'standard_per_kg_rate',
        'standard_estimated_hours',
        'himalayan_base_rate',
        'himalayan_per_kg_rate',
        'himalayan_estimated_hours',
        // Admin margin fields
        'admin_margin_type',
        'admin_margin_value',
    ];

    protected $casts = [
        'districts' => 'array',
        'municipalities' => 'array',
        'wards' => 'array',
        'postal_codes' => 'array',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
        'approved_at' => 'datetime',
        'flash_base_rate' => 'decimal:2',
        'flash_per_kg_rate' => 'decimal:2',
        'same_day_base_rate' => 'decimal:2',
        'same_day_per_kg_rate' => 'decimal:2',
        'standard_base_rate' => 'decimal:2',
        'standard_per_kg_rate' => 'decimal:2',
        'himalayan_base_rate' => 'decimal:2',
        'himalayan_per_kg_rate' => 'decimal:2',
        'admin_margin_value' => 'decimal:2',
    ];

    /**
     * Get the partner that owns this zone
     */
    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_user_id');
    }

    public function legacyPartner()
    {
        return $this->belongsTo(DomesticPartner::class, 'partner_id');
    }

/**
 * Get the admin who created this zone
 */
public function admin()
{
    return $this->belongsTo(User::class, 'admin_id');
}

/**
 * Scope for partner zones
 */
public function scopePartnerZones($query, $partnerId)
{
    return $query->where('partner_user_id', $partnerId);
}

/**
 * Scope for admin zones
 */
public function scopeAdminZones($query)
{
    return $query->whereNull('partner_id')->whereNull('partner_user_id');
}
    /**
     * Get the domestic rates where this zone is the origin
     */
    public function originRates()
    {
        return $this->hasMany(DomesticRate::class, 'origin_zone_id');
    }

    /**
     * Get the domestic rates where this zone is the destination
     */
    public function destinationRates()
    {
        return $this->hasMany(DomesticRate::class, 'destination_zone_id');
    }

    /**
     * Get service rates for a specific service type
     */
    public function getServiceRates($serviceType)
    {
        $serviceMap = [
            'flash' => ['base' => 'flash_base_rate', 'per_kg' => 'flash_per_kg_rate', 'hours' => 'flash_estimated_hours'],
            'same_day' => ['base' => 'same_day_base_rate', 'per_kg' => 'same_day_per_kg_rate', 'hours' => 'same_day_estimated_hours'],
            'standard' => ['base' => 'standard_base_rate', 'per_kg' => 'standard_per_kg_rate', 'hours' => 'standard_estimated_hours'],
            'himalayan' => ['base' => 'himalayan_base_rate', 'per_kg' => 'himalayan_per_kg_rate', 'hours' => 'himalayan_estimated_hours'],
        ];

        if (!isset($serviceMap[$serviceType])) {
            return null;
        }

        $fields = $serviceMap[$serviceType];
        return [
            'base_rate' => $this->{$fields['base']} ?? 0,
            'per_kg_rate' => $this->{$fields['per_kg']} ?? 0,
            'estimated_hours' => $this->{$fields['hours']} ?? null,
        ];
    }

    /**
     * Get zone type label
     */
    public function getZoneTypeLabelAttribute()
    {
        $types = [
            'urban' => '🏙️ Urban',
            'semi_urban' => '🏘️ Semi-Urban',
            'rural' => '🌾 Rural',
            'hilly' => '⛰️ Hilly',
            'himalayan' => '🏔️ Himalayan',
        ];
        return $types[$this->zone_type] ?? $this->zone_type;
    }

    /**
     * Get districts as comma separated string
     */
    public function getDistrictsListAttribute()
    {
        if (is_array($this->districts)) {
            return implode(', ', $this->districts);
        }
        return $this->districts;
    }

    /**
     * Scope for active zones
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for zones by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('zone_type', $type);
    }

}
