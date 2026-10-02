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
        Schema::create('flex_price_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('governorate_id')
            ->constrained('governorates')
            ->cascadeOnDelete();

            $table->unsignedInteger('billboards_count');

            $table->decimal('local_price', 12, 2);
            $table->decimal('foreign_price', 12, 2);

            $table->timestamps();

            $table->unique('governorate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flex_price_groups');
    }
};
