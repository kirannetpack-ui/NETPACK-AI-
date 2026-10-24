<?php
// app/Models/OverseasStaff.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class OverseasStaff extends Authenticatable
{
    protected $table = 'overseas_staff';
    
    protected $fillable = [
        'partner_id', 'hub_id', 'name', 'email', 'password', 'phone', 
        'position', 'role', 'can_scan_arrival', 'can_scan_departure', 
        'can_scan_customs', 'is_active', 'last_login_at'
    ];
    
    protected $hidden = ['password'];
    
    public function partner()
    {
        return $this->belongsTo(OverseasPartner::class);
    }
    
    public function hub()
    {
        return $this->belongsTo(OverseasHub::class);
    }
}