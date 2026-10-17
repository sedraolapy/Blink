<?php

namespace App\Http\Controllers\API\Orders\Electronic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\Electronic\ElectronicOrderCandidatesRequest;
use App\Http\Requests\Orders\Electronic\ElectronicOrderCustomersRequest;
use App\Http\Requests\Orders\Electronic\ElectronicOrderIssueRequest;
use App\Http\Requests\Orders\Electronic\ElectronicOrderPeriodsRequest;
use App\Services\Orders\Electronic\ElectronicOrderCandidatesService;
use App\Services\Orders\Electronic\ElectronicOrderCustomersService;
use App\Services\Orders\Electronic\ElectronicOrderIssueService;
use App\Services\Orders\Electronic\ElectronicOrderPdfService;
use App\Services\Orders\Electronic\ElectronicOrderPeriodsService;

class ElectronicOrderController extends Controller
{
    public function __construct(
        private readonly ElectronicOrderCustomersService $customersService,
        private readonly ElectronicOrderPeriodsService $periodsService,
        private readonly ElectronicOrderCandidatesService $candidatesService,
        private readonly ElectronicOrderIssueService $issueService,
        private readonly ElectronicOrderPdfService $pdfService
    ) {}

    public function customers(ElectronicOrderCustomersRequest $request)
    {
        $customers = $this->customersService->getCustomers();

        return sendResponse(
            __('messages.electronic_orders.customers_retrieved'),
            $customers
        );
    }


    public function periods(ElectronicOrderPeriodsRequest $request)
    {
        $customerId = (int) $request->validated('customer_id');
        $periods = $this->periodsService->getPeriods($customerId);

        return sendResponse(
            __('messages.electronic_orders.periods_retrieved'),
            $periods
        );
    }


    public function candidates(ElectronicOrderCandidatesRequest $request)
    {
        $customerId = (int) $request->validated('customer_id');
        $periodId = (int) $request->validated('period_id');

        $candidates = $this->candidatesService->getCandidates($customerId,$periodId);

        return sendResponse(
            __('messages.electronic_orders.candidates_retrieved'),
            $candidates
        );
    }


    public function issue(ElectronicOrderIssueRequest $request)
    {
        $data = $request->validated();

        $pdf = $this->issueService->issue(
            $data,
            fn (array $data, array $selection) =>
            $this->pdfService->generate($data, $selection)
        );

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="electronic-operation.pdf"',
        ]);
    }



}
