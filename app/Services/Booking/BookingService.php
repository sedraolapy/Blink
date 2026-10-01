<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Services\WorkingYear\WorkingYearContext;

class BookingService
{
    public function __construct(private readonly WorkingYearContext $workingYearContext)
    {}

    public function show(int $bookingId): Booking
    {
        $year = $this->workingYearContext->get();

        return Booking::query()
            ->where('year', $year)
            ->with([
                'customer',
                'flexBooking',
                'ledBooking',
                'externalBooking.types',
                'quotation',
                'contract',
            ])
            ->findOrFail($bookingId);
    }

    public function updateAdvertiserType(Booking $booking,string $advertiserType): Booking
    {
        $year = $this->workingYearContext->get();

        $booking = Booking::query()
            ->where('id', $booking->id)
            ->where('year', $year)
            ->firstOrFail();

        $booking->update([
            'booking_type' => $advertiserType,
        ]);

        return $booking->refresh();
    }
}