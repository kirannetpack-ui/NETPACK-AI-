<?php

namespace App\Notifications;

use App\Models\DomesticRate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DomesticRateSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly DomesticRate $rate)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'domestic_rate_submitted',
            'rate_id' => $this->rate->id,
            'partner_id' => $this->rate->partner_id,
            'message' => sprintf(
                '%s submitted a %s %s rate from %s to %s for approval.',
                $this->rate->partner?->name ?? 'A domestic partner',
                str_replace('_', ' ', $this->rate->rate_type),
                $this->rate->service_name,
                $this->rate->originZone?->zone_name ?? $this->rate->origin_city,
                $this->rate->destinationZone?->zone_name ?? $this->rate->destination_city,
            ),
        ];
    }
}
