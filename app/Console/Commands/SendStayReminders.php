<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Notifications\StayEndingSoonNotification;
use Illuminate\Console\Command;

class SendStayReminders extends Command
{
    protected $signature = "reservations:send-stay-reminders";

    protected $description = "Notify customers whose stay has 30 minutes remaining";

    public function handle(): int
    {
        $due = Reservation::query()
            ->with(["user", "room"])
            ->where("status", "checked_in")
            ->whereNull("reminder_sent_at")
            ->where("ends_at", ">", now())
            ->where("ends_at", "<=", now()->addMinutes(30))
            ->get();

        foreach ($due as $reservation) {
            // Mark first so a slow or failing channel can never cause a repeat send.
            $reservation->update(["reminder_sent_at" => now()]);

            $reservation->user?->notify(new StayEndingSoonNotification($reservation));
        }

        $this->info("Sent {$due->count()} reminder(s).");

        return self::SUCCESS;
    }
}
