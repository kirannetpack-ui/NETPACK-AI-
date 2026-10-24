<?php
// app/Models/OverseasPartner.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class OverseasPartner extends Authenticatable
{
    protected $table = 'overseas_partners';
    
    protected $fillable = [
        'name', 'code', 'country', 'city', 'address', 'phone', 
        'email', 'password', 'contact_person', 'status'
    ];
    
    protected $hidden = ['password'];
    
    public function hubs()
    {
        return $this->hasMany(OverseasHub::class);
    }
    
    public function staff()
    {
        return $this->hasMany(OverseasStaff::class);
    }
}