<?php
// app/Services/DomesticService.php

namespace App\Services;

use App\Models\Shipment;
use App\Models\Rider;
use App\Models\DeliveryZone;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DomesticService
{
    // Service Tiers
    const TIER_FLASH = 'flash';      // 2-4 hours
    const TIER_SAME_DAY = 'same_day'; // By 8 PM
    const TIER_STANDARD = 'standard'; // 1-3 days
    const TIER_HIMALAYAN = 'himalayan'; // 3-7 days
    
    // Pricing structure
    public function calculateDomesticRate($pickupLat, $pickupLng, $deliveryLat, $deliveryLng, $weight, $tier = 'standard')
    {
        $distance = $this->calculateDistance($pickupLat, $pickupLng, $deliveryLat, $deliveryLng);
        $zone = $this->getZoneFromLocation($deliveryLat, $deliveryLng);
        
        $rates = $this->getTierRates($tier);
        
        $rate = $rates['base'] 
              + ($rates['per_km'] * $distance) 
              + ($rates['per_kg'] * $weight);
        
        // Add remote area surcharge
        if ($zone['is_remote']) {
            $rate *= 1.4;
        }
        
        // Add Himalayan surcharge
        if ($tier === self::TIER_HIMALAYAN) {
            $rate += 2000;
        }
        
        return [
            'total' => round($rate, 2),
            'breakdown' => [
                'base_rate' => $rates['base'],
                'distance_charge' => round($rates['per_km'] * $distance, 2),
                'weight_charge' => round($rates['per_kg'] * $weight, 2),
                'distance_km' => round($distance, 2),
                'zone' => $zone['name'],
                'tier' => $tier,
            ]
        ];
    }
    
    private function getTierRates($tier)
    {
        $rates = [
            self::TIER_FLASH => ['base' => 150, 'per_km' => 20, 'per_kg' => 25, 'max_hours' => 4],
            self::TIER_SAME_DAY => ['base' => 100, 'per_km' => 15, 'per_kg' => 20, 'max_hours' => 12],
            self::TIER_STANDARD => ['base' => 80, 'per_km' => 10, 'per_kg' => 15, 'max_days' => 3],
            self::TIER_HIMALAYAN => ['base' => 500, 'per_km' => 8, 'per_kg' => 30, 'max_days' => 7],
        ];
        
        return $rates[$tier] ?? $rates[self::TIER_STANDARD];
    }
    
    public function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371;
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        
        $a = sin($latDelta/2) * sin($latDelta/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDelta/2) * sin($lngDelta/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    public function getZoneFromLocation($lat, $lng)
    {
        // Define Kathmandu Valley boundaries (approximate)
        $kathmanduBounds = [
            'min_lat' => 27.65, 'max_lat' => 27.75,
            'min_lng' => 85.25, 'max_lng' => 85.40
        ];
        
        if ($lat >= $kathmanduBounds['min_lat'] && $lat <= $kathmanduBounds['max_lat'] &&
            $lng >= $kathmanduBounds['min_lng'] && $lng <= $kathmanduBounds['max_lng']) {
            return ['name' => 'Kathmandu Valley', 'is_remote' => false];
        }
        
        // Define Terai region
        $teraiBounds = [
            'min_lat' => 26.5, 'max_lat' => 27.2,
            'min_lng' => 80.0, 'max_lng' => 88.0
        ];
        
        if ($lat >= $teraiBounds['min_lat'] && $lat <= $teraiBounds['max_lat']) {
            return ['name' => 'Terai', 'is_remote' => false];
        }
        
        // Define Himalayan region
        $himalayanDistricts = ['Mustang', 'Dolpa', 'Humla', 'Jumla', 'Mugu', 'Kalikot'];
        
        return ['name' => 'Hilly/Himalayan', 'is_remote' => true];
    }
    
    public function assignNearestRider($pickupLat, $pickupLng, $zone)
    {
        return Rider::where('assigned_zone', $zone)
            ->where('is_available', true)
            ->orderByRaw("ST_Distance_Sphere(point(current_longitude, current_latitude), point(?, ?))", [$pickupLng, $pickupLat])
            ->first();
    }
    
    public function getEstimatedDeliveryTime($pickupLat, $pickupLng, $deliveryLat, $deliveryLng, $tier)
    {
        $distance = $this->calculateDistance($pickupLat, $pickupLng, $deliveryLat, $deliveryLng);
        $tierRates = $this->getTierRates($tier);
        
        if ($tier === self::TIER_FLASH) {
            $hours = max(2, ceil($distance / 15));
            return $hours . ' hours';
        } elseif ($tier === self::TIER_SAME_DAY) {
            return 'Today by 8 PM';
        } elseif ($tier === self::TIER_HIMALAYAN) {
            $days = max(3, ceil($distance / 200));
            return $days . ' - 7 days';
        } else {
            $days = max(1, ceil($distance / 100));
            return $days . ' - 3 days';
        }
    }
}