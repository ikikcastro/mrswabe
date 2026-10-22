<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->decimal('price_3_hours', 10, 2)->default(700);
            $table->decimal('price_7_hours', 10, 2)->default(1400);
            $table->decimal('price_24_hours', 10, 2)->default(3500);
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['price_3_hours', 'price_7_hours', 'price_24_hours']);
        });
    }
};