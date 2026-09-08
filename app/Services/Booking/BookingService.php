<?php

namespace App\Services\Booking;

use App\Models\Booking;

class BookingService
{
    public function updateAdvertiserType(Booking $booking,string $advertiserType): Booking 
    {
        $booking->update([
            'booking_type' => $advertiserType,
        ]);

        return $booking->refresh();
    }
}