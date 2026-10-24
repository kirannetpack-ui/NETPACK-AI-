<?php
// app/Models/PartnerStaff.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PartnerStaff extends Authenticatable
{
    protected $table = 'partner_staff';
    
    protected $fillable = [
        'partner_id', 'name', 'email', 'password', 'phone', 'position', 'role',
        'can_scan_arrival', 'can_scan_departure', 'can_scan_delivery', 'can_add_notes',
        'is_active', 'last_login_at'
    ];
    
    protected $hidden = ['password'];
    
    protected $casts = [
        'can_scan_arrival' => 'boolean',
        'can_scan_departure' => 'boolean',
        'can_scan_delivery' => 'boolean',
        'can_add_notes' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime'
    ];
    
    public function partner()
    {
        return $this->belongsTo(DomesticPartner::class, 'partner_id');
    }
    
    public function canPerformAction($action)
    {
        switch ($action) {
            case 'arrival':
                return $this->can_scan_arrival;
            case 'departure':
                return $this->can_scan_departure;
            case 'delivery':
                return $this->can_scan_delivery;
            default:
                return false;
        }
    }
}