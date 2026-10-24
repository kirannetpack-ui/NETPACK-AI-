<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'latitude',
        'longitude',
        'location_name',
        'status',
        'recorded_at',
        'speed',
        'accuracy',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'speed' => 'decimal:2',
        'accuracy' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function scopeRecent($query, $limit = 20)
    {
        return $query->orderBy('recorded_at', 'desc')->limit($limit);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function getFormattedLocationAttribute()
    {
        if ($this->location_name) {
            return $this->location_name;
        }
        if ($this->latitude && $this->longitude) {
            return $this->latitude . ', ' . $this->longitude;
        }
        return 'Unknown';
    }

    public function getGoogleMapsUrlAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return 'https://www.google.com/maps?q=' . $this->latitude . ',' . $this->longitude;
        }
        return null;
    }
}