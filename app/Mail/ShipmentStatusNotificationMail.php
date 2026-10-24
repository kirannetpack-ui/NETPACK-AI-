<?php

namespace App\Mail;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ShipmentStatusNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Shipment $shipment,
        public string $statusLabel,
        public string $eventDescription,
        public ?string $location = null,
        public ?string $timestamp = null,
        public ?string $notes = null,
        public ?string $mawbNumber = null,
        public ?string $carrierName = null,
        public ?string $carrierTracking = null
    ) {
        $this->timestamp = $this->timestamp ?: now()->format('d M Y, h:i A T');
        $this->location = $this->location ?: ($shipment->current_location ?: 'Hub Facility');
        $this->mawbNumber = $this->mawbNumber ?: $shipment->mawb_number;
        $this->carrierName = $this->carrierName ?: $shipment->last_mile_carrier_name;
        $this->carrierTracking = $this->carrierTracking ?: $shipment->last_mile_tracking_number;
    }

    public function build()
    {
        $subject = "Shipment Update: {$this->shipment->tracking_number} — {$this->statusLabel} [{$this->location}]";

        return $this->subject($subject)
                    ->view('emails.shipment-status-updated');
    }
}
