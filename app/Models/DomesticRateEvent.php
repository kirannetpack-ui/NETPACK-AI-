<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomesticRateEvent extends Model
{
    protected $fillable = [
        'domestic_rate_id', 'event_type', 'performed_by', 'notes', 'before', 'after',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    public function rate()
    {
        return $this->belongsTo(DomesticRate::class, 'domestic_rate_id');
    }
}
