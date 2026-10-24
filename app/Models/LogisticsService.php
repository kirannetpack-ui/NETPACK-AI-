<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticsService extends Model
{
    use HasFactory;

    protected $table = 'logistics_services';

    protected $fillable = [
        'partner_id',
        'code',
        'name',
        'category',
        'transit_time_hours',
        'transit_time_days',
        'reminder_intervals',
        'base_rate',
        'per_kg_rate',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'partner_id' => 'integer',
        'transit_time_hours' => 'float',
        'transit_time_days' => 'float',
        'reminder_intervals' => 'array',
        'base_rate' => 'decimal:2',
        'per_kg_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    /**
     * Scope for active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for global/standard services (not partner-customized).
     */
    public function scopeStandard($query)
    {
        return $query->whereNull('partner_id');
    }

    /**
     * Scope for partner specific services.
     */
    public function scopeForPartner($query, $partnerId)
    {
        return $query->where('partner_id', $partnerId);
    }

    /**
     * Scope by category (domestic, international, ecommerce).
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get calculated deadline based on start time.
     */
    public function getDeadline(Carbon $startTime): Carbon
    {
        $hours = (float) $this->transit_time_hours;
        if ($hours <= 0) {
            $hours = 24.0;
        }

        return $startTime->copy()->addMinutes(round($hours * 60));
    }

    /**
     * Calculate dynamic reminder checkpoints for this service.
     * Returns an array of scheduled reminder milestones.
     */
    public function getReminderCheckpoints(Carbon $startTime): array
    {
        $transitHours = (float) $this->transit_time_hours;
        if ($transitHours <= 0) {
            $transitHours = 24.0;
        }

        $totalMinutes = round($transitHours * 60);
        $deadline = $this->getDeadline($startTime);

        // Fetch percentage milestones from configured reminder_intervals, or use defaults
        $milestones = $this->reminder_intervals;
        if (!is_array($milestones) || empty($milestones)) {
            if ($transitHours <= 4) {
                $milestones = [50, 75];
            } elseif ($transitHours <= 24) {
                $milestones = [50, 75, 90];
            } else {
                $milestones = [33, 66, 90];
            }
        }

        // Sort milestones in ascending order
        sort($milestones);

        $checkpoints = [];
        $reminderNumber = 1;

        foreach ($milestones as $pct) {
            $pct = (float) $pct;
            if ($pct <= 0 || $pct >= 100) {
                continue;
            }

            $offsetMinutes = round(($pct / 100.0) * $totalMinutes);
            $scheduledAt = $startTime->copy()->addMinutes($offsetMinutes);

            // Compute remaining hours until deadline
            $remainingHours = max(0, round($deadline->diffInMinutes($scheduledAt) / 60, 1));
            $isUrgent = ($pct >= 75 || $remainingHours <= 2);

            $checkpoints[] = [
                'reminder_number' => $reminderNumber++,
                'milestone_percent' => (int) $pct,
                'scheduled_at' => $scheduledAt,
                'remaining_hours' => $remainingHours,
                'is_urgent' => $isUrgent,
            ];
        }

        return $checkpoints;
    }

    /**
     * Human-readable SLA badge string.
     */
    public function getTransitDisplayAttribute(): string
    {
        $hours = (float) $this->transit_time_hours;

        if ($hours < 24) {
            return ($hours == (int)$hours) ? "{$hours} Hours SLA" : "{$hours}h SLA";
        }

        $days = (float) ($this->transit_time_days ?: round($hours / 24, 1));
        if ($days == (int)$days) {
            return "{$days} Days SLA ({$hours}h)";
        }

        return "{$days} Days SLA ({$hours}h)";
    }
}
