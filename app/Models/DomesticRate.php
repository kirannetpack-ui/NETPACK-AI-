<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomesticRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'origin_zone_id',
        'destination_zone_id',
        'service_type',
        'rate_type',
        'service_name',
        'base_rate',
        'per_kg_rate',
        'rate_per_kg',
        'origin_city',
        'origin_zone',
        'destination_city',
        'destination_zone',
        'per_km_rate',
        'minimum_rate',
        'logistical_charge',
        'additional_charge',
        'additional_charge_reason',
        'weight_from',
        'weight_to',
        'estimated_hours',
        'estimated_days',
        'estimated_km',
        'is_active',
        'effective_from',
        'effective_to',
        'approval_status',
        'submitted_by',
        'approved_by',
        'submitted_at',
        'approved_at',
        'rejection_reason',
        'pickup_charge',
        'origin_handling_charge',
        'destination_handling_charge',
        'remote_area_surcharge',
        'cod_charge',
        'admin_margin_type',
        'admin_margin_value',
        'is_default_destination',
    ];

    protected $casts = [
        'base_rate' => 'decimal:2',
        'per_kg_rate' => 'decimal:2',
        'per_km_rate' => 'decimal:2',
        'minimum_rate' => 'decimal:2',
        'logistical_charge' => 'decimal:2',
        'additional_charge' => 'decimal:2',
        'weight_from' => 'decimal:2',
        'weight_to' => 'decimal:2',
        'estimated_hours' => 'integer',
        'estimated_days' => 'integer',
        'estimated_km' => 'integer',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'pickup_charge' => 'decimal:2',
        'origin_handling_charge' => 'decimal:2',
        'destination_handling_charge' => 'decimal:2',
        'remote_area_surcharge' => 'decimal:2',
        'cod_charge' => 'decimal:2',
        'admin_margin_value' => 'decimal:2',
        'is_default_destination' => 'boolean',
    ];

    // Service type constants
    const SERVICE_FLASH = 'flash';
    const SERVICE_SAME_DAY = 'same_day';
    const SERVICE_STANDARD = 'standard';
    const SERVICE_HIMALAYAN = 'himalayan';

    const SERVICE_NAMES = [
        'flash' => 'FLASH',
        'same_day' => 'SAME DAY',
        'standard' => 'STANDARD',
        'himalayan' => 'HIMALAYAN',
    ];

    const SERVICE_ICONS = [
        'flash' => '⚡',
        'same_day' => '🕐',
        'standard' => '📦',
        'himalayan' => '🏔️',
    ];

    const SERVICE_DESCRIPTIONS = [
        'flash' => 'Ultra-fast delivery within 2-4 hours',
        'same_day' => 'Same day delivery within the city',
        'standard' => 'Next day delivery within the country',
        'himalayan' => 'Delivery to remote/hilly areas (2-3 days)',
    ];

    const SERVICE_COLORS = [
        'flash' => 'red',
        'same_day' => 'orange',
        'standard' => 'blue',
        'himalayan' => 'purple',
    ];

    const SERVICE_TIME = [
        'flash' => '2-4 hours',
        'same_day' => 'Same day',
        'standard' => '1-2 days',
        'himalayan' => '2-3 days',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function originZone()
    {
        return $this->belongsTo(DeliveryZone::class, 'origin_zone_id');
    }

    public function destinationZone()
    {
        return $this->belongsTo(DeliveryZone::class, 'destination_zone_id');
    }

    public function shipments()
    {
        return $this->hasMany(DomesticShipment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('approval_status', 'approved')
                     ->where(function ($query) {
                         $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', now());
                     })
                     ->where(function($q) {
                         $q->whereDate('effective_to', '>=', now())
                           ->orWhereNull('effective_to');
                     });
    }

    public function scopeByService($query, $serviceType)
    {
        return $query->where('service_type', $serviceType);
    }

    public function scopeByPartner($query, $partnerId)
    {
        return $query->where('partner_id', $partnerId);
    }

    public function scopeByZones($query, $originZoneId, $destinationZoneId)
    {
        return $query->where('origin_zone_id', $originZoneId)
                     ->where('destination_zone_id', $destinationZoneId);
    }

    public function scopeByWeight($query, $weight)
    {
        return $query->where('weight_from', '<=', $weight)
                     ->where('weight_to', '>=', $weight);
    }

    public function getServiceNameAttribute()
    {
        return $this->attributes['service_name'] 
            ?? self::SERVICE_NAMES[$this->service_type] 
            ?? ucwords(str_replace(['_', '-'], ' ', $this->service_type));
    }

    public function getServiceIconAttribute()
    {
        return self::SERVICE_ICONS[$this->service_type] ?? '🏷️';
    }

    public function getServiceDescriptionAttribute()
    {
        return self::SERVICE_DESCRIPTIONS[$this->service_type] ?? 'Custom specialized partner service';
    }

    public function getServiceColorAttribute()
    {
        return self::SERVICE_COLORS[$this->service_type] ?? 'teal';
    }

    public function getServiceTimeAttribute()
    {
        if (!empty($this->estimated_hours)) {
            return $this->estimated_hours . ' hours';
        }
        if (!empty($this->estimated_days)) {
            return $this->estimated_days . ' days';
        }
        return self::SERVICE_TIME[$this->service_type] ?? 'Varies';
    }

    public function getServiceBadgeAttribute()
    {
        $colors = [
            'flash' => 'red',
            'same_day' => 'orange',
            'standard' => 'blue',
            'himalayan' => 'purple',
        ];
        $color = $colors[$this->service_type] ?? 'teal';
        
        return "<span class='px-2 py-1 rounded-full text-xs font-medium bg-{$color}-100 text-{$color}-800'>{$this->service_icon} {$this->service_name}</span>";
    }

    public function calculateRate($weight, $distance = null)
    {
        $baseRate = max((float)$this->base_rate, (float)$this->minimum_rate);
        $weightCharge = (float)$this->per_kg_rate * (float)$weight;
        $distanceCharge = $distance ? ((float)$this->per_km_rate * (float)$distance) : 0;
        
        $subtotal = $baseRate + $weightCharge + $distanceCharge;
        $total = $subtotal
            + (float)$this->logistical_charge
            + (float)$this->additional_charge
            + (float)$this->pickup_charge
            + (float)$this->origin_handling_charge
            + (float)$this->destination_handling_charge
            + (float)$this->remote_area_surcharge
            + (float)$this->cod_charge;
        
        return [
            'base_rate' => $baseRate,
            'weight_charge' => round($weightCharge, 2),
            'distance_charge' => round($distanceCharge, 2),
            'logistical_charge' => (float)$this->logistical_charge,
            'additional_charge' => (float)$this->additional_charge,
            'pickup_charge' => (float)$this->pickup_charge,
            'origin_handling_charge' => (float)$this->origin_handling_charge,
            'destination_handling_charge' => (float)$this->destination_handling_charge,
            'remote_area_surcharge' => (float)$this->remote_area_surcharge,
            'cod_charge' => (float)$this->cod_charge,
            'subtotal' => round($subtotal, 2),
            'total' => round($total, 2),
            'breakdown' => [
                'weight' => (float)$weight,
                'distance' => (float)$distance,
                'per_kg_rate' => (float)$this->per_kg_rate,
                'per_km_rate' => (float)$this->per_km_rate,
                'minimum_rate' => (float)$this->minimum_rate,
            ],
        ];
    }

    public function events()
    {
        return $this->hasMany(DomesticRateEvent::class);
    }

    public function customerPrice(float $partnerCost): array
    {
        $margin = $this->admin_margin_type === 'fixed'
            ? (float) $this->admin_margin_value
            : $partnerCost * ((float) $this->admin_margin_value / 100);

        return [
            'partner_cost' => round($partnerCost, 2),
            'markup_amount' => round($margin, 2),
            'customer_price' => round($partnerCost + $margin, 2),
        ];
    }

    public static function getServiceTypes()
    {
        return [
            self::SERVICE_FLASH,
            self::SERVICE_SAME_DAY,
            self::SERVICE_STANDARD,
            self::SERVICE_HIMALAYAN,
        ];
    }

    public static function getServiceTypeOptions(?int $partnerId = null)
    {
        $options = [];
        foreach (self::getServiceTypes() as $type) {
            $options[$type] = [
                'name' => self::SERVICE_NAMES[$type],
                'icon' => self::SERVICE_ICONS[$type],
                'description' => self::SERVICE_DESCRIPTIONS[$type],
                'color' => self::SERVICE_COLORS[$type],
                'time' => self::SERVICE_TIME[$type],
                'is_custom' => false,
            ];
        }

        try {
            $customQuery = LogisticsService::query()
                ->where('category', 'domestic')
                ->where('is_active', true);

            if ($partnerId) {
                $customQuery->where(function ($q) use ($partnerId) {
                    $q->whereNull('partner_id')->orWhere('partner_id', $partnerId);
                });
            }

            $customServices = $customQuery->get();
            foreach ($customServices as $custom) {
                if (!isset($options[$custom->code])) {
                    $options[$custom->code] = [
                        'name' => $custom->name,
                        'icon' => '✨',
                        'description' => $custom->description ?? 'Custom specialized partner service',
                        'color' => 'teal',
                        'time' => $custom->transit_display,
                        'is_custom' => true,
                        'partner_id' => $custom->partner_id,
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Graceful fallback to standard options
        }

        return $options;
    }
}
