<?php

namespace App\Notifications;

use App\Channels\SemaphoreChannel;
use App\Models\Reservation;
use Illuminate\Notifications\Notification;

/**
 * "30 minutes remaining" notice. Always lands on the customer account
 * (the notifications table, shown by the bell in the site nav). The SMS
 * channel is added only when SMS_ENABLED=true — see SETUP-STAY-TIMER.md.
 */
class StayEndingSoonNotification extends Notification
{
    public function __construct(public Reservation $reservation) {}

    public function via(object $notifiable): array
    {
        $channels = ["database"];

        if (config("services.sms.enabled")) {
            $channels[] = SemaphoreChannel::class;
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            "type" => "stay_ending_soon",
            "reservation_id" => $this->reservation->id,
            "title" => "30 minutes remaining",
            "message" => $this->text(),
            "ends_at" => $this->reservation->ends_at->toIso8601String(),
        ];
    }

    public function toSms(object $notifiable): string
    {
        return "ikik House: " . $this->text();
    }

    private function text(): string
    {
        $room = $this->reservation->room?->name ?? "your room";

        return "You have 30 minutes remaining in {$room}. Your stay ends at "
            . $this->reservation->ends_at->format("g:i A") . ".";
    }
}
