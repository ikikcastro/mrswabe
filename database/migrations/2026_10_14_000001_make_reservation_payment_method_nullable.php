<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A "Reserve room" booking holds the room without paying yet, so
        // there is no payment method chosen at that point.
        Schema::table('reservations', function (Blueprint $table) {
            $table->enum('payment_method', ['gcash', 'maribank'])->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->enum('payment_method', ['gcash', 'maribank'])->nullable(false)->change();
        });
    }
};
