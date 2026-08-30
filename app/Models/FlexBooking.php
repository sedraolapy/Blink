<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlexBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function periods()
    {
        return $this->hasMany(FlexBookingPeriod::class);
    }

    public function designs()
    {
        return $this->hasMany(FlexDesign::class);
    }
}
