<?php

namespace App\Http\Controllers\API\Reports;

use App\Exports\Reports\InstitutionOutdoorReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\InstitutionOutdoorExportRequest;
use App\Http\Requests\Reports\InstitutionOutdoorReportRequest;
use App\Http\Resources\Reports\InstitutionOutdoorReportResource;
use App\Services\Reports\InstitutionReport\InstitutionOutdoorReportService;
use App\Services\WorkingYear\WorkingYearContext;
use Maatwebsite\Excel\Facades\Excel;

class InstitutionOutdoorReportController extends Controller
{
    public function __construct(
        private readonly InstitutionOutdoorReportService $reportService,
        private readonly WorkingYearContext $workingYearContext
    ) {}

    public function index(InstitutionOutdoorReportRequest $request)
    {
        $data = $request->validated();

        $year = $this->workingYearContext->get();
        $month = (int) $data['month'];
        $outdoorType = $data['outdoor_type'];

        $report = $this->reportService->buildReport($year,$month,$outdoorType);
        $resource = new InstitutionOutdoorReportResource($report);

        return sendResponse(
            __('messages.institution_outdoor_report.retrieved'),
            $resource->resolve($request)
        );
    }

    public function export(InstitutionOutdoorExportRequest $request)
    {
        $data = $request->validated();
        $year = $this->workingYearContext->get();
        $month = (int) $data['month'];
        $outdoorType = $data['outdoor_type'];
        $report = $this->reportService->buildReport($year, $month,$outdoorType);

        $resource = new InstitutionOutdoorReportResource($report);
        $reportData = $resource->resolve($request);

        $filename = sprintf('blink-institution-outdoor-%s-%d-%02d.xlsx',$outdoorType,$year,$month);

        return Excel::download(
            new InstitutionOutdoorReportExport(
                $reportData,
                $outdoorType,
                $data['from'],
                $data['to']
            ),$filename);
    }
}
