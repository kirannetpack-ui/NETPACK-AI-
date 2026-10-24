<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MAWB extends Model
{
    use HasFactory;

    protected $table = 'mawbs';

    protected $fillable = [
        'mawb_number',
        'airline_name',
        'airline_code',
        'origin_airport',
        'destination_airport',
        'hub_id',
        'flight_number',
        'flight_date',
        'status', // unused, assigned, in_transit, cleared, completed
        'assigned_manifest_id',
        'total_pieces',
        'total_weight',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'flight_date' => 'date',
        'total_pieces' => 'integer',
        'total_weight' => 'decimal:2',
    ];

    public function hub()
    {
        return $this->belongsTo(OverseasHub::class, 'hub_id');
    }

    public function manifest()
    {
        return $this->belongsTo(Manifest::class, 'assigned_manifest_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'mawb_id');
    }

    public function scopeUnused($query)
    {
        return $query->where('status', 'unused')->whereNull('assigned_manifest_id');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', '!=', 'unused')->orWhereNotNull('assigned_manifest_id');
    }

    public function scopeByHub($query, $hubId)
    {
        return $query->where(function ($q) use ($hubId) {
            $q->where('hub_id', $hubId)->orWhereNull('hub_id');
        });
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'unused' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'assigned' => 'bg-blue-100 text-blue-800 border-blue-200',
            'in_transit' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'cleared' => 'bg-amber-100 text-amber-800 border-amber-200',
            'completed' => 'bg-slate-100 text-slate-800 border-slate-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }
}
