<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('tier', ['signature', 'comfort', 'essential'])->default('essential');
            $table->decimal('price', 10, 2);
            $table->text('blurb')->nullable();
            $table->string('image')->nullable();
            // Amenity chips shown on the room card, e.g. ["King bed", "Soaking tub"]
            $table->json('tags')->nullable();
            $table->enum('status', ['available', 'maintenance'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
