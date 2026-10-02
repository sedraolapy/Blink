<?php

namespace App\Services\Quotation;

use App\Models\Booking;
use App\Models\Quotation;

class QuotationService
{
    public function issue(Booking $booking): Quotation
    {
        $quotation = Quotation::query()->firstOrCreate([
            'booking_id' => $booking->id,
        ]);

        if (! $quotation->wasRecentlyCreated) {
            $quotation->touch();
        }

        return $quotation->refresh();
    }
}