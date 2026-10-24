<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'ticket_number',
        'subject',
        'description',
        'category',
        'priority',
        'status', // open, in_progress, resolved, closed
        'closed_at',
        'metadata',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function messages()
    {
        return $this->hasMany(SupportMessage::class);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'open' => 'bg-yellow-100 text-yellow-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'resolved' => 'bg-green-100 text-green-800',
            'closed' => 'bg-gray-100 text-gray-800',
        ];
        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getPriorityBadgeAttribute()
    {
        $badges = [
            'low' => 'bg-green-100 text-green-800',
            'normal' => 'bg-blue-100 text-blue-800',
            'medium' => 'bg-orange-100 text-orange-800',
            'high' => 'bg-red-100 text-red-800',
        ];
        return $badges[$this->priority] ?? 'bg-gray-100 text-gray-800';
    }
}