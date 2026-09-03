<?php

namespace App\Models;

use App\Enums\ContractStatusEnum;
use App\Enums\SubscriptionTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

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

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        $locale = app()->getLocale();

        return $query->where(function (Builder $query) use ($search, $locale) {
            $query
                ->where("name->{$locale}", 'like', "%{$search}%");
        });
    }

    public function scopeSubscriptionType(Builder $query, SubscriptionTypeEnum|string|null $subscriptionType): Builder
    {
        if (! $subscriptionType) {
            return $query;
        }

        $value = $subscriptionType instanceof SubscriptionTypeEnum
            ? $subscriptionType->value
            : $subscriptionType;

        return $query->where('subscription_type', $value);
    }

    public function scopeLatestContractStatus(Builder $query,ContractStatusEnum|string|null $status): Builder
    {
        if (! $status) {
            return $query;
        }

        $value = $status instanceof ContractStatusEnum
            ? $status->value
            : $status;

        return $query->where(
            Contract::query()
                ->select('contracts.status')
                ->join(
                    'bookings',
                    'bookings.id',
                    '=',
                    'contracts.booking_id'
                )
                ->whereColumn(
                    'bookings.customer_id',
                    'customers.id'
                )
                ->orderByDesc('contracts.created_at')
                ->orderByDesc('contracts.id')
                ->limit(1),
            $value
        );
    }
}
