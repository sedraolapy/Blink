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
        Schema::create('led_designs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('led_booking_id')
                ->constrained('led_bookings')
                ->cascadeOnDelete();

            $table->string('name');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('led_designs');
    }
};
