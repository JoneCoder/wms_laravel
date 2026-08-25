<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class SlackWebhookChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (method_exists($notification, 'toSlackWebhook')) {
            $data = $notification->toSlackWebhook($notifiable);
            
            $url = null;
            
            if (method_exists($notifiable, 'routeNotificationForSlackWebhook')) {
                $url = $notifiable->routeNotificationForSlackWebhook($notification);
            } elseif (is_string($route = $notifiable->routeNotificationFor(self::class, $notification))) {
                $url = $route;
            } elseif (config('services.slack.webhook_url')) {
                $url = config('services.slack.webhook_url');
            }

            if ($url && $data) {
                Http::post($url, $data);
            }
        }
    }
}
