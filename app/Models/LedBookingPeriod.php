<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class LedBookingPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'led_booking_id',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function ledBooking()
    {
        return $this->belongsTo(LedBooking::class);
    }

    public function items()
    {
        return $this->hasMany(LedBookingItem::class);
    }
}
