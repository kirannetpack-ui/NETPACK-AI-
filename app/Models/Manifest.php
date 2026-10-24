<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Manifest extends Model
{
    protected $fillable = [
        'manifest_number',
        'created_by',
        'partner_id',
        'mawb_id',
        'mawb_number',
        'hub_id',
        'agency_id',
        'service_type', // express, economy
        'manifest_type', // international, domestic
        'flight_number',
        'flight_date',
        'load_type',
        'status',
        'origin_city',
        'destination_city',
        'current_location',
        'total_bags',
        'total_shipments',
        'total_weight',
        'dispatched_at',
        'received_at',
        'delivered_at',
        'agency_emails_sent_at',
        'agency_emails_sent_to',
        'metadata',
        'pod_uploaded_at',
        'pod_uploaded_by',
        'pod_file',
        'pod_notes',
    ];

    protected $casts = [
        'flight_date' => 'date',
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
        'delivered_at' => 'datetime',
        'agency_emails_sent_at' => 'datetime',
        'agency_emails_sent_to' => 'array',
        'metadata' => 'array',
        'total_weight' => 'decimal:2',
        'pod_uploaded_at' => 'datetime',
    ];

    public function getAgencyEmailsSentToAttribute($value)
    {
        if (is_null($value)) return [];
        if (is_array($value)) return $value;
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $value)));
    }

    public function setAgencyEmailsSentToAttribute($value)
    {
        $this->attributes['agency_emails_sent_to'] = is_array($value) ? json_encode($value) : $value;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function mawb()
    {
        return $this->belongsTo(MAWB::class, 'mawb_id');
    }

    public function hub()
    {
        return $this->belongsTo(OverseasHub::class, 'hub_id');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function bags()
    {
        return $this->hasMany(ManifestBag::class);
    }

    public function shipments()
    {
        return $this->hasMany(ManifestShipment::class);
    }

    public function trackingLogs()
    {
        return $this->hasMany(ManifestTrackingLog::class);
    }

    public function scopeInternational($query)
    {
        return $query->where('manifest_type', 'international');
    }

    public function scopeDomestic($query)
    {
        return $query->where(function($q) {
            $q->where('manifest_type', 'domestic')
              ->orWhereNull('manifest_type')
              ->orWhere('manifest_type', '!=', 'international');
        });
    }

    public static function countInternational(): int
    {
        try {
            return (int) static::where('manifest_type', 'international')->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public static function countDomestic(): int
    {
        try {
            return (int) static::where(function($q) {
                $q->where('manifest_type', '!=', 'international')
                  ->orWhereNull('manifest_type');
            })->count();
        } catch (\Throwable $e) {
            return (int) static::count();
        }
    }

    public static function generateManifestNumber(string $type = 'MF')
    {
        $prefix = $type === 'international' ? 'IMNF' : 'MF';
        $date = date('Ymd');
        $random = Str::upper(Str::random(6));
        $manifestNumber = $prefix . '-' . $date . '-' . $random;
        
        while (self::where('manifest_number', $manifestNumber)->exists()) {
            $random = Str::upper(Str::random(6));
            $manifestNumber = $prefix . '-' . $date . '-' . $random;
        }
        
        return $manifestNumber;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'in_transit' => 'bg-blue-100 text-blue-800',
            'received' => 'bg-purple-100 text-purple-800',
            'dispatched' => 'bg-indigo-100 text-indigo-800',
            'delivered' => 'bg-green-100 text-green-800',
        ];
        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'in_transit' => 'In Transit',
            'received' => 'Received',
            'dispatched' => 'Dispatched',
            'delivered' => 'Delivered',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function addTrackingLog($eventType, $description = null, $location = null, $bagId = null, $shipmentId = null)
    {
        return ManifestTrackingLog::create([
            'manifest_id' => $this->id,
            'bag_id' => $bagId,
            'shipment_id' => $shipmentId,
            'event_type' => $eventType,
            'location' => $location,
            'description' => $description,
            'performed_by' => auth()->id(),
        ]);
    }

    public function podUploadedBy()
    {
        return $this->belongsTo(User::class, 'pod_uploaded_by');
    }
}