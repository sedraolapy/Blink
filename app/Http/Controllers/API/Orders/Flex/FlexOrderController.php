<?php

namespace App\Http\Controllers\API\Orders\Flex;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\Flex\FlexOrderCandidatesRequest;
use App\Http\Requests\Orders\Flex\IssueFlexOrderRequest;
use App\Services\Orders\Flex\FlexOrderCandidatesService;
use App\Services\Orders\Flex\FlexOrderIssueService;
use App\Services\Orders\Flex\FlexOrderPdfService;

class FlexOrderController extends Controller
{
    public function __construct(
        private readonly FlexOrderCandidatesService $candidatesService,
        private readonly FlexOrderIssueService $issueService,
        private readonly FlexOrderPdfService $pdfService
    ) {}

    public function candidates(FlexOrderCandidatesRequest $request)
    {
        $data = $request->validated();

        $candidates = $this->candidatesService
            ->getCandidates((int) $data['period_id']);

        return sendResponse(
            __('messages.flex_orders.candidates_retrieved'),
            $candidates
        );
    }



    public function issue(IssueFlexOrderRequest $request)
    {
        $data = $request->validated();

        $pdf = $this->issueService->issue(
            $data,
            fn (array $validatedData, array $selection): string =>
                $this->pdfService->generate(
                    $validatedData,
                    $selection
                )
        );

        $filename = 'flex-' . $data['type']. '-period-' . $data['period_id'] . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store',
        ]);
    }


}
