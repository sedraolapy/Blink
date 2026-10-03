<?php

namespace App\Http\Controllers\API\Quotation;

use App\Http\Controllers\Controller;
use App\Http\Resources\Quotation\QuotationResource;
use App\Services\Booking\BookingService;
use App\Services\Quotation\QuotationCalculator;
use App\Services\Quotation\QuotationService;

class QuotationController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly QuotationCalculator $quotationCalculator,
        private readonly QuotationService $quotationService,
    ) {}

    public function show(int $bookingId)
    {
        $booking = $this->bookingService->show($bookingId);

        $calculation = $this->quotationCalculator
            ->calculate($booking);

        return sendResponse(
            __('messages.quotation.show'),
            new QuotationResource([
                'booking' => $booking,
                'calculation' => $calculation,
            ])
        );
    }

    public function issue(int $bookingId)
    {
        $booking = $this->bookingService->show($bookingId);
        $quotation = $this->quotationService->issue($booking);
        $booking->setRelation('quotation',$quotation);

        $calculation = $this->quotationCalculator
            ->calculate($booking);

        return sendResponse(
            __('messages.quotation.issue'),
            new QuotationResource([
                'booking' => $booking,
                'calculation' => $calculation,
            ])
        );
    }

}