<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverseasTransitPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'name',
        'type',
        'location',
        'country',
        'is_mandatory',
        'is_active',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Types
    const TYPE_HUB = 'hub';
    const TYPE_TRANSIT = 'transit';

    const TYPES = [
        self::TYPE_HUB => 'Main Hub',
        self::TYPE_TRANSIT => 'Transit Point',
    ];

    const TYPE_COLORS = [
        self::TYPE_HUB => 'purple',
        self::TYPE_TRANSIT => 'blue',
    ];

    const TYPE_ICONS = [
        self::TYPE_HUB => '🏢',
        self::TYPE_TRANSIT => '📍',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function shipments()
    {
        return $this->belongsToMany(Shipment::class, 'shipment_transit_points')
                    ->withPivot(['arrived_at', 'departed_at', 'status', 'notes', 'sequence'])
                    ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPartner($query, $partnerId)
    {
        return $query->where('partner_id', $partnerId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    public function getTypeLabelAttribute()
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getTypeColorAttribute()
    {
        return self::TYPE_COLORS[$this->type] ?? 'gray';
    }

    public function getTypeIconAttribute()
    {
        return self::TYPE_ICONS[$this->type] ?? '📍';
    }

    public function getFullLocationAttribute()
    {
        return $this->location . ', ' . $this->country;
    }

    public function getIsMandatoryLabelAttribute()
    {
        return $this->is_mandatory ? 'Yes' : 'No';
    }
}