<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('external_booking_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('external_booking_id')
            ->constrained('external_bookings')
            ->cascadeOnDelete();

            $table->string('type');

            $table->timestamps();

            $table->unique(['external_booking_id', 'type'],'external_booking_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_booking_types');
    }
};
