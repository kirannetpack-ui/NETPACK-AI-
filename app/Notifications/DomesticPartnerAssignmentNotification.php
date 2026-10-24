<?php

namespace App\Notifications;

use App\Models\DomesticPartnerAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DomesticPartnerAssignmentNotification extends Notification
{
    use Queueable;

    public function __construct(public DomesticPartnerAssignment $assignment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'domestic_partner_assignment',
            'title' => $this->assignment->is_default ? 'Default territory assignment' : 'Territory assignment',
            'message' => sprintf(
                'You are %s for %s (%s service, %s leg).',
                $this->assignment->is_default ? 'the default partner' : 'an approved alternative partner',
                $this->assignment->zone?->zone_name ?? 'a domestic territory',
                strtoupper(str_replace('_', ' ', $this->assignment->service_type)),
                str_replace('_', ' ', $this->assignment->leg_type)
            ),
            'assignment_id' => $this->assignment->id,
            'zone_id' => $this->assignment->zone_id,
        ];
    }
}
