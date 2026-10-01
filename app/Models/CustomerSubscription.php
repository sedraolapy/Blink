<?php

namespace App\Models;

use App\Enums\SubscriptionTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSubscription extends Model
{
    protected $fillable = [
        'customer_id',
        'year',
        'subscription_type',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'subscription_type' => SubscriptionTypeEnum::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}