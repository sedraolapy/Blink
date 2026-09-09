<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'operation_order',
    ];

    protected function casts(): array
    {
        return [
            'operation_order' => 'boolean',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function periods()
    {
        return $this->hasMany(LedBookingPeriod::class);
    }

    public function designs()
    {
        return $this->hasMany(LedDesign::class);
    }
}