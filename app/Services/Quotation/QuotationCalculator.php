<?php

namespace App\Services\Quotation;

use App\Models\Booking;
use App\Services\Quotation\Calculators\ElectronicQuotationCalculator;
use App\Services\Quotation\Calculators\FlexQuotationCalculator;
use App\Services\Quotation\Calculators\OutdoorQuotationCalculator;

class QuotationCalculator
{
    public function __construct(
        private readonly FlexQuotationCalculator $flexCalculator,
        private readonly ElectronicQuotationCalculator $electronicCalculator,
        private readonly OutdoorQuotationCalculator $outdoorCalculator,
    ) {}

    public function calculate(Booking $booking): array
    {
        $flex = $this->flexCalculator->calculate($booking);
        $electronic = $this->electronicCalculator->calculate($booking);
        $outdoor = $this->outdoorCalculator->calculate($booking);

        return [
            'flex' => $flex,
            'electronic' => $electronic,
            'outdoor' => $outdoor,

            'grand_total' =>
                $flex['total']
                + $electronic['total']
                + $outdoor['total'],
        ];
    }
}