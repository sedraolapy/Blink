<?php

namespace App\Models;

use App\Enums\ContractStatusEnum;
use App\Enums\SubscriptionTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\Translatable\HasTranslations;

class Customer extends Model
{
    use HasTranslations, HasFactory;

    protected $fillable = [
        'name',
        'phone',
    ];

    public array $translatable = [
        'name',
    ];


    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(CustomerSubscription::class);
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

    public function scopeSubscriptionType(Builder $query,SubscriptionTypeEnum|string|null $subscriptionType,int $year)
    {
        if (! $subscriptionType) {
            return $query;
        }

        $value = $subscriptionType instanceof SubscriptionTypeEnum
            ? $subscriptionType->value
            : $subscriptionType;

        return $query->whereHas(
            'subscriptions',
            fn (Builder $query) =>
                $query
                    ->where('year', $year)
                    ->where('subscription_type', $value)
        );
    }

    public function scopeWithLatestContractStatus(Builder $query,int $year): Builder
    {
        $latestContractStatus = Booking::query()
            ->selectRaw(
                'COALESCE(contracts.status, ?)',
                [ContractStatusEnum::PENDING->value]
            )
            ->leftJoin(
                'contracts',
                'contracts.booking_id',
                '=',
                'bookings.id'
            )
            ->whereColumn(
                'bookings.customer_id',
                'customers.id'
            )
            ->where('bookings.year', $year)
            ->orderByDesc('bookings.created_at')
            ->orderByDesc('bookings.id')
            ->limit(1);

        return $query->addSelect([
            'contract_status' => $latestContractStatus,
        ]);
    }

    public function scopeLatestContractStatus(Builder $query,?string $status,int $year)
    {
        if (! $status) {
            return $query;
        }

        $latestContractStatus = Booking::query()
            ->selectRaw(
                'COALESCE(contracts.status, ?)',
                [ContractStatusEnum::PENDING->value]
            )
            ->leftJoin(
                'contracts',
                'contracts.booking_id',
                '=',
                'bookings.id'
            )
            ->whereColumn(
                'bookings.customer_id',
                'customers.id'
            )
            ->where('bookings.year', $year)
            ->orderByDesc('bookings.created_at')
            ->orderByDesc('bookings.id')
            ->limit(1);

        return $query->where(
            $latestContractStatus,
            '=',
            $status
        );
    }
}