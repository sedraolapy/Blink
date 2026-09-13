<?php

use App\Enums\BookingItemStatusEnum;
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
        Schema::create('led_booking_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('led_booking_period_id')
            ->constrained('led_booking_periods')
            ->cascadeOnDelete();

            $table->foreignId('led_screen_id')
                ->nullable()
                ->constrained('led_screens')
                ->restrictOnDelete();

            $table->foreignId('led_network_id')
                ->nullable()
                ->constrained('led_networks')
                ->restrictOnDelete();

            $table->boolean('is_gift')
                ->default(false);

            $table->string('status')->default(BookingItemStatusEnum::UNCONFIRMED->value);
            $table->timestamps();

            $table->unique(['led_booking_period_id', 'led_screen_id'],'led_period_screen_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('led_booking_items');
    }
};
