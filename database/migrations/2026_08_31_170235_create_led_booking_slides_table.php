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
        Schema::create('led_booking_slides', function (Blueprint $table) {
            $table->id();

            $table->foreignId('led_booking_item_id')
            ->constrained('led_booking_items')
            ->cascadeOnDelete();

            $table->foreignId('design_id')
                ->nullable()
                ->constrained('led_designs')
                ->nullOnDelete();

            $table->unsignedInteger('slide_number');

            $table->timestamps();

            $table->unique(['led_booking_item_id', 'slide_number'],'led_item_slide_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('led_booking_slides');
    }
};
