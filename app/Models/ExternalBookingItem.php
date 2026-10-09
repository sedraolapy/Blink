<?php

namespace App\Models;

use App\Enums\BookingItemStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\BookingStatusEnum;
use Illuminate\Database\Eloquent\Builder;

class ExternalBookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_booking_period_id',
        'external_asset_id',
        'design_id',
        'is_gift',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingItemStatusEnum::class,
            'is_gift' => 'boolean',
        ];
    }

    public function period()
    {
        return $this->belongsTo(ExternalBookingPeriod::class,'external_booking_period_id');
    }

    public function asset()
    {
        return $this->belongsTo(ExternalAsset::class,'external_asset_id');
    }

    public function design()
    {
        return $this->belongsTo(ExternalDesign::class,'design_id');
    }

    public function scopeConfirmedForYear(Builder $query,int $year): Builder
    {
        return $query->whereHas(
            'period.externalBookingType.externalBooking.booking',
            fn (Builder $query) =>
                $query
                    ->where('year', $year)
                    ->where('status', BookingStatusEnum::CONFIRMED->value)
        );
    }
}