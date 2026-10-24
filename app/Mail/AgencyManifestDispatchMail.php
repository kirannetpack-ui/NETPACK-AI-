<?php

namespace App\Mail;

use App\Models\Manifest;
use App\Models\Agency;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgencyManifestDispatchMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Manifest $manifest,
        public Agency $agency,
        public ?string $flightNumber = null,
        public ?string $flightDate = null,
        public ?string $additionalRemarks = null
    ) {
        $this->flightNumber = $this->flightNumber ?: ($manifest->flight_number ?: ($manifest->mawb?->flight_number ?: 'Scheduled Cargo Flight'));
        $this->flightDate = $this->flightDate ?: ($manifest->flight_date ? $manifest->flight_date->format('Y-m-d') : ($manifest->mawb?->flight_date ? $manifest->mawb->flight_date->format('Y-m-d') : now()->format('Y-m-d')));
    }

    public function build()
    {
        $mawbRef = $this->manifest->mawb_number ?: ($this->manifest->mawb?->mawb_number ?: 'MAWB Pending');
        $subject = "Inbound Flight Manifest Dispatch: MAWB #{$mawbRef} — {$this->manifest->destination_city} Hub [{$this->manifest->total_shipments} Consignments]";

        return $this->subject($subject)
                    ->view('emails.agency-manifest-dispatch');
    }
}
