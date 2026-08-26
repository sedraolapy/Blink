<?php

namespace App\Models;

use App\Enums\SubscriptionTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasTranslations, HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'subscription_type',
    ];

    public array $translatable = [
        'name',
    ];

    protected function casts(): array
    {
        return [
            'subscription_type' => SubscriptionTypeEnum::class,
        ];
    }
}
