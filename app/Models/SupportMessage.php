<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_admin',
        'attachments',
        'metadata',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'attachments' => 'array',
        'metadata' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIsAdminAttribute($value)
    {
        return (bool) $value;
    }
}