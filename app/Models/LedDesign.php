<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class LedDesign extends Model
{
    use HasFactory;

    protected $fillable = [
        'led_booking_id',
        'name',
    ];

    public function ledBooking()
    {
        return $this->belongsTo(LedBooking::class);
    }

    public function slides()
    {
        return $this->hasMany(
            LedBookingSlide::class,
            'design_id'
        );
    }
}
