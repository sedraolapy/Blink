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
        Schema::create('external_booking_periods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('external_booking_type_id')
            ->constrained('external_booking_types')
            ->cascadeOnDelete();

            $table->date('start_date');
            $table->date('end_date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_booking_periods');
    }
};
