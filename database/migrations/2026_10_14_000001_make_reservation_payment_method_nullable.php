<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->change();
        });

        // Remove the constraint first if it already exists.
        DB::statement("
            ALTER TABLE reservations
            DROP CONSTRAINT IF EXISTS reservations_payment_method_check
        ");

        // Re-create the allowed values constraint.
        DB::statement("
            ALTER TABLE reservations
            ADD CONSTRAINT reservations_payment_method_check
            CHECK (
                payment_method IS NULL
                OR payment_method IN ('gcash', 'maribank')
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE reservations
            DROP CONSTRAINT IF EXISTS reservations_payment_method_check
        ");

        // Fill NULL values before restoring NOT NULL.
        DB::table('reservations')
            ->whereNull('payment_method')
            ->update(['payment_method' => 'gcash']);

        Schema::table('reservations', function (Blueprint $table) {
            $table->string('payment_method')->nullable(false)->change();
        });

        DB::statement("
            ALTER TABLE reservations
            ADD CONSTRAINT reservations_payment_method_check
            CHECK (payment_method IN ('gcash', 'maribank'))
        ");
    }
};