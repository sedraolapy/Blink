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
        Schema::create('led_screens', function (Blueprint $table) {
            $table->id();

            $table->string('code', 100)->unique();

            $table->foreignId('area_id')
                ->constrained('areas')
                ->restrictOnDelete();

            $table->foreignId('network_id')
                ->nullable()
                ->constrained('led_networks')
                ->nullOnDelete();

            $table->json('location_name')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->decimal('width', 10, 2);
            $table->decimal('height', 10, 2);


            $table->unsignedInteger('width_px');
            $table->unsignedInteger('height_px');

            $table->decimal('local_price', 12, 2)->nullable();
            $table->decimal('foreign_price', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('led_screens');
    }
};
