<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function types()
    {
        return $this->hasMany(ExternalBookingType::class);
    }

    public function designs()
    {
        return $this->hasMany(ExternalDesign::class);
    }
}