<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemoteAreaSurcharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'overseas_partner_id',
        'country',
        'zip_code_pattern',
        'area_name',
        'surcharge_amount',
        'surcharge_percentage',
        'is_active',
        'source',
        'source_updated_at',
        'service_conditions',
        'currency',
    ];

    protected $casts = [
        'surcharge_amount' => 'decimal:2',
        'surcharge_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'source_updated_at' => 'datetime',
    ];

    public function overseasPartner()
    {
        return $this->belongsTo(User::class, 'overseas_partner_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a zip code matches the pattern
     * Supports:
     * - Exact match: 10001
     * - Wildcard (*): 995*
     * - Range (10000-20000)
     * - Multiple ranges: 10000-20000, 30000-40000
     * - Partial match: 100
     */
    public static function matchesZipCode($pattern, $zipCode)
    {
        // Trim and clean
        $pattern = trim($pattern);
        $zipCode = trim($zipCode);
        
        if (empty($pattern) || empty($zipCode)) {
            return false;
        }

        // 1. Exact match
        if ($pattern === $zipCode) {
            return true;
        }

        // 2. Wildcard match (*)
        if (strpos($pattern, '*') !== false) {
            $patternRegex = str_replace('*', '.*', preg_quote($pattern, '/'));
            return preg_match('/^' . $patternRegex . '$/', $zipCode) === 1;
        }

        // 3. Multiple ranges (comma separated)
        if (strpos($pattern, ',') !== false) {
            $ranges = explode(',', $pattern);
            foreach ($ranges as $range) {
                $range = trim($range);
                if (self::matchesZipCode($range, $zipCode)) {
                    return true;
                }
            }
            return false;
        }

        // 4. Range match (e.g., 10000-20000)
        if (strpos($pattern, '-') !== false) {
            $parts = explode('-', $pattern);
            if (count($parts) === 2) {
                $start = trim($parts[0]);
                $end = trim($parts[1]);
                
                // Both are numeric
                if (is_numeric($start) && is_numeric($end) && is_numeric($zipCode)) {
                    $zipNum = intval($zipCode);
                    return $zipNum >= intval($start) && $zipNum <= intval($end);
                }
                
                // Handle partial ranges (e.g., 10000-20000 where zip is like 10000-20000)
                if (strpos($zipCode, '-') !== false) {
                    $zipParts = explode('-', $zipCode);
                    if (count($zipParts) === 2) {
                        $zipStart = trim($zipParts[0]);
                        $zipEnd = trim($zipParts[1]);
                        if (is_numeric($zipStart) && is_numeric($zipEnd)) {
                            return intval($zipStart) >= intval($start) && intval($zipEnd) <= intval($end);
                        }
                    }
                }
            }
            return false;
        }

        // 5. Prefix/Partial match (e.g., pattern "100" matches "10001", "10002")
        if (strlen($pattern) > 0 && strlen($pattern) < strlen($zipCode)) {
            if (strpos($zipCode, $pattern) === 0) {
                return true;
            }
        }

        // 6. Suffix match (e.g., pattern "01" matches "10001")
        if (strlen($pattern) > 0 && strlen($pattern) < strlen($zipCode)) {
            if (substr($zipCode, -strlen($pattern)) === $pattern) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a zip code and country have surcharge
     */
    public static function checkSurcharge($country, $zipCode, $partnerId = null)
    {
        $query = self::active()->where('country', $country);

        if ($partnerId) {
            $query->where('overseas_partner_id', $partnerId);
        }

        $surcharges = $query->get();

        foreach ($surcharges as $surcharge) {
            if (self::matchesZipCode($surcharge->zip_code_pattern, $zipCode)) {
                return $surcharge;
            }
        }

        return null;
    }

    /**
     * Get all surcharges for a country (for display)
     */
    public static function getSurchargesForCountry($country, $partnerId = null)
    {
        $query = self::active()->where('country', $country);
        
        if ($partnerId) {
            $query->where('overseas_partner_id', $partnerId);
        }
        
        return $query->orderBy('zip_code_pattern')->get();
    }

    /**
     * Get surcharge amount based on base price
     */
    public function calculateSurcharge($baseAmount)
    {
        $surcharge = 0;
        if ($this->surcharge_amount > 0) {
            $surcharge = $this->surcharge_amount;
        } elseif ($this->surcharge_percentage > 0) {
            $surcharge = $baseAmount * ($this->surcharge_percentage / 100);
        }
        return $surcharge;
    }

    /**
     * Get formatted zip pattern for display
     */
    public function getFormattedZipPatternAttribute()
    {
        if (strpos($this->zip_code_pattern, ',') !== false) {
            return 'Multiple ranges';
        }
        if (strpos($this->zip_code_pattern, '-') !== false) {
            return 'Range: ' . $this->zip_code_pattern;
        }
        if (strpos($this->zip_code_pattern, '*') !== false) {
            return 'Wildcard: ' . $this->zip_code_pattern;
        }
        return $this->zip_code_pattern;
    }
}
