<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;

class DomesticOperationsNotificationService
{
    public function notify(Notification $notification): void
    {
        User::query()
            ->where(function ($query) {
                $query->whereIn('user_type', ['super_admin', 'admin', 'domestic_admin'])
                    ->orWhere(function ($staff) {
                        $staff->where('user_type', 'staff')
                            ->where(function ($scope) {
                                $scope->whereIn('service_scope', ['domestic', 'all'])
                                    ->orWhereNull('service_scope');
                            });
                    });
            })
            ->where('verification_status', 'approved')
            ->each(fn (User $user) => $user->notify($notification));
    }
}
