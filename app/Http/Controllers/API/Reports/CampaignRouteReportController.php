<?php

namespace App\Http\Controllers\API\Reports;

use App\Exports\Reports\CampaignRouteReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\CampaignRouteReportRequest;
use App\Http\Resources\Reports\CampaignRouteReportResource;
use App\Services\Reports\CampaignRouteReportService;
use Maatwebsite\Excel\Facades\Excel;

class CampaignRouteReportController extends Controller
{
    public function __construct(private readonly CampaignRouteReportService $reportService)
    {}

    public function index(CampaignRouteReportRequest $request)
    {
        $currentPeriod = $request->attributes->get('campaign_report_period');
        $validated = $request->validated();
        $governorateIds = $validated['governorate_ids'] ?? null;
        $report = $this->reportService->buildReport($currentPeriod,$governorateIds);

        $resource = new CampaignRouteReportResource($report);

        return sendResponse(
            __('messages.campaign_route_report.retrieved'),
            $resource->resolve($request)
        );
    }

    public function export(CampaignRouteReportRequest $request)
    {
        $currentPeriod = $request->attributes->get('campaign_report_period');
        $governorateIds = $request->validated('governorate_ids');
        $report = $this->reportService->buildReport($currentPeriod,$governorateIds);

        $year = $currentPeriod['year'];
        $periodNumber = $currentPeriod['number'];

        $filename = sprintf(
            'blink-campaign-route-flex-%d-period-%d.xlsx',
            $year,
            $periodNumber
        );

        return Excel::download(new CampaignRouteReportExport($report),$filename);
    }
}
