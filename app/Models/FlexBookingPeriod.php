<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlexBookingPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'flex_booking_id',
        'advertising_period_id',
        'year',
    ];

    public function flexBooking()
    {
        return $this->belongsTo(FlexBooking::class);
    }

    public function advertisingPeriod()
    {
        return $this->belongsTo(AdvertisingPeriod::class);
    }

    public function items()
    {
        return $this->hasMany(FlexBookingItem::class);
    }
}