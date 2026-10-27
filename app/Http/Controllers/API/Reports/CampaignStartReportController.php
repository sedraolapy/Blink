<?php

namespace App\Http\Controllers\API\Reports;

use App\Exports\Reports\CampaignStartReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\CampaignStartReportRequest;
use App\Http\Resources\Reports\CampaignStartReportResource;
use App\Services\Reports\CampaignStartReportService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class CampaignStartReportController extends Controller
{
    public function __construct(private readonly CampaignStartReportService $reportService)
    {}

    public function index(CampaignStartReportRequest $request)
    {
        $resource = $this->buildReportResource($request);

        if ($resource === null) {
            return sendError(
                __('messages.campaign_start_report.not_available'),
                404
            );
        }

        return sendResponse(
            __('messages.campaign_start_report.retrieved'),
            $resource->resolve($request)
        );
    }

    public function export(CampaignStartReportRequest $request)
    {
        $resource = $this->buildReportResource($request);

        if ($resource === null) {
            return sendError(
                __('messages.campaign_start_report.not_available'),
                404
            );
        }

        $data = $resource->resolve($request);

        $year = $data['period']['year'];
        $periodNumber = $data['period']['number'];

        $filename = sprintf(
            'blink-campaign-start-flex-%d-period-%d.xlsx',
            $year,
            $periodNumber
        );

        return Excel::download(new CampaignStartReportExport($data),$filename);
    }


    private function buildReportResource(Request $request)
    {
        $period = $request->attributes->get('campaign_report_period');
        $issuedItems = $this->reportService->getIssuedItems($period['year'],$period['id']);

        return new CampaignStartReportResource([
            'period' => $period,
            'governorates' => $this->reportService->getGovernorates(),
            'items' => $this->reportService->buildItems($issuedItems),
            'summary' => $this->reportService->buildSummary($issuedItems),
        ]);
    }

}
