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
        Schema::create('external_assets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            
            $table->string('type');

            $table->foreignId('area_id')
                ->constrained('areas')
                ->restrictOnDelete();

            $table->json('location_name');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->decimal('width', 10, 2);
            $table->decimal('height', 10, 2);

            $table->decimal('local_price', 12, 2);
            $table->decimal('foreign_price', 12, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_assets');
    }
};
