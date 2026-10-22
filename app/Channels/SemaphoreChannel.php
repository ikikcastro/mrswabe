<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Optional SMS channel (Semaphore, a Philippine SMS gateway). Off by default.
 * UNTESTED against the live gateway — verify with your own API key and a
 * test number before relying on it. A failure here is logged, never thrown,
 * so a gateway outage cannot stop the in-app notification or cause repeats.
 */
class SemaphoreChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $number = $notification->reservation->contact_number ?? null;

        if (! $number) {
            return;
        }

        try {
            $response = Http::asForm()->post('https://api.semaphore.co/api/v4/messages', array_filter([
                'apikey' => config('services.sms.key'),
                'number' => $number,
                'message' => $notification->toSms($notifiable),
                'sendername' => config('services.sms.sender'),
            ]));

            if ($response->failed()) {
                Log::warning('SMS reminder failed', ['status' => $response->status()]);
            }
        } catch (\Throwable $e) {
            Log::warning('SMS reminder error: '.$e->getMessage());
        }
    }
}
