<?php
// app/Models/AgencyStaff.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AgencyStaff extends Authenticatable
{
    protected $table = 'agency_staff';
    
    protected $fillable = [
        'agency_id', 'name', 'email', 'password', 'phone', 'position',
        'role', 'can_scan_arrival', 'can_scan_departure', 'can_add_notes',
        'is_active', 'last_login_at'
    ];
    
    protected $hidden = ['password'];
    
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
    
    public function canPerformAction($action)
    {
        switch ($action) {
            case 'arrival':
                return $this->can_scan_arrival;
            case 'departure':
                return $this->can_scan_departure;
            case 'note':
                return $this->can_add_notes;
            default:
                return false;
        }
    }
    
    public function hasRole($role)
    {
        return $this->role === $role;
    }
}