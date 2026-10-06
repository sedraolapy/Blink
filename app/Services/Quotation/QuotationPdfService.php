<?php

namespace App\Services\Quotation;

use App\Models\Booking;
use Spatie\Browsershot\Browsershot;

class QuotationPdfService
{
    public function generate(Booking $booking, array $calculation, ?float $finalAmount = null): string
    {
        $quotation = [
            'customer' => [
                'id' => $booking->customer->id,
                'name' => $booking->customer->name,
            ],

            'advertiser_type' => $booking->booking_type->value,
            'quotation' => ['last_issued_at' => $booking->quotation?->updated_at?->toISOString()],
            'flex' => $calculation['flex'],
            'electronic' => $calculation['electronic'],
            'outdoor' => $calculation['outdoor'],
            'grand_total' => $calculation['grand_total'],
        ];

        $html = view('quotations.pdf', [
            'quotation' => $quotation,
            'language' => 'ar',
            'documentDate' => now()->format('Y/m/d'),
            'finalAmount' => $finalAmount,
        ])->render();

        return Browsershot::html($html)
            ->format('A4')
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->emulateMedia('screen')
            ->disableJavascript()
            ->timeout(60)
            ->pdf();
    }
}