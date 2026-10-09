<?php

namespace App\Models;

use App\Enums\BookingItemStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\BookingStatusEnum;
use Illuminate\Database\Eloquent\Builder;

class FlexBookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'flex_booking_period_id',
        'flex_billboard_id',
        'design_id',
        'has_dykat',
        'is_gift',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'has_dykat' => 'boolean',
            'is_gift' => 'boolean',
            'status' => BookingItemStatusEnum::class,
        ];
    }

    public function period()
    {
        return $this->belongsTo(FlexBookingPeriod::class,'flex_booking_period_id');
    }

    public function billboard()
    {
        return $this->belongsTo(FlexBillboard::class,'flex_billboard_id');
    }

    public function design()
    {
        return $this->belongsTo(FlexDesign::class,'design_id');
    }

    public function scopeConfirmedForYear(Builder $query,int $year)
    {
        return $query->whereHas(
            'period.flexBooking.booking',
            fn (Builder $query) =>
                $query
                    ->where('year', $year)
                    ->where('status', BookingStatusEnum::CONFIRMED->value)
        );
    }

    public function scopeForAdvertisingPeriod(Builder $query,int $periodId): Builder
    {
        return $query->whereHas(
            'period',
            fn (Builder $query) =>
                $query->where('advertising_period_id', $periodId)
        );
    }
}