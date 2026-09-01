<?php

namespace App\Models;

use App\Enums\AssetAvailabilityStatusEnum;
use App\Enums\BookingItemStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Builder;

class FlexBillboard extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'code',
        'area_id',
        'location_name',
        'latitude',
        'longitude',
        'width',
        'height',
        'local_price',
        'foreign_price',
    ];

    public array $translatable = [
        'location_name',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('flex_billboard_video')
            ->singleFile();
    }

    public function bookingItems()
    {
        return $this->hasMany(FlexBookingItem::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        $locale = app()->getLocale();

        return $query->where(function (Builder $query) use ($search, $locale) {
            $query
                ->where('code', 'like', "%{$search}%")
                ->orWhere(
                    "location_name->{$locale}",
                    'like',
                    "%{$search}%"
                );
        });
    }

    public function scopeGovernorate(Builder $query,?int $governorateId): Builder
    {
        if (! $governorateId) {
            return $query;
        }

        return $query->whereHas('area',fn (Builder $query) =>$query->where('governorate_id', $governorateId));
    }

    public function scopeAvailableForPeriod(Builder $query,int $periodId,int $year): Builder
    {
        return $query->whereDoesntHave('bookingItems',fn (Builder $query) =>$query
                        ->whereHas('period',fn (Builder $query) =>$query
                            ->where('advertising_period_id', $periodId)
                            ->where('year', $year)
                )
        );
    }

    public function scopeBookedForPeriod(Builder $query,int $periodId,int $year): Builder
    {
        return $query->whereHas('bookingItems',fn (Builder $query) =>$query
                ->where('status',BookingItemStatusEnum::BOOKED->value)
                    ->whereHas('period',fn (Builder $query) =>$query
                                ->where('advertising_period_id', $periodId)
                                ->where('year', $year)
                    )
        );
    }

    public function scopeUnconfirmedForPeriod(Builder $query,int $periodId,int $year): Builder
    {
        return $query
            ->whereHas('bookingItems',fn (Builder $query) =>$query
                        ->where('status',BookingItemStatusEnum::UNCONFIRMED->value)
                        ->whereHas('period',fn (Builder $query) =>$query
                                    ->where('advertising_period_id', $periodId)
                                    ->where('year', $year)
                        )
            );
    }

    public function scopeStatus(Builder $query,?string $status,int $periodId,int $year): Builder
    {
        if (blank($status)) {
            return $query;
        }

        return match (AssetAvailabilityStatusEnum::from($status)) {
            AssetAvailabilityStatusEnum::AVAILABLE =>$query->availableForPeriod($periodId, $year),
            AssetAvailabilityStatusEnum::UNCONFIRMED =>$query->unconfirmedForPeriod($periodId, $year),
            AssetAvailabilityStatusEnum::BOOKED =>$query->bookedForPeriod($periodId, $year),
        };
    }
}
