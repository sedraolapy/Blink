<?php

namespace App\Http\Controllers\API\Orders\Outdoor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\Outdoor\OutdoorOrderCandidatesRequest;
use App\Http\Requests\Orders\Outdoor\OutdoorOrderCustomersRequest;
use App\Http\Requests\Orders\Outdoor\OutdoorOrderIssueRequest;
use App\Http\Requests\Orders\Outdoor\OutdoorOrderPeriodsRequest;
use App\Services\Orders\Outdoor\OutdoorOrderCandidatesService;
use App\Services\Orders\Outdoor\OutdoorOrderCustomersService;
use App\Services\Orders\Outdoor\OutdoorOrderIssueService;
use App\Services\Orders\Outdoor\OutdoorOrderPdfService;
use App\Services\Orders\Outdoor\OutdoorOrderPeriodsService;

class OutdoorOrderController extends Controller
{
    public function __construct(
        private readonly OutdoorOrderCustomersService $customersService,
        private readonly OutdoorOrderPeriodsService $periodsService,
        private readonly OutdoorOrderCandidatesService $candidatesService,
        private readonly OutdoorOrderIssueService $issueService,
        private readonly OutdoorOrderPdfService $pdfService
    ) {}

    public function customers(OutdoorOrderCustomersRequest $request)
    {
        $data = $request->validated();

        $customers = $this->customersService->getCustomers($data['outdoor_type']);

        return sendResponse(
            __('messages.outdoor_orders.customers_retrieved'),
            $customers
        );
    }

    public function periods(OutdoorOrderPeriodsRequest $request)
    {
        $data = $request->validated();

        $periods = $this->periodsService->getPeriods(
            (int) $data['customer_id'],
            $data['outdoor_type']
        );

        return sendResponse(
            __('messages.outdoor_orders.periods_retrieved'),
            $periods
        );
    }

    public function candidates(OutdoorOrderCandidatesRequest $request)
    {
        $data = $request->validated();

        $candidates = $this->candidatesService->getCandidates(
            $data['outdoor_type'],
            (int) $data['customer_id'],
            (int) $data['period_id']
        );

        return sendResponse(
            __('messages.outdoor_orders.candidates_retrieved'),
            $candidates
        );
    }

    public function issue(OutdoorOrderIssueRequest $request)
    {
        $data = $request->validated();

        $pdf = $this->issueService->issue(
            $data,
            fn (array $validatedData, array $selection): string =>
                $this->pdfService->generate($validatedData,$selection)
        );

        $filename = 'outdoor-' . $data['outdoor_type']. '-period-'. $data['period_id']. '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store',
        ]);
    }
}

