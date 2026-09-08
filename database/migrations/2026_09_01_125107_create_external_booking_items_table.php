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
        Schema::create('external_booking_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('external_booking_period_id')
            ->constrained('external_booking_periods')
            ->cascadeOnDelete();

            $table->foreignId('external_asset_id')
                ->constrained('external_assets')
                ->restrictOnDelete();

            $table->foreignId('design_id')
                ->nullable()
                ->constrained('external_designs')
                ->nullOnDelete();

            $table->string('status')->default(BookingItemStatusEnum::UNCONFIRMED->value);

            $table->timestamps();

            $table->unique(['external_booking_period_id', 'external_asset_id'],'external_period_asset_unique');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_booking_items');
    }
};
