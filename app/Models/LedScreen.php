<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;

class LedScreen extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'code',
        'area_id',
        'network_id',
        'location_name',
        'latitude',
        'longitude',
        'width',
        'height',
        'width_px',
        'height_px',
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

    public function network()
    {
        return $this->belongsTo(LedNetwork::class, 'network_id');
    }

    public function bookingItems()
    {
        return $this->hasMany(LedBookingItem::class, 'led_screen_id');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
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
        return $query->when(
            $governorateId,
            fn (Builder $query) =>
                $query->whereHas('area',fn (Builder $query) =>
                        $query->where('governorate_id',$governorateId)
                )
        );
    }


    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('led_screen_video')
            ->singleFile();
    }
}