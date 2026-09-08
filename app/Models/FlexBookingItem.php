<?php

namespace App\Models;

use App\Enums\BookingItemStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}