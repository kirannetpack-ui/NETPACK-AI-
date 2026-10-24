<?php

namespace App\Notifications;

use App\Models\DeliveryZone;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DomesticZoneSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public DeliveryZone $zone, public string $action = 'submitted')
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'domestic_zone_submission',
            'title' => 'Domestic territory '.ucfirst($this->action),
            'message' => sprintf(
                '%s submitted territory %s (%s) for review.',
                $this->zone->partner?->name ?? 'A domestic partner',
                $this->zone->zone_name,
                $this->zone->zone_code
            ),
            'zone_id' => $this->zone->id,
            'partner_id' => $this->zone->partner_user_id,
            'approval_status' => $this->zone->approval_status,
        ];
    }
}
