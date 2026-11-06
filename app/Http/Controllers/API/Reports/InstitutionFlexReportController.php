<?php

namespace App\Http\Controllers\API\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\InstitutionFlexGroupsRequest;
use App\Http\Requests\Reports\InstitutionFlexReportRequest;
use App\Http\Resources\Reports\InstitutionFlexGroupsResource;
use App\Http\Resources\Reports\InstitutionFlexReportResource;
use App\Services\WorkingYear\WorkingYearContext;
use App\Exports\Reports\InstitutionFlexReportExport;
use App\Http\Requests\Reports\InstitutionFlexExportRequest;
use App\Services\Reports\InstitutionReport\InstitutionFlexReportService;
use Maatwebsite\Excel\Facades\Excel;

class InstitutionFlexReportController extends Controller
{
    public function __construct(
        private readonly InstitutionFlexReportService $reportService,
        private readonly WorkingYearContext $workingYearContext
    ) {}

    public function groups(InstitutionFlexGroupsRequest $request)
    {
        $region = $request->validated('region');
        $data = $this->reportService->getGroups($region);

        $resource = new InstitutionFlexGroupsResource($data);

        return sendResponse(
            __('messages.institution_flex_report.groups_retrieved'),
            $resource->resolve($request)
        );
    }


    public function index(InstitutionFlexReportRequest $request)
    {
        $data = $request->validated();
        $year = $this->workingYearContext->get();
        $periodId = (int) $data['period_id'];
        $region = $data['region'];
        $groupId = (int) $data['group_id'];

        $report = $this->reportService->buildReport($year,$periodId,$region,$groupId);
        $resource = new InstitutionFlexReportResource($report);

        return sendResponse(
            __('messages.institution_flex_report.retrieved'),
            $resource->resolve($request)
        );
    }


    public function export(InstitutionFlexExportRequest $request)
    {
        $data = $request->validated();
        $year = $this->workingYearContext->get();
        $periodId = (int) $data['period_id'];
        $region = $data['region'];
        $groupId = (int) $data['group_id'];

        $report = $this->reportService->buildReport($year,$periodId,$region,$groupId);
        $resource = new InstitutionFlexReportResource($report);
        $reportData = $resource->resolve($request);

        $filename = sprintf(
            'blink-institution-flex-%d-period-%d-%s.xlsx',
            $reportData['period']['year'],
            $reportData['period']['number'],
            $region
        );

        return Excel::download(
            new InstitutionFlexReportExport(
                $reportData,
                $data['from'],
                $data['to'],
                $data['booked_label'] ?? null,
                $data['remaining_label'] ?? null
            ),$filename);
    }

}
