<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\ReminderLog;
use Illuminate\Support\Facades\Log;

class AdminNotificationHelper
{
    /**
     * Notify admins about a zone rate change
     */
    public static function notifyRateChange($zone, $changes)
    {
        $admins = User::whereIn('user_type', ['admin', 'super_admin', 'domestic_admin'])->get();
        
        $partner = $zone->partner;
        $message = "📋 RATE CHANGE NOTIFICATION\n\n";
        $message .= "Partner: {$partner->name} ({$partner->email})\n";
        $message .= "Zone: {$zone->zone_name}\n";
        $message .= "Zone Code: {$zone->zone_code}\n\n";
        $message .= "Changes made:\n";
        
        $fieldLabels = [
            'flash_base_rate' => 'Flash Base Rate',
            'flash_per_kg_rate' => 'Flash Per KG Rate',
            'flash_estimated_hours' => 'Flash Estimated Hours',
            'same_day_base_rate' => 'Same Day Base Rate',
            'same_day_per_kg_rate' => 'Same Day Per KG Rate',
            'same_day_estimated_hours' => 'Same Day Estimated Hours',
            'standard_base_rate' => 'Standard Base Rate',
            'standard_per_kg_rate' => 'Standard Per KG Rate',
            'standard_estimated_hours' => 'Standard Estimated Hours',
            'himalayan_base_rate' => 'Himalayan Base Rate',
            'himalayan_per_kg_rate' => 'Himalayan Per KG Rate',
            'himalayan_estimated_hours' => 'Himalayan Estimated Hours',
        ];
        
        foreach ($changes as $field => $values) {
            $label = $fieldLabels[$field] ?? $field;
            $oldValue = $values['old'] ?? 'N/A';
            $newValue = $values['new'] ?? 'N/A';
            $message .= "  • {$label}: {$oldValue} → {$newValue}\n";
        }
        
        $message .= "\nTime: " . now()->format('Y-m-d H:i:s');
        
        // Log for each admin
        foreach ($admins as $admin) {
            ReminderLog::create([
                'pickup_request_id' => null,
                'reminder_id' => null,
                'reminder_type' => 'admin_alert',
                'sent_to' => $admin->email,
                'message' => $message,
                'channel' => 'database',
                'status' => 'sent',
                'sent_at' => now(),
                'metadata' => [
                    'zone_id' => $zone->id,
                    'partner_id' => $zone->partner_id,
                    'changes' => $changes,
                ]
            ]);
        }
        
        Log::info('Rate change notification sent to ' . count($admins) . ' admins', [
            'zone_id' => $zone->id,
            'partner_id' => $zone->partner_id
        ]);
    }
}