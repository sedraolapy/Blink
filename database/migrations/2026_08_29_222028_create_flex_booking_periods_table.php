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
        Schema::create('flex_booking_periods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('flex_booking_id')
                ->constrained('flex_bookings')
                ->cascadeOnDelete();

            $table->foreignId('advertising_period_id')
                ->constrained('advertising_periods')
                ->restrictOnDelete();
            $table->timestamps();

            $table->unique(['flex_booking_id', 'advertising_period_id'],'flex_booking_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flex_booking_periods');
    }
};
