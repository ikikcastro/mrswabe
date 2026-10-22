<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            // Nullable in case a booking is ever created for a guest without
            // an account; in practice this app only lets logged-in customers book.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('guest_name');
            $table->string('contact_number');
            $table->date('stay_date');
            $table->enum('payment_method', ['gcash', 'maribank']);
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'])
                ->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
