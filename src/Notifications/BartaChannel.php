<?php

declare(strict_types=1);

namespace Larament\Barta\Notifications;

use Illuminate\Notifications\Notification;
use Larament\Barta\Facades\Barta;

class BartaChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! $to = $notifiable->routeNotificationFor('barta', $notification)) {
            return;
        }

        /** @phpstan-ignore method.notFound */
        $message = $notification->toBarta($notifiable);

        $driver = method_exists($notification, 'bartaDriver')
            ? $notification->bartaDriver()
            : null;

        Barta::driver($driver)->to($to)->message($message->content)->send();
    }
}
