<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProofOfDelivery extends Model
{
    protected $table = 'proof_of_deliveries';

    protected $fillable = [
        'manifest_shipment_id',
        'shipment_id',
        'manifest_id',
        'uploaded_by',
        'pod_type',
        'pod_file',
        'pod_photo',
        'recipient_name',
        'recipient_signature',
        'delivery_notes',
        'delivered_at',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'metadata',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'verified_at' => 'datetime',
        'metadata' => 'json',
    ];

    // Relationships
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }

    public function manifestShipment()
    {
        return $this->belongsTo(ManifestShipment::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Accessor for status badge
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'uploaded' => 'bg-blue-100 text-blue-800',
            'verified' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}