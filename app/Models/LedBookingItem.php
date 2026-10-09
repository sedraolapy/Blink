<?php

namespace App\Models;

use App\Enums\BookingItemStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\BookingStatusEnum;
use Illuminate\Database\Eloquent\Builder;


class LedBookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'led_booking_period_id',
        'led_screen_id',
        'led_network_id',
        'is_gift',
    ];

    protected function casts(): array
    {
        return [
            'is_gift' => 'boolean',
            'status' => BookingItemStatusEnum::class,
        ];
    }

    public function period()
    {
        return $this->belongsTo(LedBookingPeriod::class,'led_booking_period_id');
    }

    public function screen()
    {
        return $this->belongsTo(LedScreen::class,'led_screen_id');
    }

    public function network()
    {
        return $this->belongsTo(LedNetwork::class,'led_network_id');
    }

    public function slides()
    {
        return $this->hasMany(LedBookingSlide::class,'led_booking_item_id');
    }

    public function scopeConfirmedForYear(Builder $query,int $year): Builder
    {
        return $query->whereHas(
            'period.ledBooking.booking',
            fn (Builder $query) =>
                $query
                    ->where('year', $year)
                    ->where('status',BookingStatusEnum::CONFIRMED->value)
        );
    }
}