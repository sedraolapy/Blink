<?php

namespace App\Services\Booking;

use App\Models\Booking;

class BookingService
{

    public function show(int $bookingId): Booking
    {
        return Booking::query()
            ->with([
                'customer',
                'flexBooking',
                'ledBooking',
                'externalBooking',
                'quotation',
                'contract',
            ])
            ->findOrFail($bookingId);
    }

    public function updateAdvertiserType(Booking $booking,string $advertiserType): Booking
    {
        $booking->update([
            'booking_type' => $advertiserType,
        ]);

        return $booking->refresh();
    }
}