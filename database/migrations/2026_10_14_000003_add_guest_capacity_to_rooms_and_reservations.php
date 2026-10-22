<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_guests')->default(2);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedTinyInteger('guest_count')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('guest_count');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('max_guests');
        });
    }
};