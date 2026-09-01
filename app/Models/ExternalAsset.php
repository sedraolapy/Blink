<?php

namespace App\Models;

use App\Enums\AssetAvailabilityStatusEnum;
use App\Enums\BookingItemStatusEnum;
use App\Enums\ExternalAssetTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;

class ExternalAsset extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'code',
        'type',
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

    protected function casts(): array
    {
        return [
            'type' => ExternalAssetTypeEnum::class,
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function bookingItems()
    {
        return $this->hasMany(ExternalBookingItem::class,'external_asset_id');
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('external_asset_video')
            ->singleFile();
    }

    public function scopeSearch(Builder $query,?string $search)
    {
        return $query->when(
            filled($search),
            function (Builder $query) use ($search) {
                $locale = app()->getLocale();

                $query->where(function (Builder $query) use ($search, $locale) {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere(
                            "location_name->{$locale}",
                            'like',
                            "%{$search}%"
                        );
                });
            }
        );
    }

    public function scopeGovernorate(Builder $query,?int $governorateId): Builder
    {
        return $query->when($governorateId,fn (Builder $query) =>
                $query->whereHas('area',fn (Builder $query) =>
                        $query->where('governorate_id',$governorateId)
                )
        );
    }

    public function scopeType(Builder $query,ExternalAssetTypeEnum $type): Builder
    {
        return $query->where('type', $type->value);
    }

    public function scopeBookedToday(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->whereHas('bookingItems',fn (Builder $query) =>
                $query
                    ->where('status',BookingItemStatusEnum::BOOKED->value)
                    ->whereHas('period',fn (Builder $query) =>
                            $query
                                ->whereDate('start_date', '<=', $today)
                                ->whereDate('end_date', '>=', $today)
                    )
        );
    }

    public function scopeUnconfirmedToday(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query
            ->whereDoesntHave('bookingItems',fn (Builder $query) =>
                    $query
                        ->where('status',BookingItemStatusEnum::BOOKED->value)
                        ->whereHas('period',fn (Builder $query) =>
                                $query
                                    ->whereDate('start_date', '<=', $today)
                                    ->whereDate('end_date', '>=', $today)
                        )
            )
            ->whereHas('bookingItems',fn (Builder $query) =>
                    $query
                        ->where('status',BookingItemStatusEnum::UNCONFIRMED->value)
                        ->whereHas('period',fn (Builder $query) =>
                                $query
                                    ->whereDate('start_date', '<=', $today)
                                    ->whereDate('end_date', '>=', $today)
                        )
            );
    }

    public function scopeAvailableToday(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->whereDoesntHave('bookingItems',fn (Builder $query) =>
                $query
                    ->whereIn('status', [
                        BookingItemStatusEnum::BOOKED->value,
                        BookingItemStatusEnum::UNCONFIRMED->value,
                    ])
                    ->whereHas('period',fn (Builder $query) =>
                            $query
                                ->whereDate('start_date', '<=', $today)
                                ->whereDate('end_date', '>=', $today)
                    )
        );
    }

    public function scopeStatusToday(Builder $query,?string $status): Builder
    {
        return match ($status) {
            AssetAvailabilityStatusEnum::BOOKED->value =>$query->bookedToday(),
            AssetAvailabilityStatusEnum::UNCONFIRMED->value =>$query->unconfirmedToday(),
            AssetAvailabilityStatusEnum::AVAILABLE->value =>$query->availableToday(),

            default =>$query,
        };
    }


}
