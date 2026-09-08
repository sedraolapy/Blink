<?php

namespace App\Models;

use App\Enums\ContractStatusEnum;
use App\Enums\SubscriptionTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Translatable\HasTranslations;

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

    protected function casts()
    {
        return [
            'subscription_type' => SubscriptionTypeEnum::class,
        ];
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function contracts(): HasManyThrough
    {
        return $this->hasManyThrough(
            Contract::class,
            Booking::class,
            'customer_id',
            'booking_id'
        );
    }

    public function latestContract(): HasOneThrough
    {
        return $this
            ->contracts()
            ->one()
            ->ofMany([
                'created_at' => 'max',
                'id' => 'max',
            ]);
    }

    public function scopeSearch(Builder $query,?string $search)
    {
        if (! $search) {
            return $query;
        }

        $locale = app()->getLocale();

        return $query->where(
            fn (Builder $query) =>
                $query->where(
                    "name->{$locale}",
                    'like',
                    "%{$search}%"
                )
        );
    }

    public function scopeSubscriptionType(Builder $query, SubscriptionTypeEnum|string|null $subscriptionType)
    {
        if (! $subscriptionType) {
            return $query;
        }

        $value = $subscriptionType
            instanceof SubscriptionTypeEnum
                ? $subscriptionType->value
                : $subscriptionType;

        return $query->where('subscription_type',$value);
    }

    public function scopeLatestContractStatus(Builder $query,ContractStatusEnum|string|null $status)
    {
        if (! $status) {
            return $query;
        }

        $value = $status instanceof ContractStatusEnum
            ? $status->value
            : $status;

        return $query->whereHas('latestContract', fn (Builder $query) =>$query->where('status',$value));
    }
}