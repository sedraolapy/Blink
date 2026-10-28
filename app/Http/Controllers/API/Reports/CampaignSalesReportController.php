<?php

namespace App\Http\Controllers\API\Reports;

use App\Exports\Reports\CampaignSalesReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\CampaignSalesReportRequest;
use App\Http\Resources\Reports\CampaignSalesReportResource;
use App\Services\Reports\CampaignSalesReportService;
use Maatwebsite\Excel\Facades\Excel;

class CampaignSalesReportController extends Controller
{
    public function __construct(
        private readonly CampaignSalesReportService $reportService
    ) {}

    public function index(CampaignSalesReportRequest $request)
    {
        $period = $request->attributes->get('campaign_report_period');
        $report = $this->reportService->buildReport($period);
        $resource = new CampaignSalesReportResource($report);

        return sendResponse(
            __('messages.campaign_sales_report.retrieved'),
            $resource->resolve($request)
        );
    }

    public function export(CampaignSalesReportRequest $request)
    {
        $period = $request->attributes->get('campaign_report_period');

        $report = $this->reportService->buildReport($period);

        $filename = sprintf(
            'blink-campaign-sales-flex-%d-period-%d.xlsx',
            $period['year'],
            $period['number']
        );

        return Excel::download(new CampaignSalesReportExport($report),$filename);
    }
}
