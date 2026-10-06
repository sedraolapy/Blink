<?php

namespace App\Http\Controllers\API\Quotation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quotation\IssueQuotationRequest;
use App\Http\Resources\Quotation\QuotationResource;
use App\Services\Booking\BookingService;
use App\Services\Quotation\QuotationCalculator;
use App\Services\Quotation\QuotationPdfService;
use App\Services\Quotation\QuotationService;

class QuotationController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly QuotationCalculator $quotationCalculator,
        private readonly QuotationService $quotationService,
        private readonly QuotationPdfService $quotationPdfService,
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

    public function issue(IssueQuotationRequest $request,int $bookingId)
    {
        $booking = $this->bookingService->show($bookingId);
        $data =     $request->validated('html');
        $pdf = $this->quotationPdfService->generate($data);

        $this->quotationService->issue($booking);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="quotation.pdf"',
        ]);
    }

}