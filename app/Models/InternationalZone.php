<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternationalZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'countries',
        'hub_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'countries' => 'array',
        'is_active' => 'boolean',
    ];

    public function hub()
    {
        return $this->belongsTo(OverseasHub::class, 'hub_id');
    }

    public function rates()
    {
        return $this->hasMany(InternationalRate::class, 'zone_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function containsCountry(string $country): bool
    {
        if (empty($this->countries) || !is_array($this->countries)) {
            return false;
        }

        $needle = strtolower(trim($country));
        foreach ($this->countries as $c) {
            if (strtolower(trim($c)) === $needle) {
                return true;
            }
        }

        return false;
    }
}
