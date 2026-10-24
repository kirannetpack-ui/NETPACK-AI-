<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ManifestBag extends Model
{
    protected $fillable = [
        'manifest_id',
        'bag_number',
        'qr_code',
        'bag_type',
        'shipment_count',
        'weight',
        'status',
        'current_location',
        'scanned_at',
        'sorted_at',
        'dispatched_at',
        'metadata',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'sorted_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'weight' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($bag) {
            if (empty($bag->bag_type)) {
                $bag->bag_type = 'consolidated';
            }
        });
    }

    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }

    public function shipments()
    {
        return $this->hasMany(ManifestShipment::class);
    }

    public static function generateBagNumber()
    {
        $prefix = 'BAG';
        $date = date('Ymd');
        $random = Str::upper(Str::random(4));
        return $prefix . '-' . $date . '-' . $random;
    }

    public static function generateQRCode()
    {
        return 'QR-' . Str::upper(Str::random(12));
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'scanned' => 'bg-blue-100 text-blue-800',
            'sorted' => 'bg-purple-100 text-purple-800',
            'dispatched' => 'bg-indigo-100 text-indigo-800',
        ];
        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }
}