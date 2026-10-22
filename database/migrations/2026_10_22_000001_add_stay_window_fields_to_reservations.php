<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // 3, 7 or 24 — the package the customer picked.
            $table->unsignedTinyInteger('stay_hours')->default(24);

            // The stay window. Starts as the booked arrival time, then is reset to
            // the real moment of check-in when the owner marks the stay "Checked in".
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable()->index();

            // Set once the "30 minutes remaining" notice has gone out, so it is
            // never sent twice for the same stay.
            $table->dateTime('reminder_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['stay_hours', 'starts_at', 'ends_at', 'reminder_sent_at']);
        });
    }
};
