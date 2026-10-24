<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShipmentMilestoneNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly array $event)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return array_merge(['type' => 'shipment_milestone'], $this->event);
    }
}
