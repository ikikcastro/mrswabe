<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();

            // Snapshot of what was owed at booking time — the room's price
            // right now, kept here so later room price changes don't rewrite
            // history for a payment that already happened.
            $table->decimal('amount', 10, 2);

            $table->enum('method', ['gcash', 'maribank']);
            // Front desk marks this 'paid' once the GCash/MariBank transfer
            // is confirmed on their end — there's no live payment gateway
            // here, so this is a manual confirmation step, not an automatic one.
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');

            // Guest-supplied or front-desk-entered proof of payment — a
            // GCash/MariBank reference number. Nullable because it isn't
            // known yet at the moment the booking (and this row) is created.
            $table->string('reference_number')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
