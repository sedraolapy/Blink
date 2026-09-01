<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
class LedNetwork extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'location_name',
        'local_price',
        'foreign_price',
    ];

    public array $translatable = [
        'location_name',
    ];

    public function screens()
    {
        return $this->hasMany(LedScreen::class, 'network_id');
    }

    public function bookingItems()
    {
        return $this->hasMany(LedBookingItem::class, 'led_network_id');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when(
            filled($search),
            function (Builder $query) use ($search) {
                $locale = app()->getLocale();

                $query->where(
                    "location_name->{$locale}",
                    'like',
                    "%{$search}%"
                );
            }
        );
    }

    public function scopeGovernorate(Builder $query,?int $governorateId): Builder
    {
        return $query->when(
            $governorateId,
            function (Builder $query) use ($governorateId) {
                $query->whereHas('screens.area',fn (Builder $query) =>
                        $query->where('governorate_id',$governorateId)
                );
            }
        );
    }
    
}