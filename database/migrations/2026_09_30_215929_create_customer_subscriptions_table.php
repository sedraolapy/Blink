<?php

use App\Enums\SubscriptionTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('year');

            $table->string('subscription_type')
                ->default(SubscriptionTypeEnum::BRONZE->value);

            $table->timestamps();

            $table->unique(['customer_id', 'year'],'customer_subscription_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_subscriptions');
    }
};