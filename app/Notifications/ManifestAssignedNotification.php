<?php

namespace App\Notifications;

use App\Models\Manifest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ManifestAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Manifest $manifest)
    {
    }

    public function via(object $notifiable): array
    {
        // Database notification is the reliable in-app fallback until a
        // partner's approved transport/API credentials are configured.
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'manifest_assigned',
            'manifest_id' => $this->manifest->id,
            'manifest_number' => $this->manifest->manifest_number,
            'message' => "Manifest {$this->manifest->manifest_number} has been assigned to you.",
        ];
    }
}
