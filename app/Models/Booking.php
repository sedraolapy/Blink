<?php

namespace App\Models;

use App\Enums\BookingTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'booking_type',
    ];

    protected function casts(): array
    {
        return [
            'booking_type' => BookingTypeEnum::class,
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function flexBooking()
    {
        return $this->hasOne(FlexBooking::class);
    }

    public function ledBooking()
    {
        return $this->hasOne(LedBooking::class);
    }

    public function externalBooking()
    {
        return $this->hasOne(ExternalBooking::class);
    }
}
