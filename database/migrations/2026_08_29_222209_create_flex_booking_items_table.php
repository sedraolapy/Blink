<?php

use App\Enums\FlexBookingItemStatusEnum;
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
        Schema::create('flex_booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flex_booking_period_id')
            ->constrained('flex_booking_periods')
            ->cascadeOnDelete();

            $table->foreignId('flex_billboard_id')
                ->constrained('flex_billboards')
                ->restrictOnDelete();

            $table->foreignId('design_id')
                ->nullable()
                ->constrained('flex_designs')
                ->nullOnDelete();

            $table->decimal('unit_price_at_booking', 12, 2);

            $table->boolean('has_dykat')
                ->default(false);

            $table->boolean('is_gift')
                ->default(false);

            $table->string('status')->default(FlexBookingItemStatusEnum::UNCONFIRMED->value);

            $table->timestamps();

            $table->unique(['flex_booking_period_id', 'flex_billboard_id'],'flex_period_billboard_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flex_booking_items');
    }
};
