<?php

namespace App\Notifications;

use App\Models\Shipment;
use App\Models\ShipmentLeg;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DomesticPickupAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Shipment $shipment, private readonly ShipmentLeg $leg)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'domestic_pickup_assigned',
            'shipment_id' => $this->shipment->id,
            'shipment_leg_id' => $this->leg->id,
            'tracking_number' => $this->shipment->tracking_number,
            'message' => "Pickup/logistics assignment for shipment {$this->shipment->tracking_number}: {$this->leg->origin_name} to {$this->leg->destination_name}.",
        ];
    }
}
