<?php

namespace App\Http\Controllers\API\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\InstitutionElectronicExportRequest;
use App\Http\Requests\Reports\InstitutionElectronicReportRequest;
use App\Http\Resources\Reports\InstitutionElectronicReportResource;
use App\Services\Reports\InstitutionReport\InstitutionElectronicReportService;
use App\Services\WorkingYear\WorkingYearContext;
use App\Exports\Reports\InstitutionElectronicReportExport;
use Maatwebsite\Excel\Facades\Excel;

class InstitutionElectronicReportController extends Controller
{
    public function __construct(
        private readonly InstitutionElectronicReportService $reportService,
        private readonly WorkingYearContext $workingYearContext
    ) {}

    public function index(InstitutionElectronicReportRequest $request)
    {
        $month = (int) $request->validated('month');
        $year = $this->workingYearContext->get();

        $report = $this->reportService->buildReport($year,$month);
        $resource = new InstitutionElectronicReportResource($report);

        return sendResponse(
            __('messages.institution_electronic_report.retrieved'),
            $resource->resolve($request)
        );
    }

    public function export(InstitutionElectronicExportRequest $request)
    {
        $data = $request->validated();
        $month = (int) $data['month'];
        $year = $this->workingYearContext->get();
        $report = $this->reportService->buildReport($year,$month);

        $resource = new InstitutionElectronicReportResource($report);
        $reportData = $resource->resolve($request);

        $filename = sprintf('blink-institution-electronic-%d-%02d.xlsx',$year,$month);

        return Excel::download(
            new InstitutionElectronicReportExport(
                $reportData,
                $data['from'],
                $data['to']
            ),$filename);
    }
}
