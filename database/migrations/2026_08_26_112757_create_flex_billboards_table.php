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
        Schema::create('flex_billboards', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();

            $table->foreignId('area_id')
                ->constrained('areas')
                ->restrictOnDelete();

            $table->json('location_name')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->decimal('width', 10, 2);
            $table->decimal('height', 10, 2);

            $table->timestamps();

            $table->index('area_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flex_billboards');
    }
};
